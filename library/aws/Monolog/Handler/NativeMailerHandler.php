<?php
namespace Monolog\Handler;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
class NativeMailerHandler extends MailHandler
{
    protected $to;
    protected $subject;
    protected $headers = array();
    protected $parameters = array();
    protected $maxColumnWidth;
    protected $contentType = 'text/plain';
    protected $encoding = 'utf-8';
    public function __construct($to, $subject, $from, $level = Logger::ERROR, $bubble = true, $maxColumnWidth = 70)
    {
        parent::__construct($level, $bubble);
        $this->to = is_array($to) ? $to : array($to);
        $this->subject = $subject;
        $this->addHeader(sprintf('From: %s', $from));
        $this->maxColumnWidth = $maxColumnWidth;
    }
    public function addHeader($headers)
    {
        foreach ((array) $headers as $header) {
            if (strpos($header, "\n") !== false || strpos($header, "\r") !== false) {
                throw new \InvalidArgumentException('Headers can not contain newline characters for security reasons');
            }
            $this->headers[] = $header;
        }
        return $this;
    }
    public function addParameter($parameters)
    {
        $this->parameters = array_merge($this->parameters, (array) $parameters);
        return $this;
    }
    protected function send($content, array $records)
    {
        $content = wordwrap($content, $this->maxColumnWidth);
        $headers = ltrim(implode("\r\n", $this->headers) . "\r\n", "\r\n");
        $headers .= 'Content-type: ' . $this->getContentType() . '; charset=' . $this->getEncoding() . "\r\n";
        if ($this->getContentType() == 'text/html' && false === strpos($headers, 'MIME-Version:')) {
            $headers .= 'MIME-Version: 1.0' . "\r\n";
        }
        $subject = $this->subject;
        if ($records) {
            $subjectFormatter = new LineFormatter($this->subject);
            $subject = $subjectFormatter->format($this->getHighestRecord($records));
        }
        $parameters = implode(' ', $this->parameters);
        foreach ($this->to as $to) {
            mail($to, $subject, $content, $headers, $parameters);
        }
    }
    public function getContentType()
    {
        return $this->contentType;
    }
    public function getEncoding()
    {
        return $this->encoding;
    }
    public function setContentType($contentType)
    {
        if (strpos($contentType, "\n") !== false || strpos($contentType, "\r") !== false) {
            throw new \InvalidArgumentException('The content type can not contain newline characters to prevent email header injection');
        }
        $this->contentType = $contentType;
        return $this;
    }
    public function setEncoding($encoding)
    {
        if (strpos($encoding, "\n") !== false || strpos($encoding, "\r") !== false) {
            throw new \InvalidArgumentException('The encoding can not contain newline characters to prevent email header injection');
        }
        $this->encoding = $encoding;
        return $this;
    }
}
