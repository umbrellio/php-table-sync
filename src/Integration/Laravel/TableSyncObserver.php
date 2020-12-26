<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel;

use Umbrellio\TableSync\Integration\Laravel\Contracts\SyncableModel;

class TableSyncObserver
{
    private $syncer;

    public function __construct(Syncer $syncer)
    {
        $this->syncer = $syncer;
    }

    public function created(SyncableModel $model): void
    {
        $this->syncer->syncCreated($model);
    }

    public function updated(SyncableModel $model): void
    {
        $this->syncer->syncUpdated($model);
    }

    public function deleted(SyncableModel $model): void
    {
        if (method_exists($model, 'trashed') && $model->trashed()) {
            $this->updated($model);
            return;
        }

        $this->syncer->syncDeleted($model);
    }
}
