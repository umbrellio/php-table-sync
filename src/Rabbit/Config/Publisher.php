<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Rabbit\Config;

class Publisher
{
    private const DEFAULT_ATTEMPTS = 3;

    private $exchangeName;
    private $confirmSelect;
    private $attempts;

    public function __construct(
        string $exchangeName,
        bool $confirmSelect = true,
        int $attempts = self::DEFAULT_ATTEMPTS
    ) {
        $this->exchangeName = $exchangeName;
        $this->confirmSelect = $confirmSelect;
        $this->attempts = $attempts;
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
