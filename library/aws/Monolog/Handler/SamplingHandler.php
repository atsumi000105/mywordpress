<?php
namespace Monolog\Handler;
class SamplingHandler extends AbstractHandler
{
    protected $handler;
    protected $factor;
    public function __construct($handler, $factor)
    {
        parent::__construct();
        $this->handler = $handler;
        $this->factor = $factor;
        if (!$this->handler instanceof HandlerInterface && !is_callable($this->handler)) {
            throw new \RuntimeException("The given handler (".json_encode($this->handler).") is not a callable nor a Monolog\Handler\HandlerInterface object");
        }
    }
    public function isHandling(array $record)
    {
        return $this->handler->isHandling($record);
    }
    public function handle(array $record)
    {
        if ($this->isHandling($record) && mt_rand(1, $this->factor) === 1) {
            if (!$this->handler instanceof HandlerInterface) {
                $this->handler = call_user_func($this->handler, $record, $this);
                if (!$this->handler instanceof HandlerInterface) {
                    throw new \RuntimeException("The factory callable should return a HandlerInterface");
                }
            }
            if ($this->processors) {
                foreach ($this->processors as $processor) {
                    $record = call_user_func($processor, $record);
                }
            }
            $this->handler->handle($record);
        }
        return false === $this->bubble;
    }
}
