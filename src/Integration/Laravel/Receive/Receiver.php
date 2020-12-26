<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Receive;

use Umbrellio\TableSync\Integration\Laravel\Exceptions\UnknownMessageEvent;
use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\MessageDataRetriever;
use Umbrellio\TableSync\Messages\ReceivedMessage;

class Receiver
{
    private $dataRetriever;

    public function __construct(MessageDataRetriever $dataRetriever)
    {
        $this->dataRetriever = $dataRetriever;
    }

    public function receive(ReceivedMessage $message): void
    {
        $event = $message->getEvent();
        $data = $this->dataRetriever->retrieve($message);

        switch ($event) {
            case 'update':
                $upserter = new Upserter();
                $upserter->upsert($data, $message->getVersion());
                break;
            case 'destroy':
                $destroyer = new Destroyer();
                $destroyer->destroy($data);
                break;
            default:
                throw new UnknownMessageEvent("Unknown event: {$event}");
        }
    }
}
