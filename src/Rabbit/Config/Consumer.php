<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Rabbit\Config;

use Umbrellio\TableSync\ReceivedMessageHandler;

class Consumer
{
    private int $microsecondsToSleep = 1000000;

    public function __construct(
        private readonly ReceivedMessageHandler $handler,
        private readonly string $queue,
        private readonly string $consumerTag = ''
    ) {
    }

    public function handler(): ReceivedMessageHandler
    {
        return $this->handler;
    }

    public function queue(): string
    {
        return $this->queue;
    }

    public function consumerTag(): string
    {
        return $this->consumerTag;
    }

    public function microsecondsToSleep(): int
    {
        return $this->microsecondsToSleep;
    }

    public function setMicrosecondsToSleep(int $microsecondsToSleep): self
    {
        $this->microsecondsToSleep = $microsecondsToSleep;

        return $this;
    }
}
