<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Console\Commands;

use Safe;
use Umbrellio\TableSync\Integration\Laravel\Console\ProcessManagerCommand;
use Umbrellio\TableSync\Integration\Laravel\Exceptions\CannotTerminateWorker;

class TerminateCommand extends ProcessManagerCommand
{
    private const WAIT_WORKER_TIMEOUT = 5;

    protected $signature = 'table_sync:terminate {--force}';
    protected $description = 'Terminate table_sync worker.';

    public function handle(): void
    {
        if (!$this->pidManager->pidExists()) {
            $this->error('Table sync worker pid not found.');
            return;
        }

        $pid = $this->pidManager->readPid();

        if ($this->option('force')) {
            $this->killProcess($pid);
            $this->pidManager->removePid();
            return;
        }

        $this->terminateProcess($pid);
        $this->waitUntilWorkerTerminate();
    }

    private function killProcess(int $pid): void
    {
        Safe\posix_kill($pid, SIGKILL);
    }

    private function terminateProcess(int $pid): void
    {
        Safe\posix_kill($pid, SIGTERM);
    }

    private function waitUntilWorkerTerminate(): void
    {
        $i = 0;
        while ($this->pidManager->pidExists()) {
            Safe\sleep(1);
            $this->checkTimeout(++$i);
        }
    }

    private function checkTimeout(int $secondsSpent): void
    {
        if ($secondsSpent >= self::WAIT_WORKER_TIMEOUT) {
            throw new CannotTerminateWorker('Exceeded worker termination timeout');
        }
    }
}
