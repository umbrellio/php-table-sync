<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\AdditionalDataHandlers;

use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\AdditionalDataHandler;
use Umbrellio\TableSync\Messages\ReceivedMessage;

final class DefaultDataHandler implements AdditionalDataHandler
{
    public function handle(ReceivedMessage $message, array $data): array
    {
        return $data;
    }
}
