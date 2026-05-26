<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel;

trait TableSyncable
{
    public static bool $isTableSyncEnabled = true;

    public static function bootTableSyncable(): void
    {
        if (static::$isTableSyncEnabled) {
            foreach (['created', 'updated', 'deleted'] as $event) {
                static::registerModelEvent($event, TableSyncObserver::class . '@' . $event);
            }
        }
    }

    public function getTableSyncableAttributes(): array
    {
        return $this->getAttributes();
    }

    public function getTableSyncableDestroyAttributes(): array
    {
        return [];
    }

    public function classForSync(): string
    {
        return static::class;
    }

    public function exists(): bool
    {
        return $this->exists;
    }
}
