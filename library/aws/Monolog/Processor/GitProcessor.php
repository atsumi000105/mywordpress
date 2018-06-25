<?php
namespace Monolog\Processor;
use Monolog\Logger;
class GitProcessor
{
    private $level;
    private static $cache;
    public function __construct($level = Logger::DEBUG)
    {
        $this->level = Logger::toMonologLevel($level);
    }
    public function __invoke(array $record)
    {
        if ($record['level'] < $this->level) {
            return $record;
        }
        $record['extra']['git'] = self::getGitInfo();
        return $record;
    }
    private static function getGitInfo()
    {
        if (self::$cache) {
            return self::$cache;
        }
        $branches = `git branch -v --no-abbrev`;
        if (preg_match('{^\* (.+?)\s+([a-f0-9]{40})(?:\s|$)}m', $branches, $matches)) {
            return self::$cache = array(
                'branch' => $matches[1],
                'commit' => $matches[2],
            );
        }
        return self::$cache = array();
    }
}
