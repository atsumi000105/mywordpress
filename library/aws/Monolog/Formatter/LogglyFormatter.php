<?php
namespace Monolog\Formatter;
class LogglyFormatter extends JsonFormatter
{
    public function __construct($batchMode = self::BATCH_MODE_NEWLINES, $appendNewline = false)
    {
        parent::__construct($batchMode, $appendNewline);
    }
    public function format(array $record)
    {
        if (isset($record["datetime"]) && ($record["datetime"] instanceof \DateTime)) {
            $record["timestamp"] = $record["datetime"]->format("Y-m-d\TH:i:s.uO");
        }
        return parent::format($record);
    }
}
