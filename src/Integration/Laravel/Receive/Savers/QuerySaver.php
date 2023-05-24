<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Receive\Savers;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\MessageData;
use Umbrellio\TableSync\Integration\Laravel\Receive\Upserter\ConflictConditionResolverContract;

class QuerySaver implements Saver
{
    public function __construct(
        private readonly ConflictConditionResolverContract $conflictConditionResolver
    ) {
    }

    // todo via modern expressive not existing query builder
    public function upsert(MessageData $messageData, float $version): void
    {
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

    public function destroy(MessageData $messageData): void
    {
        $query = DB::table($messageData->getTable());
        foreach ($messageData->getData() as $itemData) {
            $query->orWhere(function (Builder $builder) use ($messageData, $itemData) {
                $builder->where(Arr::only($itemData, $messageData->getTargetKeys()));
            });
        }
        $query->delete();
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
