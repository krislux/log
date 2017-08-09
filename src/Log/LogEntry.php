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
     * Auto-generated information about the calling class/method and machine state.
     */

    public $profile = [];


    /**
     * 
     */

    public function __construct($level, $message, $context, $profile)
    {
        $this->timestamp = new DateTime();
        $this->level = $level;
        $this->message = $message;
        $this->context = $context;
        $this->profile = $profile;
    }
}