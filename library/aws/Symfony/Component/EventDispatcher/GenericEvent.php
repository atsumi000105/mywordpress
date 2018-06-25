<?php
namespace Symfony\Component\EventDispatcher;
class GenericEvent extends Event implements \ArrayAccess, \IteratorAggregate
{
    protected $subject;
    protected $arguments;
    public function __construct($subject = null, array $arguments = array())
    {
        $this->subject = $subject;
        $this->arguments = $arguments;
    }
    public function getSubject()
    {
        return $this->subject;
    }
    public function getArgument($key)
    {
        if ($this->hasArgument($key)) {
            return $this->arguments[$key];
        }
        throw new \InvalidArgumentException(sprintf('Argument "%s" not found.', $key));
    }
    public function setArgument($key, $value)
    {
        $this->arguments[$key] = $value;
        return $this;
    }
    public function getArguments()
    {
        return $this->arguments;
    }
    public function setArguments(array $args = array())
    {
        $this->arguments = $args;
        return $this;
    }
    public function hasArgument($key)
    {
        return array_key_exists($key, $this->arguments);
    }
    public function offsetGet($key)
    {
        return $this->getArgument($key);
    }
    public function offsetSet($key, $value)
    {
        $this->setArgument($key, $value);
    }
    public function offsetUnset($key)
    {
        if ($this->hasArgument($key)) {
            unset($this->arguments[$key]);
        }
    }
    public function offsetExists($key)
    {
        return $this->hasArgument($key);
    }
    public function getIterator()
    {
        return new \ArrayIterator($this->arguments);
    }
}
