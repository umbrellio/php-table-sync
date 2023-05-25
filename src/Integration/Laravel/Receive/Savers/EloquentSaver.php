<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Receive\Savers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\MessageData;

class EloquentSaver implements Saver
{
    private const LIMIT = 500;

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
        /** @var class-string<Model> $modelClass */
        $modelClass = $messageData->getTarget();

        foreach ($messageData->getData() as $item) {
            $query = $modelClass::query()
                ->where(Arr::only($item, $messageData->getTargetKeys()))
                ->limit(self::LIMIT);

            while ($query->count() !== 0) {
                $query->get()
                    ->each(fn (Model $model) => $model->forceDelete());
            }
        }
    }

    private function getQueryByTargetKeys(MessageData $messageData, array $item): Builder
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $messageData->getTarget();

        $query = $modelClass::query();
        foreach ($messageData->getTargetKeys() as $key) {
            $query->where($key, $item[$key]);
        }

        return $query;
    }

    private function fillAndSaveModel(Model $model, float $version, array $columns, array $values): void
    {
        foreach ($columns as $key) {
            $model->{$key} = $values[$key];
        }
        $model->setAttribute('version', $version);
        $model->save();
    }

    private function updateChanged(Builder $query, float $version, MessageData $messageData, array $item): void
    {
        $columns = array_keys($messageData->getData()[0]);
        $updateColumns = array_diff($columns, $messageData->getTargetKeys());

        $query->where('version', '<', $version)
            ->where(function (Builder $builder) use ($updateColumns, $item) {
                foreach ($updateColumns as $column) {
                    $builder->where($column, '!=', $item[$column]);
                }
            })
            ->limit(self::LIMIT);

        while ($query->count() !== 0) {
            $query->get()
                ->each(fn (Model $model) => $this->fillAndSaveModel($model, $version, $updateColumns, $item));
        }
    }
}
