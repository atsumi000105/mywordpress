<?php

namespace WP2Static;

use ZipArchive;
use WP_Error;
use Exception;

class Controller {
    const VERSION = '7.0-dev';
    const OPTIONS_KEY = 'wp2static-options';
    const HOOK = 'wp2static';

    /**
     * Main controller of WP2Static
     *
     * @var \WP2Static\Controller Instance.
     */
    protected static $instance = null;

    protected function __construct() {}

    /**
     * Returns instance of WP2Static Controller
     *
     * @return \WP2Static\Controller Instance of self.
     */
    public static function getInstance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
            self::$instance->options = new Options(
                self::OPTIONS_KEY
            );
            self::$instance->view = new View();
        }

        return self::$instance;
    }

    public static function init( $bootstrap_file ) {
        $instance = self::getInstance();

        register_activation_hook(
            $bootstrap_file,
            array( $instance, 'activate' )
        );

        if ( is_admin() ) {
            add_action(
                'admin_menu',
                array(
                    $instance,
                    'registerOptionsPage',
                )
            );
            add_filter( 'custom_menu_order', '__return_true' );
            add_filter( 'menu_order', array( $instance, 'set_menu_order' ) );
        }
        return $instance;
    }


    public function set_menu_order( $menu_order ) {
        $order = array();
        $file  = plugin_basename( __FILE__ );
        foreach ( $menu_order as $index => $item ) {
            if ( $item === 'index.php' ) {
                $order[] = $item;
            }
        }

        $order = array(
            'index.php',
            'wp2static',
        );

        return $order;
    }


    public function setDefaultOptions() {
        if ( null === $this->options->getOption( 'version' ) ) {
            $this->options
            ->setOption( 'version', self::VERSION )
            ->setOption( 'static_export_settings', self::VERSION )
            // set default options
            ->setOption( 'rewriteWPPaths', '1' )
            ->setOption( 'removeConditionalHeadComments', '1' )
            ->setOption( 'removeWPMeta', '1' )
            ->setOption( 'removeWPLinks', '1' )
            ->setOption( 'removeHTMLComments', '1' )
            ->setOption( 'parse_css', '1' )
            ->save();
        }
    }

    public function activate_for_single_site() {
        $this->setDefaultOptions();
    }

    public function activate( $network_wide ) {
        if ( $network_wide ) {
            global $wpdb;

            $query = 'SELECT blog_id FROM %s WHERE site_id = %d;';

            $site_ids = $wpdb->get_col(
                sprintf(
                    $query,
                    $wpdb->blogs,
                    $wpdb->siteid
                )
            );

            foreach ( $site_ids as $site_id ) {
                switch_to_blog( $site_id );
                $this->activate_for_single_site();
            }

            restore_current_blog();
        } else {
            $this->activate_for_single_site();
        }
    }

    public function registerOptionsPage() {
        $plugins_url = plugin_dir_url( dirname( __FILE__ ) );
        $page = add_menu_page(
            __( 'WP2Static', 'static-html-output-plugin' ),
            __( 'WP2Static', 'static-html-output-plugin' ),
            'manage_options',
            self::HOOK,
            array( self::$instance, 'renderOptionsPage' ),
            'dashicons-shield-alt'
        );

        add_action(
            'admin_print_styles-' . $page,
            array(
                $this,
                'enqueueAdminStyles',
            )
        );
    }

    public function enqueueAdminStyles() {
        $plugins_url = plugin_dir_url( dirname( __FILE__ ) );

        wp_enqueue_style(
            self::HOOK . '-admin',
            $plugins_url . 'wp2static.css?cache-buster=wp2static',
            array(),
            $this::VERSION
        );
    }

    public function finalize_deployment() {
        $deployer = new Deployer();
        $deployer->finalizeDeployment();

        echo 'SUCCESS';
    }

    public function download_export_log() {
        $site_info = new SiteInfo();
        $site_info = $site_info->get();

        $target_settings = array(
            'general',
            'crawling',
        );

        if ( defined( 'WP_CLI' ) ) {
            $this->settings =
                DBSettings::get( $target_settings );
        } else {
            $this->settings =
                PostSettings::get( $target_settings );
        }

        // get export log path
        $export_log = $site_info->uploads_path .
            '/wp2static-working-files/EXPORT-LOG.txt';

        if ( is_file( $export_log ) ) {
            // create zip of export log in tmp file
            $export_log_zip = $site_info->uploads_path .
                '/wp2static-working-files/EXPORT-LOG.zip';

            $zip_archive = new ZipArchive();
            $zip_opened =
                $zip_archive->open( $export_log_zip, ZipArchive::CREATE );

            if ( $zip_opened !== true ) {
                return new WP_Error( 'Could not create archive' );
            }

            $real_filepath = realpath( $export_log );

            if ( ! $real_filepath ) {
                $err = 'Trying to add unknown file to Zip: ' . $export_log;
                WsLog::l( $err );
                throw new Exception( $err );
            }

            if ( ! $zip_archive->addFile(
                $real_filepath,
                'EXPORT-LOG.txt'
            )
            ) {
                return new WP_Error( 'Could not add Export Log to zip' );
            }

            $zip_archive->close();

            echo $site_info->uploads_url .
                '/wp2static-working-files/EXPORT-LOG.zip';
        } else {
            // serve 500 response to client
            throw new Exception( 'Unable to find Export Log to create ZIP' );
        }
    }

    public function generate_filelist_preview() {
        $site_info = new SiteInfo();
        $site_info = $site_info->get();

        $target_settings = array(
            'general',
            'crawling',
        );

        if ( defined( 'WP_CLI' ) ) {
            $this->settings =
                DBSettings::get( $target_settings );
        } else {
            $this->settings =
                PostSettings::get( $target_settings );
        }

        $plugin_hook = 'wp2static';

        $initial_file_list_count =
            FilesHelper::buildInitialFileList(
                true,
                $site_info->uploads_path,
                $site_info->uploads_url,
                $this->settings
            );

        if ( $initial_file_list_count < 1 ) {
            $err = 'Initial file list unable to be generated';
            WsLog::l( $err );
            throw new Exception( $err );
        }

        if ( ! defined( 'WP_CLI' ) ) {
            echo $initial_file_list_count;
        }
    }

    public function renderOptionsPage() {
        $site_info = new SiteInfo();
        $site_info = $site_info->get();
        $this->current_archive = '';

        $this->view
            ->setTemplate( 'options-page-js' )
            ->assign( 'options', $this->options )
            ->assign( 'wp_site', $site_info )
            ->assign( 'onceAction', self::HOOK . '-options' )
            ->render();

        $this->view
            ->setTemplate( 'options-page' )
            ->assign( 'wp_site', $site_info )
            ->assign( 'options', $this->options )
            ->assign( 'onceAction', self::HOOK . '-options' )
            ->render();
    }

    public function userIsAllowed() {
        $referred_by_admin = check_admin_referer( self::HOOK . '-options' );
        $user_can_manage_options = current_user_can( 'manage_options' );

        return $referred_by_admin && $user_can_manage_options;
    }

    public function save_options() {
        if ( ! $this->userIsAllowed() ) {
            exit( 'Not allowed to change plugin options.' );
        }

        $this->options->saveAllPostData();
    }

    public function prepare_for_export() {
        $this->exporter = new Exporter();

        $this->exporter->pre_export_cleanup();
        $this->exporter->cleanup_leftover_archives();

        $archive = new Archive();
        $archive->create();

        $this->logEnvironmentalInfo();

        $this->exporter->generateModifiedFileList();

        if ( ! defined( 'WP_CLI' ) ) {
            echo 'SUCCESS';
        }
    }

    public function reset_default_settings() {
        if ( ! delete_option( 'wp2static-options' ) ) {
            error_log( "Couldn't reset plugin to default settings" );
        }

        $this->options = new Options( self::OPTIONS_KEY );
        $this->setDefaultOptions();

        echo 'SUCCESS';
    }

    public function post_process_archive_dir() {
        $processor = new ArchiveProcessor();

        $processor->createNetlifySpecialFiles();
        // NOTE: renameWP Directories also doing same server publish
        $processor->renameArchiveDirectories();
        $processor->removeWPCruft();
        $processor->copyStaticSiteToPublicFolder();
        $processor->create_zip();

        if ( ! defined( 'WP_CLI' ) ) {
            echo 'SUCCESS';
        }
    }

    public function delete_deploy_cache() {
        $target_settings = array(
            'wpenv',
        );

        if ( defined( 'WP_CLI' ) ) {
            $this->settings =
                DBSettings::get( $target_settings );
        } else {
            $this->settings =
                PostSettings::get( $target_settings );
        }

        $uploads_dir = $this->settings['wp_uploads_path'];
        $hash_files = glob( "{$uploads_dir}/*PREVIOUS-HASHES*.txt" );
        array_map( 'unlink', $hash_files );

        if ( ! defined( 'WP_CLI' ) ) {
            echo 'SUCCESS';
        }
    }

    public function logEnvironmentalInfo() {
        $info = array(
            '' . date( 'Y-m-d h:i:s' ),
            'PHP VERSION ' . phpversion(),
            'OS VERSION ' . php_uname(),
            'WP VERSION ' . get_bloginfo( 'version' ),
            'WP URL ' . get_bloginfo( 'url' ),
            'WP SITEURL ' . get_option( 'siteurl' ),
            'WP HOME ' . get_option( 'home' ),
            'WP ADDRESS ' . get_bloginfo( 'wpurl' ),
            'PLUGIN VERSION ' . $this::VERSION,
            'VIA WP-CLI? ' . defined( 'WP_CLI' ),
            'STATIC EXPORT URL ' . $this->exporter->settings['baseUrl'],
            'PERMALINK STRUCTURE ' . get_option( 'permalink_structure' ),
        );

        if ( isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
            $info[] = 'SERVER SOFTWARE ' . $_SERVER['SERVER_SOFTWARE'];
        }

        WsLog::l( implode( PHP_EOL, $info ) );

        WsLog::l( 'Active plugins:' );

        $active_plugins = get_option( 'active_plugins' );

        foreach ( $active_plugins as $active_plugin ) {
            WsLog::l( $active_plugin );
        }

        WsLog::l( 'Plugin options:' );

        $options = $this->options->getAllOptions( false );

        foreach ( $options as $key => $value ) {
            WsLog::l( "{$value['Option name']}: {$value['Value']}" );
        }

        $extensions = get_loaded_extensions();

        WsLog::l( 'Installed extensions: ' . join( ', ', $extensions ) );
    }
}
