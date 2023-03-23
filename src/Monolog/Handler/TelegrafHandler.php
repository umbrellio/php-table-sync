<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Monolog\Handler;

use InfluxDB\Driver\UDP;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Umbrellio\TableSync\Monolog\Formatter\InfluxDBFormatter;

class TelegrafHandler extends AbstractProcessingHandler
{
    private UDP $socket;

    public function __construct(
        $host,
        $port,
        private readonly string $measurement = 'table_sync',
        int|string|Level $level = Level::Info,
        $bubble = true
    ) {
        parent::__construct($level, $bubble);

        $this->socket = new UDP($host, $port);
    }

    protected function write(LogRecord $record): void
    {
        if (!is_iterable($record->formatted)) {
            return;
        }

        foreach ($record->formatted as $formatted) {
            $this->socket->write((string) $formatted);
        }
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new InfluxDBFormatter($this->measurement);
    }
}
