<?php namespace KrisLux\Log;

use ErrorException;
use Psr\Log\LoggerInterface;
use KrisLux\Log\Drivers\DriverInterface;
use KrisLux\Log\Logger;

class LoggerStatic
{
    private static $logger;

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

    public static function init(array $options, DriverInterface $driver = null)
    {
        self::$logger = new Logger($options, $driver);
    }

    public static function addDriver(DriverInterface $driver, callable $filter = null)
    {
        self::$logger->addDriver($driver, $filter);
    }

    public static function log($level, $message, array $context = [])
    {
        if ( ! self::$logger) {
            throw new ErrorException('Static logger must be initialized with '.__CLASS__.'::init() first.');
        }

        self::$logger->log($level, $message, $context);
    }

    /**
     * PSR-3 shorthands
     */
    
    public static function emergency($message, array $context = [])
    {
        self::log(self::EMERGENCY, $message, $context);
    }

    public static function alert($message, array $context = [])
    {
        self::log(self::ALERT, $message, $context);
    }

    public static function critical($message, array $context = [])
    {
        self::log(self::CRITICAL, $message, $context);
    }

    public static function error($message, array $context = [])
    {
        self::log(self::ERROR, $message, $context);
    }

    public static function warning($message, array $context = [])
    {
        self::log(self::WARNING, $message, $context);
    }

    public static function notice($message, array $context = [])
    {
        self::log(self::NOTICE, $message, $context);
    }

    public static function info($message, array $context = [])
    {
        self::log(self::INFO, $message, $context);
    }

    public static function debug($message, array $context = [])
    {
        self::log(self::DEBUG, $message, $context);
    }
}