<?php

namespace WP2Static;

class JobQueue {

    public static function createTable() : void {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_jobs';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            job_type VARCHAR(30) NOT NULL,
            status VARCHAR(30) NOT NULL,
            duration SMALLINT(6) UNSIGNED NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Add all Urls to queue
     *
     * @param string[] $urls List of URLs to crawl
     */
    public static function addUrls( array $urls ) : void {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_jobs';

        $placeholders = [];
        $values = [];

        foreach ( $urls as $url ) {
            $placeholders[] = '(%s)';
            $values[] = rawurldecode( $url );
        }

        $query_string =
            'INSERT INTO ' . $table_name . ' (url) VALUES ' .
            implode( ', ', $placeholders );
        $query = $wpdb->prepare( $query_string, $values );

        $wpdb->query( $query );
    }

    /**
     *  Get all crawlable URLs
     *
     *  @return string[] All crawlable URLs
     */
    public static function getJobs() : array {
        global $wpdb;
        $urls = [];

        $table_name = $wpdb->prefix . 'wp2static_jobs';

        $rows = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY created_at DESC" );

        foreach ( $rows as $row ) {
            $urls[] = $row;
        }

        return $urls;
    }

    /**
     *  Get total crawlable URLs
     *
     *  @return int Total crawlable URLs
     */
    public static function getTotalJobableURLs() : int {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_jobs';

        $total_jobs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

        return $total_jobs;
    }

    /**
     *  Clear JobQueue via truncate or deletion
     *
     */
    public static function truncate() : void {
        global $wpdb;

        $table_name = $wpdb->prefix . 'wp2static_jobs';

        $wpdb->query( "TRUNCATE TABLE $table_name" );

        $total_jobs = self::getTotalJobableURLs();

        if ( $total_jobs > 0 ) {
            // TODO: simulate lack of permissios to truncate
            error_log('failed to truncate JobQueue: try deleting instead');
        }
    }
}
