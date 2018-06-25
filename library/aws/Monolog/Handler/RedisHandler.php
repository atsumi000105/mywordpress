<?php
namespace Monolog\Handler;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
class RedisHandler extends AbstractProcessingHandler
{
    private $redisClient;
    private $redisKey;
    protected $capSize;
    public function __construct($redis, $key, $level = Logger::DEBUG, $bubble = true, $capSize = false)
    {
        if (!(($redis instanceof \Predis\Client) || ($redis instanceof \Redis))) {
            throw new \InvalidArgumentException('Predis\Client or Redis instance required');
        }
        $this->redisClient = $redis;
        $this->redisKey = $key;
        $this->capSize = $capSize;
        parent::__construct($level, $bubble);
    }
    protected function write(array $record)
    {
        if ($this->capSize) {
            $this->writeCapped($record);
        } else {
            $this->redisClient->rpush($this->redisKey, $record["formatted"]);
        }
    }
    protected function writeCapped(array $record)
    {
        if ($this->redisClient instanceof \Redis) {
            $this->redisClient->multi()
                ->rpush($this->redisKey, $record["formatted"])
                ->ltrim($this->redisKey, -$this->capSize, -1)
                ->exec();
        } else {
            $redisKey = $this->redisKey;
            $capSize = $this->capSize;
            $this->redisClient->transaction(function ($tx) use ($record, $redisKey, $capSize) {
                $tx->rpush($redisKey, $record["formatted"]);
                $tx->ltrim($redisKey, -$capSize, -1);
            });
        }
    }
    protected function getDefaultFormatter()
    {
        return new LineFormatter();
    }
}
