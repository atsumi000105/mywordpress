<?php

class StaticHtmlOutput_SitePublisher {

    public function clear_file_list() {
        if ( is_file( $this->exportFileList ) ) {
            $f = fopen( $this->exportFileList, 'r+' );
            if ( $f !== false ) {
                ftruncate( $f, 0 );
                fclose( $f );
            }
        }

        if ( isset( $this->globHashAndPathList ) ) {
            if ( is_file( $this->globHashAndPathList ) ) {
                $f = fopen( $this->globHashAndPathList, 'r+' );
                if ( $f !== false ) {
                    ftruncate( $f, 0 );
                    fclose( $f );
                }
            }
        }
    }

    public function create_deployment_list( $dir, $basename_in_target ) {
        $archive = $this->archive->path;

        $files = scandir( $dir );

        foreach ( $files as $item ) {
            if ( $item != '.' && $item != '..' && $item != '.git' ) {
                if ( is_dir( $dir . '/' . $item ) ) {
                    $this->create_deployment_list(
                        $dir . '/' . $item,
                        $basename_in_target
                    );
                } elseif ( is_file( $dir . '/' . $item ) ) {
                    $wp_subdir = str_replace(
                        '/wp-admin/admin-ajax.php',
                        '',
                        $_SERVER['REQUEST_URI']
                    );

                    $wp_subdir = ltrim( $wp_subdir, '/' );
                    $dirs_in_path = $dir;
                    $filename = $item;
                    $original_filepath = $dir . '/' . $item;

                    // $local_path_to_strip = $archive . '/' . $wp_subdir;
                    $local_path_to_strip = $archive;
                    $local_path_to_strip = rtrim( $local_path_to_strip, '/' );

                    // TODO: better detection of subdir/nonstandard paths
                    $local_path_to_strip = str_replace(
                        '//',
                        '/',
                        $local_path_to_strip);

                    $deploy_path = str_replace(
                        $local_path_to_strip,
                        '',
                        $dirs_in_path
                    );

                    $original_file_without_archive = str_replace(
                        $local_path_to_strip,
                        '',
                        $original_filepath
                    );

                    $original_file_without_archive = ltrim(
                        $original_file_without_archive,
                        '/'
                    );

                    $deploy_path = $this->r_path . $deploy_path;
                    $deploy_path = ltrim( $deploy_path, '/' );
                    $deploy_path .= '/';

                    // TODO: better described as "only allow file objects"?
                    // append basename to deply path
                    if ( $basename_in_target ) {
                        $deploy_path .= basename(
                            $original_file_without_archive
                        );
                    }

                    $deploy_path = ltrim( $deploy_path, '/' );

                    $export_line =
                        $original_file_without_archive . ',' . // field 1
                        $deploy_path . // field 2
                        "\n";

                    file_put_contents(
                        $this->exportFileList,
                        $export_line,
                        FILE_APPEND | LOCK_EX
                    );

                    chmod( $this->exportFileList, 0664 );

                }
            }
        }

    }

    public function prepare_export( $basename_in_target = false ) {
        $this->clear_file_list();

        $this->create_deployment_list(
            $this->settings['wp_uploads_path'] . '/' .
                $this->archive->name,
            $basename_in_target
        );
        
        // TODO: detect and use `cat | wc -l` if available

        $linecount = 0;
        $handle = fopen( $this->exportFileList, "r" );

        while( !feof( $handle ) ) {
          $line = fgets( $handle );
          $linecount++;
        }

        fclose($handle);

        $deploy_count_path = $this->settings['wp_uploads_path'] .
                '/WP-STATIC-TOTAL-FILES-TO-DEPLOY.txt';

        file_put_contents(
            $deploy_count_path,
            $linecount,
            LOCK_EX
        );

        chmod( $deploy_count_path, 0664 );


        if ( ! defined( 'WP_CLI' ) ) {
            echo 'SUCCESS';
        }
    }

    public function get_items_to_export( $batch_size = 1 ) {
        $lines = array();

        $f = fopen( $this->exportFileList, 'r' );

        for ( $i = 0; $i < $batch_size; $i++ ) {
            $lines[] = fgets( $f );
        }

        fclose( $f );

        // TODO: optimize this for just one read, one write within func
        $contents = file( $this->exportFileList, FILE_IGNORE_NEW_LINES );

        for ( $i = 0; $i < $batch_size; $i++ ) {
            // rewrite file minus the lines we took
            array_shift( $contents );
        }

        file_put_contents(
            $this->exportFileList,
            implode( "\r\n", $contents )
        );

        chmod( $this->exportFileList, 0664 );

        return $lines;
    }

    public function get_remaining_items_count() {
        $contents = file( $this->exportFileList, FILE_IGNORE_NEW_LINES );

        // return the amount left if another item is taken
        // return count($contents) - 1;
        return count( $contents );
    }
}

