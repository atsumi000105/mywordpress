<?php
namespace Monolog\Handler\FingersCrossed;
use Monolog\Logger;
class ChannelLevelActivationStrategy implements ActivationStrategyInterface
{
    private $defaultActionLevel;
    private $channelToActionLevel;
    public function __construct($defaultActionLevel, $channelToActionLevel = array())
    {
        $this->defaultActionLevel = Logger::toMonologLevel($defaultActionLevel);
        $this->channelToActionLevel = array_map('Monolog\Logger::toMonologLevel', $channelToActionLevel);
    }
    public function isHandlerActivated(array $record)
    {
        if (isset($this->channelToActionLevel[$record['channel']])) {
            return $record['level'] >= $this->channelToActionLevel[$record['channel']];
        }
        return $record['level'] >= $this->defaultActionLevel;
    }
}
