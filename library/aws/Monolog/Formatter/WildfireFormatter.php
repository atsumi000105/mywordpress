<?php
namespace Monolog\Formatter;
use Monolog\Logger;
class WildfireFormatter extends NormalizerFormatter
{
    const TABLE = 'table';
    private $logLevels = array(
        Logger::DEBUG     => 'LOG',
        Logger::INFO      => 'INFO',
        Logger::NOTICE    => 'INFO',
        Logger::WARNING   => 'WARN',
        Logger::ERROR     => 'ERROR',
        Logger::CRITICAL  => 'ERROR',
        Logger::ALERT     => 'ERROR',
        Logger::EMERGENCY => 'ERROR',
    );
    public function format(array $record)
    {
        $file = $line = '';
        if (isset($record['extra']['file'])) {
            $file = $record['extra']['file'];
            unset($record['extra']['file']);
        }
        if (isset($record['extra']['line'])) {
            $line = $record['extra']['line'];
            unset($record['extra']['line']);
        }
        $record = $this->normalize($record);
        $message = array('message' => $record['message']);
        $handleError = false;
        if ($record['context']) {
            $message['context'] = $record['context'];
            $handleError = true;
        }
        if ($record['extra']) {
            $message['extra'] = $record['extra'];
            $handleError = true;
        }
        if (count($message) === 1) {
            $message = reset($message);
        }
        if (isset($record['context'][self::TABLE])) {
            $type  = 'TABLE';
            $label = $record['channel'] .': '. $record['message'];
            $message = $record['context'][self::TABLE];
        } else {
            $type  = $this->logLevels[$record['level']];
            $label = $record['channel'];
        }
        $json = $this->toJson(array(
            array(
                'Type'  => $type,
                'File'  => $file,
                'Line'  => $line,
                'Label' => $label,
            ),
            $message,
        ), $handleError);
        return sprintf(
            '%s|%s|',
            strlen($json),
            $json
        );
    }
    public function formatBatch(array $records)
    {
        throw new \BadMethodCallException('Batch formatting does not make sense for the WildfireFormatter');
    }
    protected function normalize($data)
    {
        if (is_object($data) && !$data instanceof \DateTime) {
            return $data;
        }
        return parent::normalize($data);
    }
}
