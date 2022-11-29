<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Monolog\Formatter;

use Monolog\Utils;

class JsonTableSyncFormatter extends TableSyncFormatter
{
    public function format(array $record)
    {
        $record = parent::format($record);
        $formatted = [
            'datetime' => $record['datetime'],
            'message' => $record['message'],
            'direction' => $record['direction'],
            'routing' => $record['routing'],
            'model' => $record['model'],
            'event' => $record['event'],
            'count' => count($record['attributes']),
        ];

        return Utils::jsonEncode($formatted, Utils::DEFAULT_JSON_FLAGS, true) . "\n";
    }
}
