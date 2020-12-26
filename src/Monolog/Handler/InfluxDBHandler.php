<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Monolog\Handler;

use InfluxDB\Database;
use InvalidArgumentException;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Umbrellio\TableSync\Monolog\Formatter\InfluxDBFormatter;
use Umbrellio\TableSync\Monolog\Formatter\TableSyncFormatter;

class InfluxDBHandler extends AbstractProcessingHandler
{
    public const POINT_MEASUREMENT = 'table_sync';

    private $database;
    private $measurement;

    public function __construct(
        Database $database,
        ?string $measurement,
        int $level = Logger::INFO,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);

        $this->database = $database;
        $this->measurement = $measurement ?? self::POINT_MEASUREMENT;
    }

    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        if ($formatter instanceof TableSyncFormatter) {
            return parent::setFormatter($formatter);
        }

        throw new InvalidArgumentException('InfluxDBHandler is only compatible with TableSyncFormatter');
    }

    protected function write(array $record): void
    {
        $this->database->writePoints($record['formatted']);
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new InfluxDBFormatter($this->measurement);
    }
}
