<?php
namespace Monolog\Handler;
use Monolog\Logger;
use Monolog\Formatter\NormalizerFormatter;
use Doctrine\CouchDB\CouchDBClient;
class DoctrineCouchDBHandler extends AbstractProcessingHandler
{
    private $client;
    public function __construct(CouchDBClient $client, $level = Logger::DEBUG, $bubble = true)
    {
        $this->client = $client;
        parent::__construct($level, $bubble);
    }
    protected function write(array $record)
    {
        $this->client->postDocument($record['formatted']);
    }
    protected function getDefaultFormatter()
    {
        return new NormalizerFormatter;
    }
}
