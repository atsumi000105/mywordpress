<?php
namespace Monolog\Handler\FingersCrossed;
use Monolog\Logger;
class ErrorLevelActivationStrategy implements ActivationStrategyInterface
{
    private $actionLevel;
    public function __construct($actionLevel)
    {
        $this->actionLevel = Logger::toMonologLevel($actionLevel);
    }
    public function isHandlerActivated(array $record)
    {
        return $record['level'] >= $this->actionLevel;
    }
}
