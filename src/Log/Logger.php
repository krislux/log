<?php namespace KrisLux\Log;

use Exception;
use ErrorException;
use Psr\Log\LoggerInterface;
use Psr\Log\InvalidArgumentException;
use KrisLux\Log\Drivers\DriverInterface;
use KrisLux\Log\LogEntry;
use KrisLux\Log\Handler;

class Logger implements LoggerInterface
{
    protected $options = [];

    protected $drivers = [];

    protected $deferred_entries = [];

    /**
     * RFC 5424
     */

    const EMERGENCY = 0;    // system is unusable
    const ALERT = 1;        // action must be taken immediately
    const CRITICAL = 2;     // critical conditions
    const ERROR = 3;        // error conditions
    const WARNING = 4;      // warning conditions
    const NOTICE = 5;       // normal but significant conditions
    const INFO = 6;         // informational messages
    const DEBUG = 7;        // debug-level messages

    protected static $levels = [
        self::EMERGENCY => 'EMERGENCY',
        self::ALERT     => 'ALERT',
        self::ERROR     => 'ERROR',
        self::CRITICAL  => 'CRITICAL',
        self::WARNING   => 'WARNING',
        self::NOTICE    => 'NOTICE',
        self::INFO      => 'INFO',
        self::DEBUG     => 'DEBUG'
    ];

    /**
     * Return the severity level list.
     * By default with the level int as key and string as value, but optionally inverted.
     * @param  bool  $inverted  Flip the array so the string is key.
     * @return array
     */
    
    public static function getLevels($inverted = false)
    {
        if ($inverted) {
            return array_flip(self::$levels);
        }
        return self::$levels;
    }

    /**
     * @param  array  $options Any custom settings as text array for easier extraction to settings file.
     * @param  ...    splat    Optional list of drivers to use.
     */
    
    public function __construct(array $options = [])
    {
        // Set default values and let arg override them.
        $this->options = [
            'dev' => true,        // Dev mode as opposed to production. Includes all logging levels.
            'trace' => true,      // Include trace information in errors and above.
            'memory' => true,     // Include memory information in errors and above.
            'defer' => false,     // Cache entries and log all at once on shutdown rather than immediately.
            'quiet' => false      // If true, ignores any write failures. Otherwise ErrorException is thrown.
        ] + $options;
        
        foreach (func_get_args() as $arg) {
            if ($arg instanceof DriverInterface) {
                $this->addDriver($arg);
            }
        }
    }

    public function __destruct()
    {
        $this->flush($this->deferred_entries);
    }

    /**
     * Add a driver for saving/sending the log entries. Multiple drivers allowed,
     * will be run one after the other in the order they are added.
     * @param  DriverInterface  $driver   The driver
     * @param  callable         $filter   A lambda to filter when the driver is used. Current error level as the only parameter.
     *                                    E.g. the lambda: function(x){return x==0;} will use the driver only for emergency level entries.
     */

    public function addDriver(DriverInterface $driver, callable $filter = null)
    {
        if ($filter) {
            $driver->setFilter($filter);
        }
        
        $this->drivers[] = $driver;
    }

    /**
     * Add an entry to the log.
     * @param  int     $level    Severity level as defined above and in RFC 5424.
     * @param  string  $message  
     * @param  array   $context  Any other data to include in the log. Will be encoded depending on driver, usually as JSON.
     */

    public function log($level, $message, array $context = [])
    {
        if ( ! isset(self::$levels[$level])) {
            throw new InvalidArgumentException(sprintf('Invalid severity level "%s"', $level));
        }

        // In production mode, only errors and higher severity are logged.
        if ( ! $this->options['dev'] && $level > self::ERROR) {
            return;
        }

        // Include caller trace information only for error or higher severity.
        $profile = [];
        if ($level <= self::ERROR) {
            if ($this->options['trace']) {
                $profile += debug_backtrace()[2];
            }
            if ($this->options['memory']) {
                $profile += [
                    'memory' => memory_get_usage(),
                    'memory_peak' => memory_get_peak_usage()
                ];
            }
        }

        $entry = new LogEntry($level, $message, $context, $profile);

        // Add to log entries list or flush immediately, depending on settings.
        if ($this->options['defer']) {
            $this->deferred_entries[] = $entry;
        }
        else {
            $this->flush([$entry]);
        }
    }

    /**
     * A simple wrapper for driver->write that can queue multiple entries.
     */
    
    private function flush(array $entries)
    {
        foreach ($entries as $entry) {
            $status = 1;
            foreach ($this->drivers as $driver) {
                $status = $status & $driver->write($entry);
            }

            if ( ! $status && ! $this->options['quiet']) {
                throw new ErrorException(sprintf('Unable to write log entry "%s". Likely no valid drivers exist.', $entry->message));
            }
        }
    }


    /**
     * PSR-3 shorthands
     */
    
    public function emergency($message, array $context = [])
    {
        $this->log(self::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = [])
    {
        $this->log(self::ALERT, $message, $context);
    }

    public function critical($message, array $context = [])
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    public function error($message, array $context = [])
    {
        $this->log(self::ERROR, $message, $context);
    }

    public function warning($message, array $context = [])
    {
        $this->log(self::WARNING, $message, $context);
    }

    public function notice($message, array $context = [])
    {
        $this->log(self::NOTICE, $message, $context);
    }

    public function info($message, array $context = [])
    {
        $this->log(self::INFO, $message, $context);
    }

    public function debug($message, array $context = [])
    {
        $this->log(self::DEBUG, $message, $context);
    }
}