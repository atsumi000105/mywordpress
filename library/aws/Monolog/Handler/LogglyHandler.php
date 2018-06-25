<?php
namespace Monolog\Handler;
use Monolog\Logger;
use Monolog\Formatter\LogglyFormatter;
class LogglyHandler extends AbstractProcessingHandler
{
    const HOST = 'logs-01.loggly.com';
    const ENDPOINT_SINGLE = 'inputs';
    const ENDPOINT_BATCH = 'bulk';
    protected $token;
    protected $tag = array();
    public function __construct($token, $level = Logger::DEBUG, $bubble = true)
    {
        if (!extension_loaded('curl')) {
            throw new \LogicException('The curl extension is needed to use the LogglyHandler');
        }
        $this->token = $token;
        parent::__construct($level, $bubble);
    }
    public function setTag($tag)
    {
        $tag = !empty($tag) ? $tag : array();
        $this->tag = is_array($tag) ? $tag : array($tag);
    }
    public function addTag($tag)
    {
        if (!empty($tag)) {
            $tag = is_array($tag) ? $tag : array($tag);
            $this->tag = array_unique(array_merge($this->tag, $tag));
        }
    }
    protected function write(array $record)
    {
        $this->send($record["formatted"], self::ENDPOINT_SINGLE);
    }
    public function handleBatch(array $records)
    {
        $level = $this->level;
        $records = array_filter($records, function ($record) use ($level) {
            return ($record['level'] >= $level);
        });
        if ($records) {
            $this->send($this->getFormatter()->formatBatch($records), self::ENDPOINT_BATCH);
        }
    }
    protected function send($data, $endpoint)
    {
        $url = sprintf("https:
        $headers = array('Content-Type: application/json');
        if (!empty($this->tag)) {
            $headers[] = 'X-LOGGLY-TAG: '.implode(',', $this->tag);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        Curl\Util::execute($ch);
    }
    protected function getDefaultFormatter()
    {
        return new LogglyFormatter();
    }
}
