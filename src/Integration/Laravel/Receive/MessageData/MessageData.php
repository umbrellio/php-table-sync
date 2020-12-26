<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Receive\MessageData;

class MessageData
{
    private $table;
    private $targetKeys;
    private $data;

    public function __construct(string $table, array $targetKeys, array $data)
    {
        $this->table = $table;
        $this->targetKeys = $targetKeys;
        $this->data = $data;
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
