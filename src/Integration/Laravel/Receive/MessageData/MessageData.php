<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Receive\MessageData;

class MessageData
{
    public function __construct(
        private readonly string $table,
        private readonly array $targetKeys,
        private readonly array $data
    ) {
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getTargetKeys(): array
    {
        return $this->targetKeys;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
