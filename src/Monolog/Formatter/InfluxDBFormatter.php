<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Monolog\Formatter;

use InfluxDB\Point;

class InfluxDBFormatter extends TableSyncFormatter
{
    private $measurement;

    public function __construct(string $measurement)
    {
        parent::__construct();

        $this->measurement = $measurement;
    }

    public function format(array $record): array
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
