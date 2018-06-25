<?php
namespace Monolog\Handler;
use Monolog\Logger;
class SyslogHandler extends AbstractSyslogHandler
{
    protected $ident;
    protected $logopts;
    public function __construct($ident, $facility = LOG_USER, $level = Logger::DEBUG, $bubble = true, $logopts = LOG_PID)
    {
        parent::__construct($facility, $level, $bubble);
        $this->ident = $ident;
        $this->logopts = $logopts;
    }
    public function close()
    {
        closelog();
    }
    protected function write(array $record)
    {
        if (!openlog($this->ident, $this->logopts, $this->facility)) {
            throw new \LogicException('Can\'t open syslog for ident "'.$this->ident.'" and facility "'.$this->facility.'"');
        }
        syslog($this->logLevels[$record['level']], (string) $record['formatted']);
    }
}
