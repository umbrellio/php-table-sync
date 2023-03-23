<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Integration\Laravel;

use Illuminate\Log\LogManager;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Umbrellio\TableSync\Monolog\Handler\TelegrafHandler;

class TelegrafLogChannel extends LogManager
{
    public function __invoke(array $config): LoggerInterface
    {
        $handler = new TelegrafHandler(
            config('telegraf.host'),
            config('telegraf.port'),
            $config['measurement'] ?? null,
            $config['level'] ?? Level::Info,
            $config['bubble'] ?? true
        );

        return new Logger($this->parseChannel($config), [$handler]);
    }
}
