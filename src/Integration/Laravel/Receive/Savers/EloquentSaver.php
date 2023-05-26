<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Receive\Savers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\MessageData;

class EloquentSaver implements Saver
{
    private const DEFAULT_LIMIT = 500;

    public function upsert(MessageData $messageData, float $version): void
    {
        foreach ($messageData->getData() as $item) {
            $query = $this->getQueryByTargetKeys($messageData, $item);

            if ($query->count() === 0) {
                $model = new ($messageData->getTarget())();
                $this->fillAndSaveModel($model, $version, array_keys($item), $item);
                continue;
            }

            $this->updateChanged($query, $version, $messageData, $item);
        }
    }

    public function destroy(MessageData $messageData): void
    {
        foreach ($messageData->getData() as $item) {
            $query = $this
                ->getQueryByTargetKeys($messageData, $item)
                ->limit($this->getLimit());

            while ($query->count() !== 0) {
                $query
                    ->get()
                    ->each(fn (Model $model) => $model->forceDelete());
            }
        }
    }

    protected function getQueryByTargetKeys(MessageData $messageData, array $item): Builder
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $messageData->getTarget();

        return $modelClass::query()->where(Arr::only($item, $messageData->getTargetKeys()));
    }

    protected function fillAndSaveModel(Model $model, float $version, array $columns, array $values): void
    {
        foreach ($columns as $key) {
            $model->{$key} = $values[$key];
        }
        $model->setAttribute('version', $version);
        $model->save();
    }

    protected function updateChanged(Builder $query, float $version, MessageData $messageData, array $item): void
    {
        $columns = array_keys($messageData->getData()[0]);
        $updateColumns = array_diff($columns, $messageData->getTargetKeys());

        $query->where('version', '<', $version)
            ->where(function (Builder $builder) use ($updateColumns, $item) {
                foreach ($updateColumns as $column) {
                    $builder->orWhere($column, '!=', $item[$column]);
                }
            })
            ->limit($this->getLimit());

        while ($query->count() !== 0) {
            $query
                ->get()
                ->each(fn (Model $model) => $this->fillAndSaveModel($model, $version, $updateColumns, $item));
        }
    }

    protected function getLimit(): int
    {
        return Config::get('table_sync.receive.eloquent_chunk_size', self::DEFAULT_LIMIT);
    }
}
