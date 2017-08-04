<?php namespace KrisLux\Log\Formatters;

use KrisLux\Log\LogEntry;

class SimpleLogFormatter
{
    public function format(LogEntry $entry)
    {
        $time = $entry->timestamp->format('Y-m-d H:i:s');
        $context = json_encode($entry->context);
        $origin = json_encode($entry->origin);

        $str = sprintf('[%s] %s %s %s', $time, $entry->message, $context, $origin);

        return $str;
    }
}