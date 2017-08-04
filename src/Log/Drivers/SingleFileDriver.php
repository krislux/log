<?php namespace KrisLux\Log\Drivers;

use KrisLux\Log\Formatters\FormatterInterface;
use KrisLux\Log\Formatters\SimpleLogFormatter;
use KrisLux\Log\LogEntry;

class SingleFileDriver extends Driver
{
    protected $handle = null;

    /**
     * 
     */

    public function __construct(array $options = [], FormatterInterface $formatter = null)
    {
        if ( ! $formatter) {
            $formatter = new SimpleLogFormatter;    // Default formatter if no other provided.
        }

        $this->options = [
            'path'      => 'log.txt',   // Path to log file
            'max_size'  => 1024,  // In KB
        ] + $options;

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

    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
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
            
            if ( ! file_exists($this->options['path']) || filesize($this->options['path']) > ($this->options['max_size'] * 1024)) {
                $mode = 'w';
            }

            $this->handle = fopen($this->options['path'], $mode);
        }

        $string = (string)$this->formatter->format($entry) . PHP_EOL;

        return fwrite($this->handle, $string) === strlen($string);
    }
}