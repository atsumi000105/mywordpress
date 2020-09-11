<?php
/*
    WordPressAdmin

    WP2Static's interface to WordPress Admin functions

    Used for registering hooks, Admin UI components, ...
*/

namespace WP2Static;

class WordPressAdmin {

    /**
     * WordPressAdmin constructor
     */
    public function __construct() {

    }

    /**
     * Register hooks for WordPress and WP2Static actions
     *
     * @param string $bootstrap_file main plugin filepath
     */
    public static function registerHooks( string $bootstrap_file ) : void {
        register_activation_hook(
            $bootstrap_file,
            [ 'WP2Static\Controller', 'activate' ]
        );

        register_deactivation_hook(
            $bootstrap_file,
            [ 'WP2Static\Controller', 'deactivate' ]
        );

        add_filter(
            // phpcs:ignore WordPress.WP.CronInterval -- namespaces not yet fully supported
            'cron_schedules',
            [ 'WP2Static\WPCron', 'wp2static_custom_cron_schedules' ]
        );

        add_filter(
            'wp2static_list_redirects',
            [ 'WP2Static\CrawlCache', 'wp2static_list_redirects' ]
        );

        add_filter(
            'cron_request',
            [ 'WP2Static\WPCron', 'wp2static_cron_with_http_basic_auth' ]
        );

        add_action(
            'wp_ajax_wp2static_run',
            [ 'WP2Static\Controller', 'wp2staticRun' ],
            10,
            0
        );

        add_action(
            'wp_ajax_wp2static_poll_log',
            [ 'WP2Static\Controller', 'wp2staticPollLog' ],
            10,
            0
        );

        add_action(
            'admin_post_wp2static_ui_save_options',
            [ 'WP2Static\Controller', 'wp2staticUISaveOptions' ],
            10,
            0
        );

        add_action(
            'wp2static_register_addon',
            [ 'WP2Static\Addons', 'registerAddon' ],
            10,
            5
        );

        add_action(
            'wp2static_post_deploy_trigger',
            [ 'WP2Static\Controller', 'emailDeployNotification' ],
            10,
            0
        );

        add_action(
            'wp2static_post_deploy_trigger',
            [ 'WP2Static\Controller', 'webhookDeployNotification' ],
            10,
            0
        );

        add_action(
            'admin_post_wp2static_post_processed_site_delete',
            [ 'WP2Static\Controller', 'wp2staticPostProcessedSiteDelete' ],
            10,
            0
        );

        add_action(
            'admin_post_wp2static_post_processed_site_show',
            [ 'WP2Static\Controller', 'wp2staticPostProcessedSiteShow' ],
            10,
            0
        );

        add_action(
            'admin_post_wp2static_log_delete',
            [ 'WP2Static\Controller', 'wp2staticLogDelete' ],
            10,
            0
        );

        add_action(
            'admin_post_wp2static_delete_all_caches',
            [ 'WP2Static\Controller', 'wp2staticDeleteAllCaches' ],
            10,
            0
        );

        add_action(
            'admin_post_wp2static_delete_jobs_queue',
            [ 'WP2Static\Controller', 'wp2staticDeleteJobsQueue' ],
            10,
            0
        );

        add_action(
            'admin_post_wp2staticProcessJobsQueue',
            [ 'WP2Static\Controller', 'wp2staticProcessJobsQueue' ],
            10,
            0
        );

        add_action(
            'admin_post_wp2static_crawl_queue_delete',
            [ 'WP2Static\Controller', 'wp2staticCrawlQueueDelete' ],
            10,
            0
        );

        add_action(
            'admin_post_wp2static_crawl_queue_show',
            [ 'WP2Static\Controller', 'wp2staticCrawlQueueShow' ],
            10,
            0
        );

        add_action(
            'admin_post_wp2static_deploy_cache_delete',
            [ 'WP2Static\Controller', 'wp2staticDeployCacheDelete' ],
            10,
            0
        );

        add_action(
            'admin_post_wp2static_deploy_cache_show',
            [ 'WP2Static\Controller', 'wp2staticDeployCacheShow' ],
            10,
            0
        );

        add_action(
            'admin_post_wp2static_crawl_cache_delete',
            [ 'WP2Static\Controller', 'wp2staticCrawlCacheDelete' ],
            10,
            0
        );

        add_action(
            'admin_post_wp2static_crawl_cache_show',
            [ 'WP2Static\Controller', 'wp2staticCrawlCacheShow' ],
            10,
            0
        );

        add_action(
            'admin_post_wp2static_static_site_delete',
            [ 'WP2Static\Controller', 'wp2staticStaticSiteDelete' ],
            10,
            0
        );

        add_action(
            'admin_post_wp2static_static_site_show',
            [ 'WP2Static\Controller', 'wp2staticStaticSiteShow' ],
            10,
            0
        );

        add_action(
            'admin_post_wp2static_ui_save_job_options',
            [ 'WP2Static\Controller', 'wp2staticUISaveJobOptions' ],
            10,
            0
        );

        add_action(
            'admin_post_wp2static_manually_enqueue_jobs',
            [ 'WP2Static\Controller', 'wp2staticManuallyEnqueueJobs' ],
            10,
            0
        );

        add_action(
            'admin_post_wp2static_toggle_addon',
            [ 'WP2Static\Controller', 'wp2staticToggleAddon' ],
            10,
            0
        );

        add_action(
            'wp2static_process_queue',
            [ 'WP2Static\Controller', 'wp2staticProcessQueue' ],
            10,
            0
        );

        add_action(
            'wp2static_headless_hook',
            [ 'WP2Static\Controller', 'wp2staticHeadless' ],
            10,
            0
        );

        add_action(
            'wp2static_crawl',
            [ 'WP2Static\Crawler', 'wp2staticCrawl' ],
            10,
            2
        );

        add_action(
            'wp2static_process_html',
            [ 'WP2Static\SimpleRewriter', 'rewrite' ],
            10,
            1
        );

        add_action(
            'wp2static_process_css',
            [ 'WP2Static\SimpleRewriter', 'rewrite' ],
            10,
            1
        );

        add_action(
            'wp2static_process_js',
            [ 'WP2Static\SimpleRewriter', 'rewrite' ],
            10,
            1
        );

        add_action(
            'wp2static_process_robots_txt',
            [ 'WP2Static\SimpleRewriter', 'rewrite' ],
            10,
            1
        );

        add_action(
            'wp2static_process_xml',
            [ 'WP2Static\SimpleRewriter', 'rewrite' ],
            10,
            1
        );

        add_action(
            'save_post',
            [ 'WP2Static\Controller', 'wp2staticSavePostHandler' ],
            0
        );

        add_action(
            'trashed_post',
            [ 'WP2Static\Controller', 'wp2staticTrashedPostHandler' ],
            0
        );

        /*
         * Register actions for when we should invalidate cache for
         * a URL(s) or whole site
         *
         */
        $single_url_invalidation_events = [
            'save_post',
            'deleted_post',
        ];

        $full_site_invalidation_events = [
            'switch_theme',
        ];

        foreach ( $single_url_invalidation_events as $invalidation_events ) {
            add_action(
                $invalidation_events,
                [ 'WP2Static\Controller', 'invalidateSingleURLCache' ],
                10,
                2
            );
        }
    }

    /**
     * Add WP2Static elements to WordPress Admin UI
     */
    public static function addAdminUIElements() : void {
        if ( is_admin() ) {
            add_action(
                'admin_menu',
                [ 'WP2Static\Controller', 'registerOptionsPage' ]
            );
            add_filter( 'custom_menu_order', '__return_true' );
            add_filter( 'menu_order', [ 'WP2Static\Controller', 'setMenuOrder' ] );
        }
    }
}

