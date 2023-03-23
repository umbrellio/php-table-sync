<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Monolog\Formatter;

use Monolog\Formatter\NormalizerFormatter;
use Monolog\LogRecord;

class TableSyncFormatter extends NormalizerFormatter
{
    /** @return array */
    public function format(LogRecord $record)
    {
        return [
            'datetime' => $record->datetime->format('Y-m-d\TH:i:s.uP'),
            'message' => $record['message'],
            'direction' => $this->getDirection($record),
            'routing' => $this->getRoutingKey($record),
            'model' => $this->getModel($record),
            'event' => $this->getEventType($record),
            'attributes' => $this->getAttributes($record),
            'exception' => $record['context']['exception'] ?? null,
        ];
    }

    protected function getDirection(LogRecord $record): string
    {
        return $record->context['direction'] ?? '';
    }

    protected function getRoutingKey(LogRecord $record): string
    {
        return $record->context['routing_key'] ?? '';
    }

    protected function getEventType(LogRecord $record): string
    {
        return $this->getBody($record)['event'] ?? '';
    }

    protected function getModel(LogRecord $record): string
    {
        return $this->getBody($record)['model'] ?? '';
    }

    protected function getAttributes(LogRecord $record): array
    {
        if (!$body = $this->getBody($record)) {
            return [];
        }

        $attributes = $this->wrapAttributes($body['attributes']);

        return $this->addVersions($attributes, $body['version']);
    }

    protected function getBody(LogRecord $record): array
    {
        return (array) json_decode($record->context['body'] ?? '', true);
    }

    private function wrapAttributes(array $attributes): array
    {
        $keys = array_keys($attributes);

        $isArray = collect($keys)
            ->every(function ($key) {
                return is_numeric($key);
            });

        return $isArray ? $attributes : [$attributes];
    }

    private function addVersions(array $items, float $version): array
    {
        return array_map(function ($item) use ($version) {
            return array_merge($item, compact('version'));
        }, $items);
    }
}
