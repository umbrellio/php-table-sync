<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Monolog\Formatter;

use Monolog\Formatter\NormalizerFormatter;

class TableSyncFormatter extends NormalizerFormatter
{
    public function format(array $record)
    {
        return [
            'datetime' => (string) $record['datetime'],
            'message' => $record['message'],
            'direction' => $this->getDirection($record),
            'routing' => $this->getRoutingKey($record),
            'model' => $this->getModel($record),
            'event' => $this->getEventType($record),
            'attributes' => $this->getAttributes($record),
            'exception' => $record['context']['exception'] ?? null,
        ];
    }

    protected function getDirection(array $record): string
    {
        return $record['context']['direction'] ?? '';
    }

    protected function getRoutingKey(array $record): string
    {
        return $record['context']['routing_key'] ?? '';
    }

    protected function getEventType(array $record): string
    {
        return $this->getBody($record)['event'] ?? '';
    }

    protected function getModel(array $record): string
    {
        return $this->getBody($record)['model'] ?? '';
    }

    protected function getAttributes(array $record): array
    {
        if (!$body = $this->getBody($record)) {
            return [];
        }

        $attributes = $this->wrapAttributes($body['attributes']);

        return $this->addVersions($attributes, $body['version']);
    }

    protected function getBody(array $record): array
    {
        return (array) json_decode($record['context']['body'] ?? '', true);
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
