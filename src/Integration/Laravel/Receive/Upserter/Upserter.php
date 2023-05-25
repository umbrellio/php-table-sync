<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Receive\Upserter;

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

        if ($messageData->getTarget()) {
            App::make(QuerySaver::class)->upsert($messageData, $version);
            return;
        }

        if ($messageData->getModel()) {
            App::make(EloquentSaver::class)->upsert($messageData, $version);
            return;
        }

        throw new InvalidArgumentException('Table or Model must be set');
    }
}
