<?php namespace KrisLux\Log\Drivers;

use KrisLux\Log\Formatters\FormatterInterface;
use KrisLux\Log\LogEntry;

interface DriverInterface
{   
    public function __construct(array $options = [], FormatterInterface $formatter = null);

    /**
     * @param  callable   $filter   A lambda to filter when the driver is used. Current error level as the only parameter.
     *                              E.g. the lambda: function(x){return x==0;} will use the driver only for emergency level entries.
     */

    public function setFilter(callable $lambda);

    /**
     * Save or send the log entries provided.
     * @param  mixed  $entry  The formatted data ready to output.
     */
    
    public function write(LogEntry $entry);
}