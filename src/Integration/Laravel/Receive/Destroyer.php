<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Receive;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\MessageData;

class Destroyer
{
    public function destroy(MessageData $message): void
    {
        if (empty(array_filter($message->getData()))) {
            return;
        }

        $query = DB::table($message->getTable());
        foreach ($message->getData() as $itemData) {
            $query->orWhere(function (Builder $builder) use ($message, $itemData) {
                $builder->where(Arr::only($itemData, $message->getTargetKeys()));
            });
        }
        $query->delete();
    }
}
