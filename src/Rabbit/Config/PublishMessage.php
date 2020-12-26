<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Rabbit\Config;

use PhpAmqpLib\Wire\AMQPTable;

class PublishMessage
{
    private $headers;
    private $appId;

    public function __construct(string $appId, ?AMQPTable $headers = null)
    {
        $this->appId = $appId;
        $this->headers = $headers;
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
