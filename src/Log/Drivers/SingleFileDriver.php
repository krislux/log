<?php namespace KrisLux\Log\Drivers;

/**
 * Driver for a single log file that automatically resets itself when it reaches a set size.
 *
 * Supported options:
 * path         Absolute filesystem path to the output file. Will NOT attempt to create folders.
 * max_size     In kilobytes, the maximum size of the log before resetting. Default is 1 MB.
 */

use KrisLux\Log\Formatters\FormatterInterface;
use KrisLux\Log\Formatters\LogFormatter;
use KrisLux\Log\LogEntry;

class SingleFileDriver extends Driver
{
    protected $handle = null;


    public function __construct(array $options = [], FormatterInterface $formatter = null)
    {
        if ( ! $formatter) {
            $formatter = new LogFormatter;    // Default formatter if no other provided.
        }

        $this->options = $options + [
            'path'      => getcwd() . '/log.txt',   // Path to log file
            'max_size'  => 1024,  // In KB. When reaching this size, log will simply start over.
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
            
            if ( ! file_exists($this->options['path']) || filesize($this->options['path']) > ($this->options['max_size'] * 1024)) {
                $mode = 'w';
            }

            $this->handle = fopen($this->options['path'], $mode);
        }

        $string = (string)$this->formatter->format($entry) . PHP_EOL;

        return fwrite($this->handle, $string) === strlen($string);
    }
}