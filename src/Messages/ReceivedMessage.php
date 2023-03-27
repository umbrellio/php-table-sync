<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Messages;

class ReceivedMessage
{
    public function __construct(
        private readonly string $event,
        private readonly string $model,
        private readonly array $attributes,
        private readonly float $version,
        private readonly array $metadata,
        private readonly string $appId,
        private readonly array $headers = []
    ) {
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getVersion(): float
    {
        return $this->version;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
