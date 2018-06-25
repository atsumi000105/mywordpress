<?php
namespace Monolog\Handler;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
abstract class AbstractSyslogHandler extends AbstractProcessingHandler
{
    protected $facility;
    protected $logLevels = array(
        Logger::DEBUG     => LOG_DEBUG,
        Logger::INFO      => LOG_INFO,
        Logger::NOTICE    => LOG_NOTICE,
        Logger::WARNING   => LOG_WARNING,
        Logger::ERROR     => LOG_ERR,
        Logger::CRITICAL  => LOG_CRIT,
        Logger::ALERT     => LOG_ALERT,
        Logger::EMERGENCY => LOG_EMERG,
    );
    protected $facilities = array(
        'auth'     => LOG_AUTH,
        'authpriv' => LOG_AUTHPRIV,
        'cron'     => LOG_CRON,
        'daemon'   => LOG_DAEMON,
        'kern'     => LOG_KERN,
        'lpr'      => LOG_LPR,
        'mail'     => LOG_MAIL,
        'news'     => LOG_NEWS,
        'syslog'   => LOG_SYSLOG,
        'user'     => LOG_USER,
        'uucp'     => LOG_UUCP,
    );
    public function __construct($facility = LOG_USER, $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            $this->facilities['local0'] = LOG_LOCAL0;
            $this->facilities['local1'] = LOG_LOCAL1;
            $this->facilities['local2'] = LOG_LOCAL2;
            $this->facilities['local3'] = LOG_LOCAL3;
            $this->facilities['local4'] = LOG_LOCAL4;
            $this->facilities['local5'] = LOG_LOCAL5;
            $this->facilities['local6'] = LOG_LOCAL6;
            $this->facilities['local7'] = LOG_LOCAL7;
        } else {
            $this->facilities['local0'] = 128; 
            $this->facilities['local1'] = 136; 
            $this->facilities['local2'] = 144; 
            $this->facilities['local3'] = 152; 
            $this->facilities['local4'] = 160; 
            $this->facilities['local5'] = 168; 
            $this->facilities['local6'] = 176; 
            $this->facilities['local7'] = 184; 
        }
        if (array_key_exists(strtolower($facility), $this->facilities)) {
            $facility = $this->facilities[strtolower($facility)];
        } elseif (!in_array($facility, array_values($this->facilities), true)) {
            throw new \UnexpectedValueException('Unknown facility value "'.$facility.'" given');
        }
        $this->facility = $facility;
    }
    protected function getDefaultFormatter()
    {
        return new LineFormatter('%channel%.%level_name%: %message% %context% %extra%');
    }
}
