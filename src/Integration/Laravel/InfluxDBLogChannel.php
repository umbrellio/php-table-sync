<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel;

use Illuminate\Log\LogManager;
use InfluxDB\Database;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Umbrellio\TableSync\Monolog\Handler\InfluxDBHandler;

class InfluxDBLogChannel extends LogManager
{
    public function __invoke(array $config): LoggerInterface
    {
        $handler = new InfluxDBHandler(
            $this->app->make(Database::class),
            $config['measurement'] ?? null,
            $config['level'] ?? Logger::INFO,
            $config['bubble'] ?? true
        );

        return new Logger($this->parseChannel($config), [$handler]);
    }
}
