<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Console;

use Safe;

class PidManager
{
    public function __construct(
        private readonly string $pathToPidFile
    ) {
    }

    public function pidExists(): bool
    {
        return file_exists($this->pathToPidFile);
    }

    public function readPid(): int
    {
        $pid = Safe\file_get_contents($this->pathToPidFile);
        return (int) $pid;
    }

    public function writePid(int $pid = null): void
    {
        $pid = $pid ?? posix_getpid();
        Safe\file_put_contents($this->pathToPidFile, $pid);
    }

    public function removePid(): void
    {
        if (!$this->pidExists()) {
            return;
        }

        unlink($this->pathToPidFile);
    }
}
