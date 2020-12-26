<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Messages;

class PublishMessage
{
    public const EVENT_DESTROYED = 'deleted';
    public const EVENT_CREATED = 'created';

    private $className;
    private $routingKey;
    private $event;
    private $attributes;

    public function __construct(string $className, string $event, string $routingKey, array $attributes = [])
    {
        $this->className = $className;
        $this->routingKey = $routingKey;
        $this->event = $event;
        $this->attributes = $attributes;
    }

    public function isDestroyed(): bool
    {
        return $this->event === self::EVENT_DESTROYED;
    }

    public function isCreated(): bool
    {
        return $this->event === self::EVENT_CREATED;
    }

    public function className(): string
    {
        return $this->className;
    }

    public function routingKey(): string
    {
        return $this->routingKey;
    }

    public function attributes(): array
    {
        return $this->attributes;
    }

    public function event(): string
    {
        return $this->event;
    }
}
