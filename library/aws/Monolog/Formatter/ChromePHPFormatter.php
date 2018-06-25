<?php
namespace Monolog\Formatter;
use Monolog\Logger;
class ChromePHPFormatter implements FormatterInterface
{
    private $logLevels = array(
        Logger::DEBUG     => 'log',
        Logger::INFO      => 'info',
        Logger::NOTICE    => 'info',
        Logger::WARNING   => 'warn',
        Logger::ERROR     => 'error',
        Logger::CRITICAL  => 'error',
        Logger::ALERT     => 'error',
        Logger::EMERGENCY => 'error',
    );
    public function format(array $record)
    {
        $backtrace = 'unknown';
        if (isset($record['extra']['file'], $record['extra']['line'])) {
            $backtrace = $record['extra']['file'].' : '.$record['extra']['line'];
            unset($record['extra']['file'], $record['extra']['line']);
        }
        $message = array('message' => $record['message']);
        if ($record['context']) {
            $message['context'] = $record['context'];
        }
        if ($record['extra']) {
            $message['extra'] = $record['extra'];
        }
        if (count($message) === 1) {
            $message = reset($message);
        }
        return array(
            $record['channel'],
            $message,
            $backtrace,
            $this->logLevels[$record['level']],
        );
    }
    public function formatBatch(array $records)
    {
        $formatted = array();
        foreach ($records as $record) {
            $formatted[] = $this->format($record);
        }
        return $formatted;
    }
}
