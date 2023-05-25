<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Receive\MessageData;

use Illuminate\Database\Eloquent\Model;
use Umbrellio\TableSync\Integration\Laravel\Receive\Savers\Saver;

class MessageData
{
    public function __construct(
        private readonly string $target,
        private readonly Saver $saver,
        private readonly array $targetKeys,
        private readonly array $data,
    ) {
    }

    /** @return non-empty-string|class-string<Model> */
    public function getTarget(): string
    {
        return $this->target;
    }

    public function getTargetKeys(): array
    {
        return $this->targetKeys;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function upsert(float $version): void
    {
        if ($this->isEmpty()) {
            return;
        }
        $this->saver->upsert($this, $version);
    }

    public function destroy(): void
    {
        if ($this->isEmpty()) {
            return;
        }
        $this->saver->destroy($this);
    }

    private function isEmpty(): bool
    {
        return empty(array_filter($this->data));
    }
}
