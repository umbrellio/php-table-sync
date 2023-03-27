<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Messages;

class PublishMessage
{
    public const EVENT_CREATED = 'created';
    public const EVENT_UPDATED = 'updated';
    public const EVENT_DELETED = 'deleted';

    public function __construct(
        private readonly string $className,
        private readonly string $event,
        private readonly string $routingKey,
        private readonly array $attributes = []
    ) {
    }

    public function isDestroyed(): bool
    {
        return $this->event === self::EVENT_DELETED;
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
