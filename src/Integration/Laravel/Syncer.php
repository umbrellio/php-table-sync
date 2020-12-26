<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel;

use Umbrellio\TableSync\Integration\Laravel\Contracts\SyncableModel;
use Umbrellio\TableSync\Messages\PublishMessage;
use Umbrellio\TableSync\Publisher;

class Syncer
{
    private const EVENT_CREATED = 'created';
    private const EVENT_UPDATED = 'updated';
    private const EVENT_DELETED = 'deleted';

    private $publisher;

    public function __construct(Publisher $publisher)
    {
        $this->publisher = $publisher;
    }

    public function syncCreated(SyncableModel $model): void
    {
        $this->syncTable($model, self::EVENT_CREATED);
    }

    public function syncUpdated(SyncableModel $model): void
    {
        $this->syncTable($model, self::EVENT_UPDATED);
    }

    public function syncDeleted(SyncableModel $model): void
    {
        $this->syncTable($model, self::EVENT_DELETED);
    }

    private function syncTable(SyncableModel $model, string $event): void
    {
        $attributes = $this->getSyncableAttributes($model);

        if (!$this->needsPublishAttributes($model, $attributes)) {
            return;
        }

        $this->publisher->publish(new PublishMessage(
            $model->classForSync(),
            $event,
            $model->routingKey(),
            $attributes
        ));
    }

    private function getSyncableAttributes(SyncableModel $model): array
    {
        if (!$model->exists()) {
            return $this->pkAttributes($model);
        }

        return array_merge($this->pkAttributes($model), $model->getTableSyncableAttributes());
    }

    private function needsPublishAttributes(SyncableModel $model, array $attributes): bool
    {
        return !$model->exists() || $model->fresh();
    }

    private function pkAttributes(SyncableModel $model): array
    {
        return [$model->getKeyName() => $model->getKey()];
    }
}
