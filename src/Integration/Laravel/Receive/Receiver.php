<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Receive;

use Umbrellio\TableSync\Integration\Laravel\Exceptions\UnknownMessageEvent;
use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\MessageDataRetriever;
use Umbrellio\TableSync\Messages\ReceivedMessage;

class Receiver
{
    public function __construct(
        private readonly MessageDataRetriever $dataRetriever
    ) {
    }

    public function receive(ReceivedMessage $message): void
    {
        $event = $message->getEvent();
        $data = $this->dataRetriever->retrieve($message);

        switch ($event) {
            case 'update':
                $data->upsert($message->getVersion());
                break;
            case 'destroy':
                $data->destroy();
                break;
            default:
                throw new UnknownMessageEvent("Unknown event: {$event}");
        }
    }
}
