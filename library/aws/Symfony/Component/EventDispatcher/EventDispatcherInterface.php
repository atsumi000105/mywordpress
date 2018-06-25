<?php
namespace Symfony\Component\EventDispatcher;
interface EventDispatcherInterface
{
    public function dispatch($eventName, Event $event = null);
    public function addListener($eventName, $listener, $priority = 0);
    public function addSubscriber(EventSubscriberInterface $subscriber);
    public function removeListener($eventName, $listener);
    public function removeSubscriber(EventSubscriberInterface $subscriber);
    public function getListeners($eventName = null);
    public function hasListeners($eventName = null);
}
