<?php
namespace Monolog\Handler;
use Monolog\Formatter\WildfireFormatter;
class FirePHPHandler extends AbstractProcessingHandler
{
    const PROTOCOL_URI = 'http:
    const STRUCTURE_URI = 'http:
    const PLUGIN_URI = 'http:
    const HEADER_PREFIX = 'X-Wf';
    protected static $initialized = false;
    protected static $messageIndex = 1;
    protected static $sendHeaders = true;
    protected function createHeader(array $meta, $message)
    {
        $header = sprintf('%s-%s', self::HEADER_PREFIX, join('-', $meta));
        return array($header => $message);
    }
    protected function createRecordHeader(array $record)
    {
        return $this->createHeader(
            array(1, 1, 1, self::$messageIndex++),
            $record['formatted']
        );
    }
    protected function getDefaultFormatter()
    {
        return new WildfireFormatter();
    }
    protected function getInitHeaders()
    {
        return array_merge(
            $this->createHeader(array('Protocol', 1), self::PROTOCOL_URI),
            $this->createHeader(array(1, 'Structure', 1), self::STRUCTURE_URI),
            $this->createHeader(array(1, 'Plugin', 1), self::PLUGIN_URI)
        );
    }
    protected function sendHeader($header, $content)
    {
        if (!headers_sent() && self::$sendHeaders) {
            header(sprintf('%s: %s', $header, $content));
        }
    }
    protected function write(array $record)
    {
        if (!self::$sendHeaders) {
            return;
        }
        if (!self::$initialized) {
            self::$initialized = true;
            self::$sendHeaders = $this->headersAccepted();
            if (!self::$sendHeaders) {
                return;
            }
            foreach ($this->getInitHeaders() as $header => $content) {
                $this->sendHeader($header, $content);
            }
        }
        $header = $this->createRecordHeader($record);
        if (trim(current($header)) !== '') {
            $this->sendHeader(key($header), current($header));
        }
    }
    protected function headersAccepted()
    {
        if (!empty($_SERVER['HTTP_USER_AGENT']) && preg_match('{\bFirePHP/\d+\.\d+\b}', $_SERVER['HTTP_USER_AGENT'])) {
            return true;
        }
        return isset($_SERVER['HTTP_X_FIREPHP_VERSION']);
    }
    public function __get($property)
    {
        if ('sendHeaders' !== $property) {
            throw new \InvalidArgumentException('Undefined property '.$property);
        }
        return static::$sendHeaders;
    }
    public function __set($property, $value)
    {
        if ('sendHeaders' !== $property) {
            throw new \InvalidArgumentException('Undefined property '.$property);
        }
        static::$sendHeaders = $value;
    }
}
