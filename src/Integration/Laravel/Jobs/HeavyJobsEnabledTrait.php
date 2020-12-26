<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Jobs;

use Illuminate\Support\Facades\Config;

trait HeavyJobsEnabledTrait
{
    public function isHeavyJobsEnabled()
    {
        return Config::get('table_sync.laravel_heavy_jobs_enabled', false);
    }
}
