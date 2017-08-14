<?php namespace KrisLux\Log\Drivers;

/**
 * Driver for PHP's inbuilt mail sender. This must be set up correctly in php.ini
 * or it will fail. As it does not throw any errors, it is recommended to test
 * the mail() function of your setup before relying on this.
 * 
 * This will NOT aggregate messages before sending. One mail per log entry.
 * The main intended use for this is in combination with another driver, filtering
 * this to only be used for alert/emergency severity.
 */

use KrisLux\Log\Formatters\FormatterInterface;
use KrisLux\Log\Formatters;
use KrisLux\Log\LogEntry;

class MailDriver extends Driver
{
    public function __construct(array $options = [], FormatterInterface $formatter = null)
    {
        if ( ! $formatter) {
            $formatter = new Formatters\JsonFormatter;
        }

        $this->options = $options + [
            'prefix' => '[LOG] ',  // Prefix this to email titles to make them more recognisable.
            'recipients' => [],    // Array of recipient e-mail addresses.
            'headers' => []        // Any custom headers for the mail() function.
        ];

        if (empty($this->options['recipients'])) {
            throw new \ErrorException('At least one e-mail recipient is required.');
        }
        $this->formatter = $formatter;
    }

    public function write(LogEntry $entry)
    {
        $title = $this->options['prefix'] . $entry->message;
        $string = (string)$this->formatter->format($entry) . PHP_EOL;
        $headers = implode(PHP_EOL, (array)$this->options['headers']);

        $status = 1;
        foreach ((array)$this->options['recipients'] as $recipient) {
            $status = $status & mail($recipient, $title, $string, $headers);
        }

        return (bool)$status;
    }
}