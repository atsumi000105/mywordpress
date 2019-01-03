<?php

use GuzzleHttp\Client;

class StaticHtmlOutput_GitHub extends StaticHtmlOutput_SitePublisher {

    public function __construct() {
        $target_settings = array(
            'general',
            'wpenv',
            'github',
            'advanced',
        );

        if ( isset( $_POST['selected_deployment_option'] ) ) {
            require_once dirname( __FILE__ ) .
                '/../library/StaticHtmlOutput/PostSettings.php';

            $this->settings = WPSHO_PostSettings::get( $target_settings );
        } else {
            require_once dirname( __FILE__ ) .
                '/../library/StaticHtmlOutput/DBSettings.php';

            $this->settings = WPSHO_DBSettings::get( $target_settings );
        }

        list($this->user, $this->repository) = explode(
            '/',
            $this->settings['ghRepo']
        );

        $this->exportFileList =
            $this->settings['wp_uploads_path'] .
                '/WP-STATIC-EXPORT-GITHUB-FILES-TO-EXPORT.txt';
        $archiveDir = file_get_contents(
            $this->settings['wp_uploads_path'] .
                '/WP-STATIC-CURRENT-ARCHIVE.txt'
        );

        $this->r_path = '';

        if ( isset( $this->settings['ghPath'] ) ) {
            $this->r_path = $this->settings['ghPath'];
        }

        // TODO: move this where needed
        require_once dirname( __FILE__ ) .
            '/../library/StaticHtmlOutput/Archive.php';
        $this->archive = new Archive();
        $this->archive->setToCurrentArchive();

        $this->api_base = 'https://api.github.com/repos/';

        switch ( $_POST['ajax_action'] ) {
            case 'github_prepare_export':
                $this->prepare_export( true );
                break;
            case 'github_upload_files':
                $this->upload_files();
                break;
            case 'test_github':
                $this->test_upload();
                break;
        }
    }

    public function upload_files() {
        require_once dirname( __FILE__ ) .
            '/../library/GuzzleHttp/autoloader.php';

        $filesRemaining = $this->get_remaining_items_count();

        if ( $filesRemaining < 0 ) {
            echo 'ERROR';
            die();
        }

        $batch_size = $this->settings['ghBlobIncrement'];

        if ( $batch_size > $filesRemaining ) {
            $batch_size = $filesRemaining;
        }

        $lines = $this->get_items_to_export( $batch_size );
        $globHashPathLines = array();

        $headers = [
            //'Content-Type' => 'application/json',
            'Authorization' => 'token ' . $this->settings['ghToken'],
        ];


        $client = new Client(
            array(
                'base_uri' => $this->api_base,
                'headers' => $headers
            )
        );

        foreach ( $lines as $line ) {
            list($fileToTransfer, $targetPath) = explode( ',', $line );

            $fileToTransfer = $this->archive->path . $fileToTransfer;
            $targetPath = rtrim( $targetPath );

            $resource_path = 
                    $this->settings['ghRepo'] . '/contents/' . $targetPath;

            // GraphQL query to get sha of existing file
$query = <<<JSON
query{
  repository(owner: "{$this->user}", name: "{$this->repository}") {
    object(expression: "{$this->settings['ghBranch']}:{$targetPath}") {
      ... on Blob {
        oid
      }
    }
  }
}
JSON;

            $variables = '';

            $json = array(
                'query' => $query,
                'variables' => $variables
            );

            $response = $client->request(
                'POST',
                // override base_uri with a full URL
                'https://api.github.com/graphql',
                array(
                    'json' => $json,
                    'curl' => array( CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2 )
                )
            );

            $gh_file_info = json_decode( $response->getBody()->getContents(), true );

            error_log( print_r( $gh_file_info, true ));

            $existing_file_object = $gh_file_info['data']['repository']['object'];

            error_log( print_r( $existing_file_object, true ));
            error_log( $existing_file_object);

            if ( ! empty ( $existing_file_object ) ) {
                error_log('File exists: ' . $targetPath);

                $existing_sha = $existing_file_object['oid']; 

                error_log('Same request format, just with SHA added');

                $file_contents = file_get_contents( $fileToTransfer );
                $b64_file_contents = base64_encode( $file_contents );
                $local_sha = sha1( $b64_file_contents );

                error_log('TODO: compare both SHAs: ' . $existing_sha);
                error_log('TODO: to this one: ' . $local_sha);

                try {
                    $response = $client->request(
                        'PUT',
                        $resource_path,
                        array(
                            'json' => array (
                               'message' => 'The commit message', 
                               'content' => $b64_file_contents, 
                               'branch' => $this->settings['ghBranch'], 
                               'sha' => $existing_sha, 
                            )
                        )
                    );

                } catch ( Exception $e ) {
                    require_once dirname( __FILE__ ) .
                        '/../library/StaticHtmlOutput/WsLog.php';
                    WsLog::l( 'GITHUB EXPORT: error encountered' );
                    WsLog::l( $e );
                    error_log( $e );
                    throw new Exception( $e );
                    return;
                }


            } else {
                error_log('File does not exist in GH: ' . $targetPath);

                $file_contents = file_get_contents( $fileToTransfer );
                $b64_file_contents = base64_encode( $file_contents );

                try {
                    $response = $client->request(
                        'PUT',
                        $resource_path,
                        array(
                            'json' => array (
                               'message' => 'The commit message', 
                               'content' => $b64_file_contents, 
                               'branch' => $this->settings['ghBranch'], 
                            )
                        )
                    );

                } catch ( Exception $e ) {
                    require_once dirname( __FILE__ ) .
                        '/../library/StaticHtmlOutput/WsLog.php';
                    WsLog::l( 'GITHUB EXPORT: error encountered' );
                    WsLog::l( $e );
                    error_log( $e );
                    throw new Exception( $e );
                    return;
                }
            }
        }

        if ( isset( $this->settings['ghBlobDelay'] ) &&
            $this->settings['ghBlobDelay'] > 0 ) {
            sleep( $this->settings['ghBlobDelay'] );
        }

        $filesRemaining = $this->get_remaining_items_count();

        if ( $filesRemaining > 0 ) {
            if ( defined( 'WP_CLI' ) ) {
                $this->upload_files();
            } else {
                echo $filesRemaining;
            }
        } else {
            if ( ! defined( 'WP_CLI' ) ) {
                echo 'SUCCESS';
            }
        }
    }

    public function test_upload() {
        require_once dirname( __FILE__ ) .
            '/../library/GuzzleHttp/autoloader.php';

        $headers = [
            'Authorization' => 'token ' . $this->settings['ghToken'],
        ];

        $client = new Client(
            array(
                'base_uri' => $this->api_base,
                'headers' => $headers
            )
        );

        $b64_file_contents = base64_encode( 'WP2Static test upload' );

        $resource_path = 
                $this->settings['ghRepo'] . '/contents/' .
                    '.WP2Static/' . uniqid();

        try {
            $response = $client->request(
                'PUT',
                $resource_path,
                array(
                    'json' => array (
                       'message' => 'WP2Static test upload', 
                       'content' => $b64_file_contents, 
                       'branch' => $this->settings['ghBranch'], 
                    )
                )
            );

        } catch ( Exception $e ) {
            require_once dirname( __FILE__ ) .
                '/../library/StaticHtmlOutput/WsLog.php';
            WsLog::l( 'GITHUB EXPORT: error encountered' );
            WsLog::l( $e );
            throw new Exception( $e );
            return;
        }


        if ( ! defined( 'WP_CLI' ) ) {
            echo 'SUCCESS';
        }
    }
}

$github = new StaticHtmlOutput_GitHub();
