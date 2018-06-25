<?php
namespace Monolog\Handler;
use Monolog\Logger;
use Monolog\Formatter\NormalizerFormatter;
class NewRelicHandler extends AbstractProcessingHandler
{
    protected $appName;
    protected $transactionName;
    protected $explodeArrays;
    public function __construct(
        $level = Logger::ERROR,
        $bubble = true,
        $appName = null,
        $explodeArrays = false,
        $transactionName = null
    ) {
        parent::__construct($level, $bubble);
        $this->appName       = $appName;
        $this->explodeArrays = $explodeArrays;
        $this->transactionName = $transactionName;
    }
    protected function write(array $record)
    {
        if (!$this->isNewRelicEnabled()) {
            throw new MissingExtensionException('The newrelic PHP extension is required to use the NewRelicHandler');
        }
        if ($appName = $this->getAppName($record['context'])) {
            $this->setNewRelicAppName($appName);
        }
        if ($transactionName = $this->getTransactionName($record['context'])) {
            $this->setNewRelicTransactionName($transactionName);
            unset($record['formatted']['context']['transaction_name']);
        }
        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof \Exception) {
            newrelic_notice_error($record['message'], $record['context']['exception']);
            unset($record['formatted']['context']['exception']);
        } else {
            newrelic_notice_error($record['message']);
        }
        if (isset($record['formatted']['context']) && is_array($record['formatted']['context'])) {
            foreach ($record['formatted']['context'] as $key => $parameter) {
                if (is_array($parameter) && $this->explodeArrays) {
                    foreach ($parameter as $paramKey => $paramValue) {
                        $this->setNewRelicParameter('context_' . $key . '_' . $paramKey, $paramValue);
                    }
                } else {
                    $this->setNewRelicParameter('context_' . $key, $parameter);
                }
            }
        }
        if (isset($record['formatted']['extra']) && is_array($record['formatted']['extra'])) {
            foreach ($record['formatted']['extra'] as $key => $parameter) {
                if (is_array($parameter) && $this->explodeArrays) {
                    foreach ($parameter as $paramKey => $paramValue) {
                        $this->setNewRelicParameter('extra_' . $key . '_' . $paramKey, $paramValue);
                    }
                } else {
                    $this->setNewRelicParameter('extra_' . $key, $parameter);
                }
            }
        }
    }
    protected function isNewRelicEnabled()
    {
        return extension_loaded('newrelic');
    }
    protected function getAppName(array $context)
    {
        if (isset($context['appname'])) {
            return $context['appname'];
        }
        return $this->appName;
    }
    protected function getTransactionName(array $context)
    {
        if (isset($context['transaction_name'])) {
            return $context['transaction_name'];
        }
        return $this->transactionName;
    }
    protected function setNewRelicAppName($appName)
    {
        newrelic_set_appname($appName);
    }
    protected function setNewRelicTransactionName($transactionName)
    {
        newrelic_name_transaction($transactionName);
    }
    protected function setNewRelicParameter($key, $value)
    {
        if (null === $value || is_scalar($value)) {
            newrelic_add_custom_parameter($key, $value);
        } else {
            newrelic_add_custom_parameter($key, @json_encode($value));
        }
    }
    protected function getDefaultFormatter()
    {
        return new NormalizerFormatter();
    }
}
