<?php
namespace Symfony\Component\EventDispatcher\Debug;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
interface TraceableEventDispatcherInterface extends EventDispatcherInterface
{
    public function getCalledListeners();
    public function getNotCalledListeners();
}
