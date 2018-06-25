<?php
namespace Monolog\Handler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
class PsrHandler extends AbstractHandler
{
    protected $logger;
    public function __construct(LoggerInterface $logger, $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->logger = $logger;
    }
    public function handle(array $record)
    {
        if (!$this->isHandling($record)) {
            return false;
        }
        $this->logger->log(strtolower($record['level_name']), $record['message'], $record['context']);
        return false === $this->bubble;
    }
}
