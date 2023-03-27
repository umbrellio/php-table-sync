<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Monolog\Formatter;

use Monolog\LogRecord;

class LineTableSyncFormatter extends TableSyncFormatter
{
    public function __construct(
        private readonly string $format = '[%datetime%] %message% %routing% %model% %event%'
    ) {
        parent::__construct();
    }

    public function format(LogRecord $record): string
    {
        $vars = parent::format($record);

        $output = $this->format . PHP_EOL;

        foreach ($vars as $var => $value) {
            if (is_array($value)) {
                $output = str_replace("%{$var}%", json_encode($value), $output);
                continue;
            }

            if (str_contains($output, "%{$var}%")) {
                $output = str_replace("%{$var}%", $value, $output);
            }
        }

        if ($vars['exception'] !== null) {
            $output .= (string) $vars['exception'] . PHP_EOL;
        }

        return $output;
    }
}
