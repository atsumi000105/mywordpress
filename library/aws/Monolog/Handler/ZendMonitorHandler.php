<?php
namespace Monolog\Handler;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Logger;
class ZendMonitorHandler extends AbstractProcessingHandler
{
    protected $levelMap = array(
        Logger::DEBUG     => 1,
        Logger::INFO      => 2,
        Logger::NOTICE    => 3,
        Logger::WARNING   => 4,
        Logger::ERROR     => 5,
        Logger::CRITICAL  => 6,
        Logger::ALERT     => 7,
        Logger::EMERGENCY => 0,
    );
    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        if (!function_exists('zend_monitor_custom_event')) {
            throw new MissingExtensionException('You must have Zend Server installed in order to use this handler');
        }
        parent::__construct($level, $bubble);
    }
    protected function write(array $record)
    {
        $this->writeZendMonitorCustomEvent(
            $this->levelMap[$record['level']],
            $record['message'],
            $record['formatted']
        );
    }
    protected function writeZendMonitorCustomEvent($level, $message, $formatted)
    {
        zend_monitor_custom_event($level, $message, $formatted);
    }
    public function getDefaultFormatter()
    {
        return new NormalizerFormatter();
    }
    public function getLevelMap()
    {
        return $this->levelMap;
    }
}
