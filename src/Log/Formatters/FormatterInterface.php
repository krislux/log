<?php namespace KrisLux\Log\Formatters;

use KrisLux\Log\LogEntry;

interface FormatterInterface
{
    public function format(LogEntry $entry);
}