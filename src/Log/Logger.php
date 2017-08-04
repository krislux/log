<?php namespace KrisLux\Log;

use Exception;
use ErrorException;
use Psr\Log\LoggerInterface;
use Psr\Log\InvalidArgumentException;
use KrisLux\Log\Drivers\DriverInterface;
use KrisLux\Log\LogEntry;
use KrisLux\Log\Handler;
use KrisLux\Log\Exceptions\NoValidDriverException;

class Logger implements LoggerInterface
{
    private $options = [];

    private $drivers = [];

    protected $entries = [];

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

    public static function getLevels()
    {
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
            'trace' => true,      // Include trace information in warnings and above.
            'immediate' => false  // Write or send entries as they are added instead of buffering.
                                  // Generally more demanding, but can be useful if you expect the server to crash.
        ] + $options;
        
        foreach (func_get_args() as $arg) {
            if ($arg instanceof DriverInterface) {
                $this->addDriver($arg);
            }
        }
    }

    public function __destruct()
    {
        $this->flush($this->entries);
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
     * 
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

        // Include caller trace information only for warning or higher severity.
        $origin = $level <= self::WARNING ? debug_backtrace()[2] : [];

        $entry = new LogEntry($level, $message, $context, $origin);

        // Add to log entries list or flush immediately, depending on settings.
        if ($this->options['immediate']) {
            $this->flush([$entry]);
        }
        else {
            $this->entries[] = $entry;
        }
    }


    private function flush(array $entries)
    {
        foreach ($entries as $entry) {
            
            foreach ($this->drivers as $driver) {
                $driver->write($entry);
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
        $this->log(self::alert, $message, $context);
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