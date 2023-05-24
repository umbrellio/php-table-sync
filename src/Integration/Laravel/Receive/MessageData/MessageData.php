<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Receive\MessageData;

use Illuminate\Database\Eloquent\Model;

class MessageData
{
    /** @param null|class-string<Model> $model */
    public function __construct(
        private readonly ?string $table,
        private readonly ?string $model,
        private readonly array $targetKeys,
        private readonly array $data
    ) {
    }

    public function getTable(): ?string
    {
        return $this->table;
    }

    /** @return null|class-string<Model> */
    public function getModel(): ?string
    {
        return $this->model;
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
