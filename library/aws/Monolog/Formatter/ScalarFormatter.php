<?php
namespace Monolog\Formatter;
class ScalarFormatter extends NormalizerFormatter
{
    public function format(array $record)
    {
        foreach ($record as $key => $value) {
            $record[$key] = $this->normalizeValue($value);
        }
        return $record;
    }
    protected function normalizeValue($value)
    {
        $normalized = $this->normalize($value);
        if (is_array($normalized) || is_object($normalized)) {
            return $this->toJson($normalized, true);
        }
        return $normalized;
    }
}
