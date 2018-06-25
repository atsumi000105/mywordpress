<?php
namespace Monolog\Handler;
class WhatFailureGroupHandler extends GroupHandler
{
    public function handle(array $record)
    {
        if ($this->processors) {
            foreach ($this->processors as $processor) {
                $record = call_user_func($processor, $record);
            }
        }
        foreach ($this->handlers as $handler) {
            try {
                $handler->handle($record);
            } catch (\Exception $e) {
            } catch (\Throwable $e) {
            }
        }
        return false === $this->bubble;
    }
    public function handleBatch(array $records)
    {
        foreach ($this->handlers as $handler) {
            try {
                $handler->handleBatch($records);
            } catch (\Exception $e) {
            } catch (\Throwable $e) {
            }
        }
    }
}
