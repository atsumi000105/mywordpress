<?php
namespace Monolog\Formatter;
class LogstashFormatter extends NormalizerFormatter
{
    const V0 = 0;
    const V1 = 1;
    protected $systemName;
    protected $applicationName;
    protected $extraPrefix;
    protected $contextPrefix;
    protected $version;
    public function __construct($applicationName, $systemName = null, $extraPrefix = null, $contextPrefix = 'ctxt_', $version = self::V0)
    {
        parent::__construct('Y-m-d\TH:i:s.uP');
        $this->systemName = $systemName ?: gethostname();
        $this->applicationName = $applicationName;
        $this->extraPrefix = $extraPrefix;
        $this->contextPrefix = $contextPrefix;
        $this->version = $version;
    }
    public function format(array $record)
    {
        $record = parent::format($record);
        if ($this->version === self::V1) {
            $message = $this->formatV1($record);
        } else {
            $message = $this->formatV0($record);
        }
        return $this->toJson($message) . "\n";
    }
    protected function formatV0(array $record)
    {
        if (empty($record['datetime'])) {
            $record['datetime'] = gmdate('c');
        }
        $message = array(
            '@timestamp' => $record['datetime'],
            '@source' => $this->systemName,
            '@fields' => array(),
        );
        if (isset($record['message'])) {
            $message['@message'] = $record['message'];
        }
        if (isset($record['channel'])) {
            $message['@tags'] = array($record['channel']);
            $message['@fields']['channel'] = $record['channel'];
        }
        if (isset($record['level'])) {
            $message['@fields']['level'] = $record['level'];
        }
        if ($this->applicationName) {
            $message['@type'] = $this->applicationName;
        }
        if (isset($record['extra']['server'])) {
            $message['@source_host'] = $record['extra']['server'];
        }
        if (isset($record['extra']['url'])) {
            $message['@source_path'] = $record['extra']['url'];
        }
        if (!empty($record['extra'])) {
            foreach ($record['extra'] as $key => $val) {
                $message['@fields'][$this->extraPrefix . $key] = $val;
            }
        }
        if (!empty($record['context'])) {
            foreach ($record['context'] as $key => $val) {
                $message['@fields'][$this->contextPrefix . $key] = $val;
            }
        }
        return $message;
    }
    protected function formatV1(array $record)
    {
        if (empty($record['datetime'])) {
            $record['datetime'] = gmdate('c');
        }
        $message = array(
            '@timestamp' => $record['datetime'],
            '@version' => 1,
            'host' => $this->systemName,
        );
        if (isset($record['message'])) {
            $message['message'] = $record['message'];
        }
        if (isset($record['channel'])) {
            $message['type'] = $record['channel'];
            $message['channel'] = $record['channel'];
        }
        if (isset($record['level_name'])) {
            $message['level'] = $record['level_name'];
        }
        if ($this->applicationName) {
            $message['type'] = $this->applicationName;
        }
        if (!empty($record['extra'])) {
            foreach ($record['extra'] as $key => $val) {
                $message[$this->extraPrefix . $key] = $val;
            }
        }
        if (!empty($record['context'])) {
            foreach ($record['context'] as $key => $val) {
                $message[$this->contextPrefix . $key] = $val;
            }
        }
        return $message;
    }
}
