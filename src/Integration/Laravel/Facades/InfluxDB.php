<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use InfluxDB\Database;

class InfluxDB extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Database::class;
    }
}
