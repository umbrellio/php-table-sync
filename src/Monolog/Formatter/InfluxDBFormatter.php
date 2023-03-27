<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Monolog\Formatter;

use InfluxDB\Point;
use Monolog\LogRecord;

class InfluxDBFormatter extends TableSyncFormatter
{
    public function __construct(
        private readonly string $measurement
    ) {
        parent::__construct();
    }

    public function format(LogRecord $record): array
    {
        return [
            new Point(
                $this->measurement,
                count($this->getAttributes($record)),
                [
                    'model' => $this->getModel($record),
                    'event' => $this->getEventType($record),
                    'direction' => $this->getDirection($record),
                ]
            ),
        ];
    }
}
