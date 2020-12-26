<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel;

trait TableSyncable
{
    public static $isTableSyncEnabled = true;

    public static function bootTableSyncable()
    {
        if (static::$isTableSyncEnabled) {
            static::observe(TableSyncObserver::class);
        }
    }

    public function getTableSyncableAttributes(): array
    {
        return $this->getAttributes();
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
