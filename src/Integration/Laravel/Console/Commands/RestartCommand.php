<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Console\Commands;

use Umbrellio\TableSync\Integration\Laravel\Console\ProcessManagerCommand;

class RestartCommand extends ProcessManagerCommand
{
    protected $signature = 'table_sync:restart {--force}';
    protected $description = 'Restart table_sync worker.';

    public function handle(): void
    {
        if (!$this->pidManager->pidExists()) {
            $this->info('Table sync worker not started.');
        } else {
            $arguments = $this->option('force') ? [
                '--force' => true,
            ] : [];

            $this->call('table_sync:terminate', $arguments);
        }

        $this->call('table_sync:work');
    }
}
