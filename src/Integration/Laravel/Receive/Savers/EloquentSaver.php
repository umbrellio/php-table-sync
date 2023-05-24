<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Receive\Savers;

use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\MessageData;

class EloquentSaver implements Saver
{
    public function upsert(MessageData $messageData, float $version): void
    {
    }

    public function destroy(MessageData $messageData): void
    {
    }
}
