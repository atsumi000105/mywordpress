<?php
namespace Monolog\Formatter;
class MongoDBFormatter implements FormatterInterface
{
    private $exceptionTraceAsString;
    private $maxNestingLevel;
    public function __construct($maxNestingLevel = 3, $exceptionTraceAsString = true)
    {
        $this->maxNestingLevel = max($maxNestingLevel, 0);
        $this->exceptionTraceAsString = (bool) $exceptionTraceAsString;
    }
    public function format(array $record)
    {
        return $this->formatArray($record);
    }
    public function formatBatch(array $records)
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }
        return $records;
    }
    protected function formatArray(array $record, $nestingLevel = 0)
    {
        if ($this->maxNestingLevel == 0 || $nestingLevel <= $this->maxNestingLevel) {
            foreach ($record as $name => $value) {
                if ($value instanceof \DateTime) {
                    $record[$name] = $this->formatDate($value, $nestingLevel + 1);
                } elseif ($value instanceof \Exception) {
                    $record[$name] = $this->formatException($value, $nestingLevel + 1);
                } elseif (is_array($value)) {
                    $record[$name] = $this->formatArray($value, $nestingLevel + 1);
                } elseif (is_object($value)) {
                    $record[$name] = $this->formatObject($value, $nestingLevel + 1);
                }
            }
        } else {
            $record = '[...]';
        }
        return $record;
    }
    protected function formatObject($value, $nestingLevel)
    {
        $objectVars = get_object_vars($value);
        $objectVars['class'] = get_class($value);
        return $this->formatArray($objectVars, $nestingLevel);
    }
    protected function formatException(\Exception $exception, $nestingLevel)
    {
        $formattedException = array(
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile() . ':' . $exception->getLine(),
        );
        if ($this->exceptionTraceAsString === true) {
            $formattedException['trace'] = $exception->getTraceAsString();
        } else {
            $formattedException['trace'] = $exception->getTrace();
        }
        return $this->formatArray($formattedException, $nestingLevel);
    }
    protected function formatDate(\DateTime $value, $nestingLevel)
    {
        return new \MongoDate($value->getTimestamp());
    }
}
