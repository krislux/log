<?php namespace KrisLux\Log\Drivers;

/**
 * Driver for a set of rotating log files.
 *
 * Supported options:
 * dir          Absolute path to the directory to store the logs in. Defaults to ./logs
 * name         Naming scheme in date() format. Defaults to "Ymd\.\l\o\g". Precision determines rate of rotation.
 */

use KrisLux\Log\Formatters\FormatterInterface;
use KrisLux\Log\Formatters\LogFormatter;
use KrisLux\Log\LogEntry;

class RotatingFileDriver extends Driver
{
    protected $handle = null;


    public function __construct(array $options = [], FormatterInterface $formatter = null)
    {
        if ( ! $formatter) {
            $formatter = new LogFormatter;    // Default formatter if no other provided.
        }

        $this->options = $options + [
            'dir'       => getcwd() . '/logs/',   // Dir to store log files.
            'name' => 'Ymd\.\l\o\g'   // Naming scheme for log file.
        ];
        
        $this->formatter = $formatter;
    }

    /**
     * Finalizer to close file connection
     */

    public function __destruct()
    {
        if ($this->handle) {
            fclose($this->handle);
        }
    }

    public function write(LogEntry $entry)
    {
        if ( ! $this->testFilter($entry->level)) {
            return false;
        }
        
        /**
         * Hook into filestream on first write.
         */
        if ( ! $this->handle) {
            $mode = 'a';

            $dir = rtrim($this->options['dir'], "\\/") . DIRECTORY_SEPARATOR;
            $path = $dir . date($this->options['name']);

            $this->handle = fopen($path, $mode);
        }

        $string = (string)$this->formatter->format($entry) . PHP_EOL;

        return fwrite($this->handle, $string) === strlen($string);
    }
}