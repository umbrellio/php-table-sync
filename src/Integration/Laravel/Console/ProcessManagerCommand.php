<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Console;

use Illuminate\Console\Command;

abstract class ProcessManagerCommand extends Command
{
    protected $pidManager;

    public function __construct(PidManager $pidManager)
    {
        parent::__construct();
        $this->pidManager = $pidManager;
    }
}
