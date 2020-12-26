<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Contracts;

interface SyncableModel
{
    /**
     * @var string
     */
    public function getKeyName();

    /**
     * @var string
     */
    public function getKey();

    /**
     * @var static|null
     */
    public function fresh();

    /**
     * @var array
     */
    public function getTableSyncableAttributes();

    /**
     * @var string
     */
    public function classForSync();

    /**
     * @var bool
     */
    public function exists();

    /**
     * @var string
     */
    public function routingKey();
}
