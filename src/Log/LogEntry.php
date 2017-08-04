<?php namespace KrisLux\Log;

use DateTime;

class LogEntry
{    
    /**
     * The current time. Set automatically.
     */

    public $timestamp;

    /**
     * Severity level.
     */

    public $level;

    /**
     * The developer defined message part of the entry.
     */
    
    public $message;
    
    /**
     * Developer defined context data, if any.
     */

    public $context = [];

    /**
     * Trace information about the origin class/function of the log entry.
     */

    public $origin = [];


    /**
     * 
     */

    public function __construct($level, $message, $context, $origin)
    {
        $this->timestamp = new DateTime();
        $this->level = $level;
        $this->message = $message;
        $this->context = $context;
        $this->origin = $origin;
    }
}