<?php
namespace Symfony\Component\EventDispatcher\Tests;
use Symfony\Component\EventDispatcher\EventDispatcher;
class EventDispatcherTest extends AbstractEventDispatcherTest
{
    protected function createEventDispatcher()
    {
        return new EventDispatcher();
    }
}
