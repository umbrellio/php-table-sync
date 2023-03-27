<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Monolog\Handler;

use InfluxDB\Database;
use InvalidArgumentException;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Level;
use Monolog\LogRecord;
use Umbrellio\TableSync\Monolog\Formatter\InfluxDBFormatter;
use Umbrellio\TableSync\Monolog\Formatter\TableSyncFormatter;

class InfluxDBHandler extends AbstractProcessingHandler
{
    public function __construct(
        private readonly Database $database,
        private readonly string $measurement = 'table_sync',
        int|string|Level $level = Level::Info,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);
    }

    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        if ($formatter instanceof TableSyncFormatter) {
            return parent::setFormatter($formatter);
        }

        throw new InvalidArgumentException('InfluxDBHandler is only compatible with TableSyncFormatter');
    }

    protected function write(LogRecord $record): void
    {
        $this->database->writePoints($record->formatted);
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new InfluxDBFormatter($this->measurement);
    }
}
