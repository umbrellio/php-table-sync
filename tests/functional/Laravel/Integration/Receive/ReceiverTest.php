<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Tests\functional\Laravel\Integration\Receive;

use Umbrellio\TableSync\Integration\Laravel\Exceptions\UnknownMessageEvent;
use Umbrellio\TableSync\Integration\Laravel\Receive\MessageData\MessageDataRetriever;
use Umbrellio\TableSync\Integration\Laravel\Receive\Receiver;
use Umbrellio\TableSync\Messages\ReceivedMessage;
use Umbrellio\TableSync\Tests\functional\Laravel\LaravelTestCase;

class ReceiverTest extends LaravelTestCase
{
    /**
     * @test
     */
    public function exceptionIfUnknownMessageEvent(): void
    {
        $messageDataRetreiver = $this->createMock(MessageDataRetriever::class);
        $receiver = new Receiver($messageDataRetreiver);

        $message = $this->createMock(ReceivedMessage::class);
        $message->method('getEvent')
            ->willReturn('test_event');

        $this->expectException(UnknownMessageEvent::class);
        $receiver->receive($message);
    }
}
