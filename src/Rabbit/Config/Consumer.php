<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Rabbit\Config;

use Umbrellio\TableSync\ReceivedMessageHandler;

class Consumer
{
    private $handler;
    private $queue;
    private $consumerTag;
    private $microsecondsToSleep = 1000000;

    public function __construct(ReceivedMessageHandler $handler, string $queue, string $consumerTag = '')
    {
        $this->handler = $handler;
        $this->queue = $queue;
        $this->consumerTag = $consumerTag;
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
