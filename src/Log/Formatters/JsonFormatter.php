<?php namespace KrisLux\Log\Formatters;

/**
 * Formatter for JSON
 *
 * Note that only the entries are valid JSON. If added to a running log file,
 * the whole file will be a simple list of JSON objects, not a valid JSON array,
 * due to the limitations of appending.
 */

use KrisLux\Log\Logger;
use KrisLux\Log\LogEntry;

class JsonFormatter
{
    public function format(LogEntry $entry)
    {
        $entry->timestamp = $entry->timestamp->format('c');

        $entry->level = Logger::getLevels()[$entry->level];

        $str = json_encode($entry, JSON_PRETTY_PRINT);

        return $str;
    }
}