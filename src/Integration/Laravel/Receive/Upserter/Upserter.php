<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Receive\Upserter;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\MessageData;
use Umbrellio\TableSync\Integration\Laravel\Receive\Upserter\ConflictConditionResolverContract;

class Upserter
{
    private $conflictConditionResolver;

    public function __construct(ConflictConditionResolverContract $conflictConditionResolver)
    {
        $this->conflictConditionResolver = $conflictConditionResolver;
    }

    // todo via modern expressive not existing query builder
    public function upsert(MessageData $messageData, float $version): void
    {
        if (empty($messageData->getData())) {
            return;
        }

        $data = $this->addVersionToItems($messageData->getData(), $version);

        $columns = array_keys($data[0]);
        $columnString = implode(',', array_map(function (string $column): string {
            return "\"{$column}\"";
        }, $columns));

        $values = $this->convertInsertValues($data);
        $target = $this->conflictConditionResolver->resolve($messageData);

        $valueBindings = Arr::flatten($data);

        $updateColumns = array_diff($columns, $messageData->getTargetKeys());
        $updateSpecString = $this->updateSpec($updateColumns);

        $sql = <<<CODE_SAMPLE
        INSERT INTO {$messageData->getTable()} ({$columnString}) VALUES {$values}
        ON CONFLICT {$target} 
        DO UPDATE 
          SET {$updateSpecString}
          WHERE {$messageData->getTable()}.version < ?
CODE_SAMPLE;

        DB::statement($sql, array_merge($valueBindings, [$version]));
    }

    private function addVersionToItems(array $items, float $version): array
    {
        return array_map(function ($item) use ($version) {
            return array_merge($item, compact('version'));
        }, $items);
    }

    private function convertInsertValues(array $items): string
    {
        $values = array_map(function (array $item) {
            $item = $this->implodedPlaceholders($item);
            return "({$item})";
        }, $items);

        return implode(',', $values);
    }

    private function updateSpec(array $columns): string
    {
        $values = array_map(function (string $column) {
            return "\"{$column}\" = EXCLUDED.{$column}";
        }, $columns);

        return implode(',', $values);
    }

    private function implodedPlaceholders(array $items, string $placeholder = '?'): string
    {
        return implode(',', array_map(function () use ($placeholder) {
            return $placeholder;
        }, $items));
    }
}
