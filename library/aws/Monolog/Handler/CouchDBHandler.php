<?php
namespace Monolog\Handler;
use Monolog\Formatter\JsonFormatter;
use Monolog\Logger;
class CouchDBHandler extends AbstractProcessingHandler
{
    private $options;
    public function __construct(array $options = array(), $level = Logger::DEBUG, $bubble = true)
    {
        $this->options = array_merge(array(
            'host'     => 'localhost',
            'port'     => 5984,
            'dbname'   => 'logger',
            'username' => null,
            'password' => null,
        ), $options);
        parent::__construct($level, $bubble);
    }
    protected function write(array $record)
    {
        $basicAuth = null;
        if ($this->options['username']) {
            $basicAuth = sprintf('%s:%s@', $this->options['username'], $this->options['password']);
        }
        $url = 'http:
        $context = stream_context_create(array(
            'http' => array(
                'method'        => 'POST',
                'content'       => $record['formatted'],
                'ignore_errors' => true,
                'max_redirects' => 0,
                'header'        => 'Content-type: application/json',
            ),
        ));
        if (false === @file_get_contents($url, null, $context)) {
            throw new \RuntimeException(sprintf('Could not connect to %s', $url));
        }
    }
    protected function getDefaultFormatter()
    {
        return new JsonFormatter(JsonFormatter::BATCH_MODE_JSON, false);
    }
}
