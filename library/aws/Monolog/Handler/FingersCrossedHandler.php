<?php
namespace Monolog\Handler;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossed\ActivationStrategyInterface;
use Monolog\Logger;
class FingersCrossedHandler extends AbstractHandler
{
    protected $handler;
    protected $activationStrategy;
    protected $buffering = true;
    protected $bufferSize;
    protected $buffer = array();
    protected $stopBuffering;
    protected $passthruLevel;
    public function __construct($handler, $activationStrategy = null, $bufferSize = 0, $bubble = true, $stopBuffering = true, $passthruLevel = null)
    {
        if (null === $activationStrategy) {
            $activationStrategy = new ErrorLevelActivationStrategy(Logger::WARNING);
        }
        if (!$activationStrategy instanceof ActivationStrategyInterface) {
            $activationStrategy = new ErrorLevelActivationStrategy($activationStrategy);
        }
        $this->handler = $handler;
        $this->activationStrategy = $activationStrategy;
        $this->bufferSize = $bufferSize;
        $this->bubble = $bubble;
        $this->stopBuffering = $stopBuffering;
        if ($passthruLevel !== null) {
            $this->passthruLevel = Logger::toMonologLevel($passthruLevel);
        }
        if (!$this->handler instanceof HandlerInterface && !is_callable($this->handler)) {
            throw new \RuntimeException("The given handler (".json_encode($this->handler).") is not a callable nor a Monolog\Handler\HandlerInterface object");
        }
    }
    public function isHandling(array $record)
    {
        return true;
    }
    public function activate()
    {
        if ($this->stopBuffering) {
            $this->buffering = false;
        }
        if (!$this->handler instanceof HandlerInterface) {
            $record = end($this->buffer) ?: null;
            $this->handler = call_user_func($this->handler, $record, $this);
            if (!$this->handler instanceof HandlerInterface) {
                throw new \RuntimeException("The factory callable should return a HandlerInterface");
            }
        }
        $this->handler->handleBatch($this->buffer);
        $this->buffer = array();
    }
    public function handle(array $record)
    {
        if ($this->processors) {
            foreach ($this->processors as $processor) {
                $record = call_user_func($processor, $record);
            }
        }
        if ($this->buffering) {
            $this->buffer[] = $record;
            if ($this->bufferSize > 0 && count($this->buffer) > $this->bufferSize) {
                array_shift($this->buffer);
            }
            if ($this->activationStrategy->isHandlerActivated($record)) {
                $this->activate();
            }
        } else {
            $this->handler->handle($record);
        }
        return false === $this->bubble;
    }
    public function close()
    {
        if (null !== $this->passthruLevel) {
            $level = $this->passthruLevel;
            $this->buffer = array_filter($this->buffer, function ($record) use ($level) {
                return $record['level'] >= $level;
            });
            if (count($this->buffer) > 0) {
                $this->handler->handleBatch($this->buffer);
                $this->buffer = array();
            }
        }
    }
    public function reset()
    {
        $this->buffering = true;
    }
    public function clear()
    {
        $this->buffer = array();
        $this->reset();
    }
}
