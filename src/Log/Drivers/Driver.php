<?php namespace KrisLux\Log\Drivers;

use ErrorException;
use KrisLux\Log\Formatters\FormatterInterface;

abstract class Driver implements DriverInterface
{
    protected $formatter;

    protected $filter;

    protected $options;

    public function __construct(array $options = [], FormatterInterface $formatter = null)
    {
        if ( ! $formatter) {
            throw new ErrorException('No formatter given and this driver does not provide a default.');
        }
        $this->options = $options;
        $this->formatter = $formatter;
    }
    
    /**
     * A lambda to filter when the driver is used. Current error level as the only parameter.
     * E.g. the lambda: function(x){return x==0;} will use the driver only for emergency level entries.
     */

    public function setFilter(callable $lambda)
    {
        $this->filter = $lambda;
    }

    /**
     * Return if the provided severity level passes the filter. Must return true if no filter set.
     */

    public function testFilter($level)
    {
        if ( ! $this->filter)
            return true;
        $closure = $this->filter;
        return $closure($level);
    }

}