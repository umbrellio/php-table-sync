<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Messages;

class ReceivedMessage
{
    private $event;
    private $model;
    private $attributes;
    private $version;
    private $metadata;
    private $appId;
    private $headers;

    public function __construct(
        string $event,
        string $model,
        array $attributes,
        float $version,
        array $metadata,
        string $appId,
        array $headers = []
    ) {
        $this->event = $event;
        $this->model = $model;
        $this->attributes = $attributes;
        $this->version = $version;
        $this->metadata = $metadata;
        $this->appId = $appId;
        $this->headers = $headers;
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
