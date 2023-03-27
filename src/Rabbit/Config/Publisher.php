<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Rabbit\Config;

class Publisher
{
    public function __construct(
        private readonly string $exchangeName,
        private readonly bool $confirmSelect = true,
        private readonly int $attempts = 3
    ) {
    }

    public function confirmSelect(): bool
    {
        return $this->confirmSelect;
    }

    public function exchangeName(): string
    {
        return $this->exchangeName;
    }

    public function attempts(): int
    {
        return $this->attempts;
    }
}
