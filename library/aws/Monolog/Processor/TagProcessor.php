<?php
namespace Monolog\Processor;
class TagProcessor
{
    private $tags;
    public function __construct(array $tags = array())
    {
        $this->setTags($tags);
    }
    public function addTags(array $tags = array())
    {
        $this->tags = array_merge($this->tags, $tags);
    }
    public function setTags(array $tags = array())
    {
        $this->tags = $tags;
    }
    public function __invoke(array $record)
    {
        $record['extra']['tags'] = $this->tags;
        return $record;
    }
}
