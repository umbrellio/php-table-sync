<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Monolog\Formatter;

use Monolog\LogRecord;
use Monolog\Utils;

class JsonTableSyncFormatter extends TableSyncFormatter
{
    /** @return string */
    public function format(LogRecord $record)
    {
        $formattedRecord = parent::format($record);
        $formatted = [
            'datetime' => $formattedRecord['datetime'],
            'message' => $formattedRecord['message'],
            'direction' => $formattedRecord['direction'],
            'routing' => $formattedRecord['routing'],
            'model' => $formattedRecord['model'],
            'event' => $formattedRecord['event'],
            'count' => count($formattedRecord['attributes']),
        ];

        return Utils::jsonEncode($formatted, Utils::DEFAULT_JSON_FLAGS, true) . "\n";
    }
}
