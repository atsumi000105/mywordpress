<?php
$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);
return array(
    'Symfony\\Component\\OptionsResolver\\' => array($vendorDir . '/symfony/options-resolver'),
    'Psr\\Http\\Message\\' => array($vendorDir . '/psr/http-message/src'),
    'Psr\\Cache\\' => array($vendorDir . '/psr/cache/src'),
    'Http\\Promise\\' => array($vendorDir . '/php-http/promise/src'),
    'Http\\Message\\' => array($vendorDir . '/php-http/message-factory/src', $vendorDir . '/php-http/message/src'),
    'Http\\Discovery\\' => array($vendorDir . '/php-http/discovery/src'),
    'Http\\Client\\Common\\Plugin\\' => array($vendorDir . '/php-http/cache-plugin/src'),
    'Http\\Client\\Common\\' => array($vendorDir . '/php-http/client-common/src'),
    'Http\\Client\\' => array($vendorDir . '/php-http/httplug/src'),
    'Http\\Adapter\\Guzzle6\\' => array($vendorDir . '/php-http/guzzle6-adapter/src'),
    'GuzzleHttp\\Psr7\\' => array($vendorDir . '/guzzlehttp/psr7/src'),
    'GuzzleHttp\\Promise\\' => array($vendorDir . '/guzzlehttp/promises/src'),
    'GuzzleHttp\\' => array($vendorDir . '/guzzlehttp/guzzle/src'),
    'Github\\' => array($vendorDir . '/knplabs/github-api/lib/Github'),
    'Clue\\StreamFilter\\' => array($vendorDir . '/clue/stream-filter/src'),
);
