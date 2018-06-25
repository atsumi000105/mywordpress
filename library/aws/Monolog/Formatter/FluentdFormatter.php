<?php
namespace Monolog\Formatter;
class FluentdFormatter implements FormatterInterface
{
    protected $levelTag = false;
    public function __construct($levelTag = false)
    {
        if (!function_exists('json_encode')) {
            throw new \RuntimeException('PHP\'s json extension is required to use Monolog\'s FluentdUnixFormatter');
        }
        $this->levelTag = (bool) $levelTag;
    }
    public function isUsingLevelsInTag()
    {
        return $this->levelTag;
    }
    public function format(array $record)
    {
        $tag = $record['channel'];
        if ($this->levelTag) {
            $tag .= '.' . strtolower($record['level_name']);
        }
        $message = array(
            'message' => $record['message'],
            'extra' => $record['extra'],
        );
        if (!$this->levelTag) {
            $message['level'] = $record['level'];
            $message['level_name'] = $record['level_name'];
        }
        return json_encode(array($tag, $record['datetime']->getTimestamp(), $message));
    }
    public function formatBatch(array $records)
    {
        $message = '';
        foreach ($records as $record) {
            $message .= $this->format($record);
        }
        return $message;
    }
}
