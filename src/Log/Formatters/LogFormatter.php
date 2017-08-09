<?php namespace KrisLux\Log\Formatters;

/**
 * Formatter for a basic server log format.
 *
 * Example:
 * 
 * [1970-01-01 00:00:00] WARNING Error message [] []
 */

use KrisLux\Log\Logger;
use KrisLux\Log\LogEntry;

class LogFormatter
{
    public function format(LogEntry $entry)
    {
        $time = $entry->timestamp->format('Y-m-d H:i:s');
        $context = json_encode($entry->context);
        $profile = json_encode($entry->profile);

        $level = Logger::getLevels()[$entry->level];

        $str = sprintf('[%s] %s %s %s %s', $time, $level, $entry->message, $context, $profile);

        return $str;
    }
}