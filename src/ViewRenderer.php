<?php

namespace WP2Static;

class ViewRenderer {

    public static function renderOptionsPage() : void {
        CoreOptions::init();

        $view = [
            'coreOptions' => CoreOptions::getAll(),
            'nonce_action' => 'wp2static-ui-options',
        ];

        require_once WP2STATIC_PATH . 'views/options-page.php';
    }

    public static function renderAdvancedOptionsPage() : void {
        CoreOptions::init();

        $view = [
            'coreOptions' => CoreOptions::getAll(),
            'nonce_action' => 'wp2static-ui-advanced-options',
        ];

        require_once WP2STATIC_PATH . 'views/advanced-options-page.php';
    }

    public static function renderDiagnosticsPage() : void {
        $view = [];
        $view['memoryLimit'] = ini_get( 'memory_limit' );
        $view['coreOptions'] = array_values( CoreOptions::getAll() );
        $view['site_info'] = SiteInfo::getAllInfo();
        $view['phpOutOfDate'] = version_compare( PHP_VERSION, '7.4', '<' );
        $view['uploadsWritable'] = SiteInfo::isUploadsWritable();
        $view['maxExecutionTime'] = ini_get( 'max_execution_time' );
        $view['curlSupported'] = SiteInfo::hasCURLSupport();
        $view['permalinksAreCompatible'] = SiteInfo::permalinksAreCompatible();
        $view['domDocumentAvailable'] = class_exists( 'DOMDocument' );
        $view['extensions'] = get_loaded_extensions();

        require_once WP2STATIC_PATH . 'views/diagnostics-page.php';
    }

    public static function renderLogsPage() : void {
        $view = [];
        $view['nonce_action'] = 'wp2static-log-page';
        $view['logs'] = WsLog::getAll();

        require_once WP2STATIC_PATH . 'views/logs-page.php';
    }

    public static function renderAddonsPage() : void {
        $view = [];
        $view['nonce_action'] = 'wp2static-addons-page';
        $view['addons'] = Addons::getAll();

        require_once WP2STATIC_PATH . 'views/addons-page.php';
    }

    public static function renderTry1ClickPublish() : void {
        if ( ! is_admin() ) {
            http_response_code( 403 );
            die( 'Forbidden' );
        }

        require_once WP2STATIC_PATH . 'views/try-1-click-publishing.php';
    }

    public static function renderCrawlQueue() : void {
        if ( ! is_admin() ) {
            http_response_code( 403 );
            die( 'Forbidden' );
        }

        $action = filter_input( INPUT_GET, 'action' );
        /**
         * @var string[] $url_id
         */
        $url_id = filter_input( INPUT_GET, 'id' );

        if ( $action === 'remove' && is_array( $url_id ) ) {
            CrawlQueue::rmUrlsById( $url_id );
        }

        $urls = CrawlQueue::getCrawlablePaths();
        // Apply search
        $search_term = strval( filter_input( INPUT_GET, 's' ) );
        if ( $search_term !== '' ) {
            $urls = array_filter(
                $urls,
                function ( $url ) use ( $search_term ) {
                    return stripos( $url, $search_term ) !== false;
                }
            );
        }

        $page_size = 200;
        $page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
        $paginator = new Paginator( $urls, $page_size, $page );
        $view = [
            'paginatorFirstPage' => $paginator->firstPage(),
            'paginatorLastPage' => $paginator->lastPage(),
            'paginatorPage' => $paginator->page(),
            'paginatorRecords' => $paginator->records(),
            'paginatorTotalRecords' => $paginator->totalRecords(),
        ];

        require_once WP2STATIC_PATH . 'views/crawl-queue-page.php';
    }

    public static function renderCrawlCache() : void {
        if ( ! is_admin() ) {
            http_response_code( 403 );
            die( 'Forbidden' );
        }

        $action = filter_input( INPUT_GET, 'action' );
        /**
         * @var string[] $url_id
         */
        $url_id = filter_input( INPUT_GET, 'id' );

        if ( $action === 'remove' && is_array( $url_id ) ) {
            CrawlCache::rmUrlsById( $url_id );
        }

        $urls = CrawlCache::getURLs();
        // Apply search
        $search_term = strval( filter_input( INPUT_GET, 's' ) );
        if ( $search_term !== '' ) {
            $urls = array_filter(
                $urls,
                function ( $url ) use ( $search_term ) {
                    return stripos( $url->url ?? '', $search_term ) !== false;
                }
            );
        }

        $page_size = 200;
        $page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
        $paginator = new Paginator( $urls, $page_size, $page );
        $view = [
            'paginatorFirstPage' => $paginator->firstPage(),
            'paginatorLastPage' => $paginator->lastPage(),
            'paginatorPage' => $paginator->page(),
            'paginatorRecords' => $paginator->records(),
            'paginatorTotalRecords' => $paginator->totalRecords(),
        ];

        require_once WP2STATIC_PATH . 'views/crawl-cache-page.php';
    }

    public static function renderPostProcessedSitePaths() : void {
        if ( ! is_admin() ) {
            http_response_code( 403 );
            die( 'Forbidden' );
        }

        $paths = ProcessedSite::getPaths();

        // Apply search
        $search_term = strval( filter_input( INPUT_GET, 's' ) );
        if ( $search_term !== '' ) {
            $paths = array_filter(
                $paths,
                function ( $path ) use ( $search_term ) {
                    return stripos( $path, $search_term ) !== false;
                }
            );
        }

        $page_size = 200;
        $page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
        $paginator = new Paginator( $paths, $page_size, $page );
        $view = [
            'paginatorFirstPage' => $paginator->firstPage(),
            'paginatorLastPage' => $paginator->lastPage(),
            'paginatorPage' => $paginator->page(),
            'paginatorRecords' => $paginator->records(),
            'paginatorTotalRecords' => $paginator->totalRecords(),
        ];

        require_once WP2STATIC_PATH . 'views/post-processed-site-paths-page.php';
    }

    public static function renderStaticSitePaths() : void {
        if ( ! is_admin() ) {
            http_response_code( 403 );
            die( 'Forbidden' );
        }

        $paths = StaticSite::getPaths();

        // Apply search
        $search_term = strval( filter_input( INPUT_GET, 's' ) );
        if ( $search_term !== '' ) {
            $paths = array_filter(
                $paths,
                function ( $path ) use ( $search_term ) {
                    return stripos( $path, $search_term ) !== false;
                }
            );
        }

        $page_size = 200;
        $page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
        $paginator = new Paginator( $paths, $page_size, $page );
        $view = [
            'paginatorFirstPage' => $paginator->firstPage(),
            'paginatorLastPage' => $paginator->lastPage(),
            'paginatorPage' => $paginator->page(),
            'paginatorRecords' => $paginator->records(),
            'paginatorTotalRecords' => $paginator->totalRecords(),
        ];

        require_once WP2STATIC_PATH . 'views/static-site-paths-page.php';
    }

    public static function renderDeployCache() : void {
        if ( ! is_admin() ) {
            http_response_code( 403 );
            die( 'Forbidden' );
        }

        $deploy_namespace = strval( filter_input( INPUT_GET, 'deploy_namespace' ) );
        $paths = $deploy_namespace !== ''
            ? DeployCache::getPaths( $deploy_namespace )
            : DeployCache::getPaths();

        // Apply search
        $search_term = strval( filter_input( INPUT_GET, 's' ) );
        if ( $search_term !== '' ) {
            $paths = array_filter(
                $paths,
                function ( $path ) use ( $search_term ) {
                    return stripos( $path, $search_term ) !== false;
                }
            );
        }

        $page_size = 200;
        $page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
        $paginator = new Paginator( $paths, $page_size, $page );
        $view = [
            'paginatorFirstPage' => $paginator->firstPage(),
            'paginatorLastPage' => $paginator->lastPage(),
            'paginatorPage' => $paginator->page(),
            'paginatorRecords' => $paginator->records(),
            'paginatorTotalRecords' => $paginator->totalRecords(),
        ];

        require_once WP2STATIC_PATH . 'views/deploy-cache-page.php';
    }

    public static function renderJobsPage() : void {
        CoreOptions::init();
        JobQueue::markFailedJobs();
        JobQueue::squashQueue();

        $view = [];
        $view['nonce_action'] = 'wp2static-ui-job-options';
        $view['jobs'] = JobQueue::getJobs();

        $view['jobOptions'] = [
            'queueJobOnPostSave' => CoreOptions::get( 'queueJobOnPostSave' ),
            'queueJobOnPostDelete' => CoreOptions::get( 'queueJobOnPostDelete' ),
            'processQueueImmediately' => CoreOptions::get( 'processQueueImmediately' ),
            'processQueueInterval' => CoreOptions::get( 'processQueueInterval' ),
            'autoJobQueueDetection' => CoreOptions::get( 'autoJobQueueDetection' ),
            'autoJobQueueCrawling' => CoreOptions::get( 'autoJobQueueCrawling' ),
            'autoJobQueuePostProcessing' => CoreOptions::get( 'autoJobQueuePostProcessing' ),
            'autoJobQueueDeployment' => CoreOptions::get( 'autoJobQueueDeployment' ),
        ];

        $view = apply_filters( 'wp2static_render_jobs_page_vars', $view );

        require_once WP2STATIC_PATH . 'views/jobs-page.php';
    }

    public static function renderRunPage() : void {
        $view = [];

        require_once WP2STATIC_PATH . 'views/run-page.php';
    }


    public static function renderCachesPage() : void {
        $view = [];

        // performance check vs map
        $disk_space = 0;

        $exported_site_dir = StaticSite::getPath();
        if ( is_dir( $exported_site_dir ) ) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $exported_site_dir
                )
            );

            foreach ( $files as $file ) {
                /**
                 * @var \SplFileInfo $file
                 */
                $disk_space += $file->getSize();
            }
        }

        $view['exportedSiteDiskSpace'] = sprintf( '%4.2f MB', $disk_space / 1048576 );
        // end check

        if ( is_dir( $exported_site_dir ) ) {
            $view['exportedSiteFileCount'] = iterator_count(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator(
                        $exported_site_dir,
                        \FilesystemIterator::SKIP_DOTS
                    )
                )
            );
        } else {
            $view['exportedSiteFileCount'] = 0;
        }

        // performance check vs map
        $disk_space = 0;
        $processed_site_dir = ProcessedSite::getPath();

        if ( is_dir( $processed_site_dir ) ) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $processed_site_dir
                )
            );

            foreach ( $files as $file ) {
                /**
                 * @var \SplFileInfo $file
                 */
                $disk_space += $file->getSize();
            }
        }

        $view['processedSiteDiskSpace'] = sprintf( '%4.2f MB', $disk_space / 1048576 );
        // end check

        if ( is_dir( $processed_site_dir ) ) {
            $view['processedSiteFileCount'] = iterator_count(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator(
                        $processed_site_dir,
                        \FilesystemIterator::SKIP_DOTS
                    )
                )
            );
        } else {
            $view['processedSiteFileCount'] = 0;
        }

        $view['crawlQueueTotalURLs'] = CrawlQueue::getTotal();
        $view['crawlCacheTotalURLs'] = CrawlCache::getTotal();
        $view['deployCacheTotalPaths'] = DeployCache::getTotal();
        $view['uploads_path'] = SiteInfo::getPath( 'uploads' );
        $view['nonce_action'] = 'wp2static-caches-page';

        require_once WP2STATIC_PATH . 'views/caches-page.php';
    }


}
