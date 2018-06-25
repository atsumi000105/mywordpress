<?php
namespace Monolog\Handler;
use Monolog\Logger;
class SocketHandler extends AbstractProcessingHandler
{
    private $connectionString;
    private $connectionTimeout;
    private $resource;
    private $timeout = 0;
    private $writingTimeout = 10;
    private $lastSentBytes = null;
    private $persistent = false;
    private $errno;
    private $errstr;
    private $lastWritingAt;
    public function __construct($connectionString, $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->connectionString = $connectionString;
        $this->connectionTimeout = (float) ini_get('default_socket_timeout');
    }
    protected function write(array $record)
    {
        $this->connectIfNotConnected();
        $data = $this->generateDataStream($record);
        $this->writeToSocket($data);
    }
    public function close()
    {
        if (!$this->isPersistent()) {
            $this->closeSocket();
        }
    }
    public function closeSocket()
    {
        if (is_resource($this->resource)) {
            fclose($this->resource);
            $this->resource = null;
        }
    }
    public function setPersistent($persistent)
    {
        $this->persistent = (boolean) $persistent;
    }
    public function setConnectionTimeout($seconds)
    {
        $this->validateTimeout($seconds);
        $this->connectionTimeout = (float) $seconds;
    }
    public function setTimeout($seconds)
    {
        $this->validateTimeout($seconds);
        $this->timeout = (float) $seconds;
    }
    public function setWritingTimeout($seconds)
    {
        $this->validateTimeout($seconds);
        $this->writingTimeout = (float) $seconds;
    }
    public function getConnectionString()
    {
        return $this->connectionString;
    }
    public function isPersistent()
    {
        return $this->persistent;
    }
    public function getConnectionTimeout()
    {
        return $this->connectionTimeout;
    }
    public function getTimeout()
    {
        return $this->timeout;
    }
    public function getWritingTimeout()
    {
        return $this->writingTimeout;
    }
    public function isConnected()
    {
        return is_resource($this->resource)
            && !feof($this->resource);  
    }
    protected function pfsockopen()
    {
        return @pfsockopen($this->connectionString, -1, $this->errno, $this->errstr, $this->connectionTimeout);
    }
    protected function fsockopen()
    {
        return @fsockopen($this->connectionString, -1, $this->errno, $this->errstr, $this->connectionTimeout);
    }
    protected function streamSetTimeout()
    {
        $seconds = floor($this->timeout);
        $microseconds = round(($this->timeout - $seconds) * 1e6);
        return stream_set_timeout($this->resource, $seconds, $microseconds);
    }
    protected function fwrite($data)
    {
        return @fwrite($this->resource, $data);
    }
    protected function streamGetMetadata()
    {
        return stream_get_meta_data($this->resource);
    }
    private function validateTimeout($value)
    {
        $ok = filter_var($value, FILTER_VALIDATE_FLOAT);
        if ($ok === false || $value < 0) {
            throw new \InvalidArgumentException("Timeout must be 0 or a positive float (got $value)");
        }
    }
    private function connectIfNotConnected()
    {
        if ($this->isConnected()) {
            return;
        }
        $this->connect();
    }
    protected function generateDataStream($record)
    {
        return (string) $record['formatted'];
    }
    protected function getResource()
    {
        return $this->resource;
    }
    private function connect()
    {
        $this->createSocketResource();
        $this->setSocketTimeout();
    }
    private function createSocketResource()
    {
        if ($this->isPersistent()) {
            $resource = $this->pfsockopen();
        } else {
            $resource = $this->fsockopen();
        }
        if (!$resource) {
            throw new \UnexpectedValueException("Failed connecting to $this->connectionString ($this->errno: $this->errstr)");
        }
        $this->resource = $resource;
    }
    private function setSocketTimeout()
    {
        if (!$this->streamSetTimeout()) {
            throw new \UnexpectedValueException("Failed setting timeout with stream_set_timeout()");
        }
    }
    private function writeToSocket($data)
    {
        $length = strlen($data);
        $sent = 0;
        $this->lastSentBytes = $sent;
        while ($this->isConnected() && $sent < $length) {
            if (0 == $sent) {
                $chunk = $this->fwrite($data);
            } else {
                $chunk = $this->fwrite(substr($data, $sent));
            }
            if ($chunk === false) {
                throw new \RuntimeException("Could not write to socket");
            }
            $sent += $chunk;
            $socketInfo = $this->streamGetMetadata();
            if ($socketInfo['timed_out']) {
                throw new \RuntimeException("Write timed-out");
            }
            if ($this->writingIsTimedOut($sent)) {
                throw new \RuntimeException("Write timed-out, no data sent for `{$this->writingTimeout}` seconds, probably we got disconnected (sent $sent of $length)");
            }
        }
        if (!$this->isConnected() && $sent < $length) {
            throw new \RuntimeException("End-of-file reached, probably we got disconnected (sent $sent of $length)");
        }
    }
    private function writingIsTimedOut($sent)
    {
        $writingTimeout = (int) floor($this->writingTimeout);
        if (0 === $writingTimeout) {
            return false;
        }
        if ($sent !== $this->lastSentBytes) {
            $this->lastWritingAt = time();
            $this->lastSentBytes = $sent;
            return false;
        } else {
            usleep(100);
        }
        if ((time() - $this->lastWritingAt) >= $writingTimeout) {
            $this->closeSocket();
            return true;
        }
        return false;
    }
}
