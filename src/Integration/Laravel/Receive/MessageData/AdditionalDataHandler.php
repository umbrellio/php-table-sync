<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Receive\MessageData;

use Umbrellio\TableSync\Messages\ReceivedMessage;

interface AdditionalDataHandler
{
    public function handle(ReceivedMessage $message, array $data): array;
}
