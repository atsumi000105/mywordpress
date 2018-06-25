<?php
namespace Monolog\Handler;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
class ErrorLogHandler extends AbstractProcessingHandler
{
    const OPERATING_SYSTEM = 0;
    const SAPI = 4;
    protected $messageType;
    protected $expandNewlines;
    public function __construct($messageType = self::OPERATING_SYSTEM, $level = Logger::DEBUG, $bubble = true, $expandNewlines = false)
    {
        parent::__construct($level, $bubble);
        if (false === in_array($messageType, self::getAvailableTypes())) {
            $message = sprintf('The given message type "%s" is not supported', print_r($messageType, true));
            throw new \InvalidArgumentException($message);
        }
        $this->messageType = $messageType;
        $this->expandNewlines = $expandNewlines;
    }
    public static function getAvailableTypes()
    {
        return array(
            self::OPERATING_SYSTEM,
            self::SAPI,
        );
    }
    protected function getDefaultFormatter()
    {
        return new LineFormatter('[%datetime%] %channel%.%level_name%: %message% %context% %extra%');
    }
    protected function write(array $record)
    {
        if ($this->expandNewlines) {
            $lines = preg_split('{[\r\n]+}', (string) $record['formatted']);
            foreach ($lines as $line) {
                error_log($line, $this->messageType);
            }
        } else {
            error_log((string) $record['formatted'], $this->messageType);
        }
    }
}
