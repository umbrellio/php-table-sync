<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\ReceivedMessageHandlers;

use Illuminate\Contracts\Bus\Dispatcher;
use Umbrellio\TableSync\Integration\Laravel\Jobs\ReceiveMessage;
use Umbrellio\TableSync\Messages\ReceivedMessage;
use Umbrellio\TableSync\ReceivedMessageHandler;

final class QueueReceivedMessageHandler implements ReceivedMessageHandler
{
    private $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function handle(ReceivedMessage $message): void
    {
        $this->dispatcher->dispatch(new ReceiveMessage($message));
    }
}
