<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Contracts;

interface SyncableModel
{
    /**
     * @return string
     */
    public function getKeyName();

    /**
     * @return string
     */
    public function getKey();

    /**
     * @return static|null
     */
    public function fresh();

    /**
     * @return array
     */
    public function getTableSyncableAttributes();

    /**
     * @return array
     */
    public function getTableSyncableDeletedAttributes();

    /**
     * @return string
     */
    public function classForSync();

    /**
     * @return bool
     */
    public function exists();

    /**
     * @return string
     */
    public function routingKey();
}
