<?php

declare(strict_types=1);

namespace Umbrellio\TableSync\Monolog\Formatter;

class LineTableSyncFormatter extends TableSyncFormatter
{
    private $format = '[%datetime%] %message% %routing% %model% %event%';

    public function __construct(?string $format = null)
    {
        parent::__construct();

        if ($format !== null) {
            $this->format = $format;
        }
    }

    public function format(array $record): string
    {
        $vars = parent::format($record);

        $output = $this->format . PHP_EOL;

        foreach ($vars as $var => $value) {
            if (is_array($value)) {
                $output = str_replace("%{$var}%", json_encode($value), $output);
                continue;
            }

            if (strpos($output, "%{$var}%") !== false) {
                $output = str_replace("%{$var}%", $value, $output);
            }
        }

        if ($vars['exception'] !== null) {
            $output .= (string) $vars['exception'] . PHP_EOL;
        }

        return $output;
    }
}
