<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Console;

use Illuminate\Console\Command;

abstract class ProcessManagerCommand extends Command
{
    public function __construct(
        protected PidManager $pidManager
    ) {
        parent::__construct();
    }
}
