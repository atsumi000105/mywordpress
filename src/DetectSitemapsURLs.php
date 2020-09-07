<?php

namespace WP2Static;

use vipnytt\SitemapParser;
use vipnytt\SitemapParser\Exceptions\SitemapParserException;


class DetectSitemapsURLs {

    /**
     * Detect Authors URLs
     *
     * @return string[] list of URLs
     * @throws WP2StaticException
     */
    public static function detect( string $wp_site_url ) : array {

        $sitemaps_urls = [];
        $parser = new SitemapParser( 'WP2StaticAgent', [ 'strict' => false ] );
        $request = new Request();
        $robots_exits = $request->existUrl( $wp_site_url . 'robots.txt' );

        try {
            // if robots exits we parse looking for sitemaps
            if ( $robots_exits === true ) {
                $parser->parseRecursive( $wp_site_url . 'robots.txt' );
                $sitemaps = $parser->getSitemaps();
            }

            // if no sitemaps I'm adding knowing sitemaps
            if ( count( $sitemaps ) === 0 ) {
                $sitemaps = [
                    $wp_site_url . 'sitemap.xml', // normal sitemap
                    $wp_site_url . 'sitemap_index.xml', // yoast sitemap
                    $wp_site_url . 'wp_sitemap.xml', // wp 5.5 sitemap
                ];
            }

            foreach ( $sitemaps as $sitemap ) {
                if ( $request->existUrl( $sitemap ) ) {
                    $parser->parse( $sitemap );

                    $sitemaps_urls [] = '/' . str_replace(
                        $wp_site_url,
                        '',
                        $sitemap
                    );

                    $extract_sitemaps = $parser->getSitemaps();

                    foreach ( $extract_sitemaps as $url => $tags ) {
                        $sitemap_url = str_replace(
                            $wp_site_url,
                            '',
                            $url
                        );

                        $sitemaps_urls [] = '/' . $sitemap_url;
                    }
                }
            }
        } catch ( SitemapParserException $e ) {
            WsLog::l(
                $e->getMessage()
            );
            throw new WP2StaticException( $e->getMessage() );
        }

        return $sitemaps_urls;
    }
}
