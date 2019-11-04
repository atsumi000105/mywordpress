<?php

namespace WP2Static;

use ZipArchive;
use WP_Error;
use WP_CLI;
use WP_Post;

class Controller {
    const VERSION = '7.0-build0009';
    const OPTIONS_KEY = 'wp2static-options';
    const HOOK = 'wp2static';

    public $options;
    public $bootstrap_file;

    /**
     * Main controller of WP2Static
     *
     * @var \WP2Static\Controller Instance.
     */
    protected static $plugin_instance = null;

    protected function __construct() {}

    /**
     * Returns instance of WP2Static Controller
     *
     * @return \WP2Static\Controller Instance of self.
     */
    public static function getInstance() : Controller {
        if ( null === self::$plugin_instance ) {
            self::$plugin_instance = new self();
        }

        return self::$plugin_instance;
    }

    public static function init( string $bootstrap_file ) : Controller {
        $plugin_instance = self::getInstance();
        $plugin_instance->bootstrap_file = $bootstrap_file;

        $xhr_router = new XHRRouter( $plugin_instance );
        $xhr_router->registerXHRRoutes();

        $wordpress_admin = new WordPressAdmin( $plugin_instance );
        $wordpress_admin->registerHooks();

        $wordpress_admin->addAdminUIElements();

        // load Settings once into singleton
        $plugin_instance->options = new Options( self::OPTIONS_KEY);
        ExportSettings::loadSettingsFromDBOptions(
            $plugin_instance->options->getSettings( true ));
        ExportSettings::setDestinationURL( 'https://example.com' );
        ExportSettings::loadRewriteRules();

        // create DB table for crawl caching
        CrawlCache::createTable();
        CrawlQueue::createTable();
        ExportLog::createTable();
        DeployQueue::createTable();
        DeployCache::createTable();

        ConfigHelper::set_max_execution_time();

        return $plugin_instance;
    }

    /**
     * Adjusts position of dashboard menu icons
     *
     * @param string[] $menu_order list of menu items
     * @return string[] list of menu items
     */
    public function set_menu_order( array $menu_order ) : array {
        $order = [];
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

    public function setDefaultOptions() : void {
        if ( null === $this->options->getOption( 'version' ) ) {
            $this->options
            ->setOption( 'version', self::VERSION )
            ->setOption( 'static_export_settings', self::VERSION )
            // set default options
            ->setOption( 'rewriteWPPaths', '1' )
            ->setOption( 'currentDeploymentMethod', 'folder' )
            ->setOption( 'removeConditionalHeadComments', '1' )
            ->setOption( 'removeWPMeta', '1' )
            ->setOption( 'dontUseCrawlCaching', '1' )
            ->setOption( 'removeWPLinks', '1' )
            ->setOption( 'removeHTMLComments', '1' )
            ->setOption( 'parseCSS', '0' )
            ->save();
        }
    }

    public function activate_for_single_site() : void {
        $this->setDefaultOptions();
    }

    public function activate( bool $network_wide = null ) : void {
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

    public function registerOptionsPage() : void {
        $plugins_url = plugin_dir_url( dirname( __FILE__ ) );
        $page = add_menu_page(
            __( 'WP2Static', 'static-html-output-plugin' ),
            __( 'WP2Static', 'static-html-output-plugin' ),
            'manage_options',
            self::HOOK,
            array( self::$plugin_instance, 'renderOptionsPage' ),
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

    public function enqueueAdminStyles() : void {
        $plugins_url = plugin_dir_url( dirname( __FILE__ ) );

        wp_enqueue_style(
            self::HOOK . '-admin',
            $plugins_url . 'admin/wp2static.css?cache-buster=wp2static',
            array(),
            $this::VERSION
        );
    }

    // NOTE: wrapper for UI to echo success response
    public function finalize_deployment() : void {
        $deployer = new Deployer();
        $deployer->finalizeDeployment();

        echo 'SUCCESS';
    }

    /**
     * Generate ZIP of export log and print URL to UI
     *
     * @throws WP2StaticException
     */
    public function download_export_log() : void {
        $export_log = SiteInfo::getPath( 'uploads' ) .
            'wp2static-working-files/EXPORT-LOG.txt';

        if ( is_file( $export_log ) ) {
            // create zip of export log in tmp file
            $export_log_zip = SiteInfo::getPath( 'uploads' ) .
                'wp2static-working-files/EXPORT-LOG.zip';

            $zip_archive = new ZipArchive();
            $zip_opened =
                $zip_archive->open( $export_log_zip, ZipArchive::CREATE );

            if ( $zip_opened !== true ) {
                throw new WP2StaticException(
                    'Could not create archive'
                );
            }

            $real_filepath = realpath( $export_log );

            if ( ! $real_filepath ) {
                $err = 'Trying to add unknown file to Zip: ' . $export_log;
                WsLog::l( $err );
                throw new WP2StaticException( $err );
            }

            if ( ! $zip_archive->addFile(
                $real_filepath,
                'EXPORT-LOG.txt'
            )
            ) {
                throw new WP2StaticException(
                    'Could not add Export Log to zip'
                );
            }

            $zip_archive->close();

            echo SiteInfo::getUrl( 'uploads' ) .
                'wp2static-working-files/EXPORT-LOG.zip';
        } else {
            throw new WP2StaticException(
                'Unable to find Export Log to create ZIP'
            );
        }
    }

    public function crawl_site() : void {
        $ch = curl_init();

        $asset_downloader = new AssetDownloader( $ch );

        $site_crawler = new SiteCrawler( $asset_downloader );

        $site_crawler->crawl();
    }

    public function crawlSite() : void {
        $crawler = new Crawler();

        $wordpress_site = new WordPressSite();

        $static_site = new StaticSite('/tmp/teststaticsite');

        $crawler->crawlSite($wordpress_site, $static_site);

    }

    public function test_folder() : void {
        $archive_processor = new ArchiveProcessor();

        $target_folder = $this->settings['targetFolder'];

        $has_safety_file =
            $archive_processor->dir_has_safety_file( $target_folder );
        $is_empty =
            $archive_processor->dir_is_empty( $target_folder );

        if ( $has_safety_file || $is_empty ) {
            wp_die( 'SUCCESS', '', 200 );
        }

        wp_die(
            'Not permitted to write to target directory',
            '',
            500
        );
    }

    /**
     * Detect URLs within WordPress site and echo number of files to UI
     *
     * @throws WP2StaticException
     */
    public function detectURLs() : void {
        // TODO: move DeployQueue truncation somewhere...
        // DeployQueue::truncate();

        $detected_url_count = WordPressSite::detectURLs();

        if ( $detected_url_count < 1 ) {
            $err = 'Initial file list unable to be generated';
            http_response_code( 500 );
            echo $err;
            WsLog::l( $err );
            throw new WP2StaticException( $err );
        }

        $via_ui = filter_input( INPUT_POST, 'ajax_action' );

        if ( is_string( $via_ui ) ) {
            echo $detected_url_count;
        }
    }

    /**
     * Check whether a PHP function is enabled
     *
     * @param string $function_name list of menu items
     */
    public function isEnabled( string $function_name ) : bool {
        $disable_functions = ini_get( 'disable_functions' );

        if ( ! is_string( $disable_functions ) ) {
            return false;
        }

        $is_enabled =
            is_callable( $function_name ) &&
            false === stripos( $disable_functions, $function_name );

        return $is_enabled;
    }

    /**
     * Check whether site it publicly accessible
     */
    public function check_local_dns_resolution() : void {
        if ( $this->isEnabled( 'shell_exec' ) ) {
            $site_host = parse_url( $this->site_url, PHP_URL_HOST );

            $output =
                shell_exec( "/usr/sbin/traceroute $site_host" );

            if ( ! is_string( $output ) ) {
                echo 'Unknown';
                return;
            }

            $hops_in_route = substr_count( $output, PHP_EOL );

            $resolves_to_local_ip4 =
                ( strpos( $output, '127.0.0.1' ) !== false );
            $resolves_to_local_ip6 = ( strpos( $output, '::1' ) !== false );

            $resolves_locally =
                $resolves_to_local_ip4 || $resolves_to_local_ip6;

            if ( $resolves_locally && $hops_in_route < 2 ) {
                echo 'Yes';
            } else {
                echo 'No';
            }
        } else {
            error_log( 'no shell_exec' );
            echo 'Unknown';
        }
    }

    public function delete_crawl_cache() : void {

        // we now have modified file list in DB
        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_crawl_cache';

        $wpdb->query( "TRUNCATE TABLE $table_name" );

        $sql =
            "SELECT count(*) FROM $table_name";

        $count = $wpdb->get_var( $sql );

        if ( $count === '0' ) {
            http_response_code( 200 );

            echo 'SUCCESS';
        } else {
            http_response_code( 500 );
        }
    }

    public function load_wp2static_admin_js( string $hook ) : void {
        if ( $hook !== 'toplevel_page_wp2static' ) {
            return;
        }

        $plugin = self::getInstance();

        wp_register_script(
            'wp2static_admin_js',
            SiteInfo::getUrl( 'plugins' ) .
                'static-html-output-plugin/' . // TODO: rm hardcoding slug
                'admin/wp2static-admin.js',
            array( 'jquery' ),
            self::VERSION,
            false
        );

        $options = json_encode(
            $plugin->options->wp2static_options,
            JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES
        );

        $site_info = SiteInfo::getAllInfo();
        $site_info['phpOutOfDate'] = PHP_VERSION < 7.2;
        $site_info['uploadsWritable'] = SiteInfo::isUploadsWritable();
        $site_info['maxExecutionTime'] = ini_get( 'max_execution_time' );
        $site_info['curlSupported'] = SiteInfo::hasCURLSupport();
        $site_info['permalinksDefined'] = SiteInfo::permalinksAreDefined();
        $site_info['domDocumentAvailable'] = class_exists( 'DOMDocument' );

        $plugin->site_info = $site_info;

        $site_info = json_encode(
            $site_info,
            JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES
        );

        $data = array(
            // TODO: pass translatable strings
            'someString' => __( 'Some string to translate', 'plugin-domain' ),
            'options' => $options,
            'siteInfo' => $site_info,
            'onceAction' => self::HOOK . '-options',
        );

        wp_localize_script( 'wp2static_admin_js', 'wp2staticString', $data );
        wp_enqueue_script( 'wp2static_admin_js' );
    }

    public function renderOptionsPage() : void {
        $view = [];
        // TODO: kill all vars in PHP templates
        $view['onceAction'] = self::HOOK . '-options';

        require_once WP2STATIC_PATH . 'views/options-page.php';
    }

    public function userIsAllowed() : bool {
        if ( defined( 'WP_CLI' ) ) {
            return true;
        }

        $referred_by_admin = check_admin_referer( self::HOOK . '-options' );
        $user_can_manage_options = current_user_can( 'manage_options' );

        return $referred_by_admin && $user_can_manage_options;
    }

    public function save_options() : void {
        $via_ui = filter_input( INPUT_POST, 'ajax_action' );

        // Note when running via UI, we save all options
        if ( is_string( $via_ui ) ) {
            if ( ! $this->userIsAllowed() ) {
                exit( 'Not allowed to change plugin options.' );
            }

            $this->options->saveAllOptions();
        }
    }

    public function prepare_for_export() : void {
        $this->save_options();
        $exporter = new Exporter();
        $exporter->pre_export_cleanup();

        FilesHelper::create_export_directory(
            SiteInfo::getPath( 'uploads' ) . 'wp2static-exported-site/');

        EnvironmentalInfo::log(
            self::VERSION,
            $this->options->getAllOptions( false ));

        $exporter->generateModifiedFileList();
        $via_ui = filter_input( INPUT_POST, 'ajax_action' );

        if ( is_string( $via_ui ) ) {
            echo 'SUCCESS';
        }
    }

    public function reset_default_settings() : void {
        if ( ! delete_option( 'wp2static-options' ) ) {
            $err = 'Couldn\'t reset plugin to default settings';
            WsLog::l( $err );
        }

        $this->options = new Options( self::OPTIONS_KEY );
        $this->setDefaultOptions();
    }

    public function post_process_archive_dir() : void {
        $processor = new ArchiveProcessor();

        $processor->createNetlifySpecialFiles();
        // NOTE: renameWP Directories also doing same server publish
        $processor->renameArchiveDirectories();
        $processor->removeWPCruft();
        $processor->copyStaticSiteToPublicFolder();
        $processor->create_zip();

        $via_ui = filter_input( INPUT_POST, 'ajax_action' );

        if ( is_string( $via_ui ) ) {
            echo 'SUCCESS';
        }
    }

    public function delete_deploy_cache() : void {
        DeployCache::truncate();

        $via_ui = filter_input( INPUT_POST, 'ajax_action' );

        if ( is_string( $via_ui ) ) {
            echo 'SUCCESS';
        }
    }

    public function wp2static_headless() : void {
        $start_time = microtime();

        $plugin = self::getInstance();
        $plugin->generate_filelist_preview();
        $plugin->prepare_for_export();
        $plugin->crawl_site();
        $plugin->post_process_archive_dir();

        $end_time = microtime();

        $duration = $Utils::microtime_diff( $start_time, $end_time );

        WsLog::l( "Generated static site archive in $duration seconds" );

        $deployer = new Deployer();
        $deployer->deploy();
    }

    public function invalidate_single_url_cache(
        int $post_id = 0,
        WP_Post $post = null
    ) : void {
        if ( ! $post ) {
            return;
        }

        $permalink = get_permalink(
            $post->ID
        );

        $site_url = SiteInfo::getUrl( 'site' );

        if ( ! is_string( $permalink ) || ! is_string( $site_url ) ) {
            return;
        }

        $url = str_replace(
            $site_url,
            '/',
            $permalink
        );

        CrawlCache::rmUrl( $url );
    }

    public function wp2static_add_dashboard_widgets() : void {
        wp_add_dashboard_widget(
            'wp2static__dashboard_widget',
            'WP2Static',
            [ 'WP2Static\Controller', 'wp2static_dashboard_widget_function' ]
        );
    }

    public function wp2static_dashboard_widget_function() : void {
        echo '<p>Publish whole site as static HTML</p>';
        echo "<button class='button button-primary'>Publish whole site" .
            '</button>';
    }
}
