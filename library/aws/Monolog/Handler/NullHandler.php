<?php
namespace Monolog\Handler;
use Monolog\Logger;
class NullHandler extends AbstractHandler
{
    public function __construct($level = Logger::DEBUG)
    {
        parent::__construct($level, false);
    }
    public function handle(array $record)
    {
        if ($record['level'] < $this->level) {
            return false;
        }
        return true;
    }
}
