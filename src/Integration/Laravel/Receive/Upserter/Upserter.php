<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Receive\Upserter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use InvalidArgumentException;
use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\MessageData;
use Umbrellio\TableSync\Integration\Laravel\Receive\Savers\EloquentSaver;
use Umbrellio\TableSync\Integration\Laravel\Receive\Savers\QuerySaver;

class Upserter
{
    public function upsert(MessageData $messageData, float $version): void
    {
        if (empty(array_filter($messageData->getData()))) {
            return;
        }

        if ($messageData->getTable()) {
            App::make(QuerySaver::class)->upsert($messageData, $version);
            return;
        }

        if ($messageData->getModel()) {
            App::make(EloquentSaver::class)->upsert($messageData, $version);
            return;
        }

        throw new InvalidArgumentException('Table or Model must be set');
    }

//    private function upsertByModel(array $data, array $columns, MessageData $messageData, float $version): void
//    {
//        /** @var class-string<Model> $modelClass */
//        $modelClass = $messageData->getModel();
//
//        foreach ($data as $item) {
//            $query = $modelClass::query();
//            foreach ($messageData->getTargetKeys() as $key) {
//                $query->orWhere($key, $item[$key]);
//            }
//            $collection = $query->get();
//
//            if ($collection->count() === 0) {
//                $model = new $modelClass();
//                foreach ($item as $key => $value) {
//                    $model->{$key} = $value;
//                }
//                $model->save();
//                continue;
//            }
//
//            foreach ($collection as $model) {
//
//            }
//        }
//
//    }
}
