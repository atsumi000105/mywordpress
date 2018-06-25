<?php
namespace Monolog\Handler;
use Monolog\Logger;
class BufferHandler extends AbstractHandler
{
    protected $handler;
    protected $bufferSize = 0;
    protected $bufferLimit;
    protected $flushOnOverflow;
    protected $buffer = array();
    protected $initialized = false;
    public function __construct(HandlerInterface $handler, $bufferLimit = 0, $level = Logger::DEBUG, $bubble = true, $flushOnOverflow = false)
    {
        parent::__construct($level, $bubble);
        $this->handler = $handler;
        $this->bufferLimit = (int) $bufferLimit;
        $this->flushOnOverflow = $flushOnOverflow;
    }
    public function handle(array $record)
    {
        if ($record['level'] < $this->level) {
            return false;
        }
        if (!$this->initialized) {
            register_shutdown_function(array($this, 'close'));
            $this->initialized = true;
        }
        if ($this->bufferLimit > 0 && $this->bufferSize === $this->bufferLimit) {
            if ($this->flushOnOverflow) {
                $this->flush();
            } else {
                array_shift($this->buffer);
                $this->bufferSize--;
            }
        }
        if ($this->processors) {
            foreach ($this->processors as $processor) {
                $record = call_user_func($processor, $record);
            }
        }
        $this->buffer[] = $record;
        $this->bufferSize++;
        return false === $this->bubble;
    }
    public function flush()
    {
        if ($this->bufferSize === 0) {
            return;
        }
        $this->handler->handleBatch($this->buffer);
        $this->clear();
    }
    public function __destruct()
    {
    }
    public function close()
    {
        $this->flush();
    }
    public function clear()
    {
        $this->bufferSize = 0;
        $this->buffer = array();
    }
}
