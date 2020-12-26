<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Console\Commands;

use Umbrellio\TableSync\Integration\Laravel\Console\ProcessManagerCommand;
use Umbrellio\TableSync\Rabbit\Consumer;

class WorkCommand extends ProcessManagerCommand
{
    protected $signature = 'table_sync:work';
    protected $description = 'Run table_sync worker';

    public function __destruct()
    {
        $this->pidManager->removePid();
    }

    public function handle(Consumer $consumer): void
    {
        if ($this->pidManager->pidExists()) {
            $this->warn("Table sync worker already running (PID: {$this->pidManager->readPid()})");
            return;
        }

        $this->pidManager->writePid();
        $this->listenTerminateSignals($consumer);

        $consumer->consume();
    }

    private function listenTerminateSignals(Consumer $consumer): void
    {
        pcntl_async_signals(true);

        pcntl_signal(SIGTERM, function () use ($consumer) {
            $this->line('Shutting down by SIGTERM.');

            $consumer->terminate();
        });

        pcntl_signal(SIGINT, function () use ($consumer) {
            $this->line('Shutting down by SIGINT');

            $consumer->terminate();
        });
    }
}
