<?php
namespace Monolog\Processor;
class MemoryPeakUsageProcessor extends MemoryProcessor
{
    public function __invoke(array $record)
    {
        $bytes = memory_get_peak_usage($this->realUsage);
        $formatted = $this->formatBytes($bytes);
        $record['extra']['memory_peak_usage'] = $formatted;
        return $record;
    }
}
