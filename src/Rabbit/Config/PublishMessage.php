<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Rabbit\Config;

use PhpAmqpLib\Wire\AMQPTable;

class PublishMessage
{
    public function __construct(
        private readonly string $appId,
        private readonly ?AMQPTable $headers = null
    ) {
    }

    public function headers(): ?AMQPTable
    {
        return $this->headers;
    }

    public function appId(): string
    {
        return $this->appId;
    }
}
