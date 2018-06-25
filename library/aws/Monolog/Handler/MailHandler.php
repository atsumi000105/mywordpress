<?php
namespace Monolog\Handler;
abstract class MailHandler extends AbstractProcessingHandler
{
    public function handleBatch(array $records)
    {
        $messages = array();
        foreach ($records as $record) {
            if ($record['level'] < $this->level) {
                continue;
            }
            $messages[] = $this->processRecord($record);
        }
        if (!empty($messages)) {
            $this->send((string) $this->getFormatter()->formatBatch($messages), $messages);
        }
    }
    abstract protected function send($content, array $records);
    protected function write(array $record)
    {
        $this->send((string) $record['formatted'], array($record));
    }
    protected function getHighestRecord(array $records)
    {
        $highestRecord = null;
        foreach ($records as $record) {
            if ($highestRecord === null || $highestRecord['level'] < $record['level']) {
                $highestRecord = $record;
            }
        }
        return $highestRecord;
    }
}
