<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Monolog\Handler;

use InfluxDB\Driver\UDP;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Umbrellio\TableSync\Monolog\Formatter\InfluxDBFormatter;

class TelegrafHandler extends AbstractProcessingHandler
{
    private $measurement;
    private $socket;

    public function __construct($host, $port, ?string $measurement, $level = Logger::INFO, $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->measurement = $measurement ?? InfluxDBHandler::POINT_MEASUREMENT;
        $this->socket = new UDP($host, $port);
    }

    protected function write(array $record): void
    {
        foreach ($record['formatted'] as $record) {
            $this->socket->write((string) $record);
        }
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new InfluxDBFormatter($this->measurement);
    }
}
