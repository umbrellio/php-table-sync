<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Receive\Savers;

use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\MessageData;

interface Saver
{
    public function upsert(MessageData $messageData, float $version): void;

    public function destroy(MessageData $messageData): void;
}
