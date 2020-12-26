<?php

declare(strict_types=1);

namespace Umbrellio\TableSync;

use Umbrellio\TableSync\Messages\ReceivedMessage;

interface ReceivedMessageHandler
{
    public function handle(ReceivedMessage $message): void;
}
