<?php

namespace GitWrapper\Test;

use Psr\Log\AbstractLogger;

/**
 * Intercepts data sent to STDOUT and STDERR and uses the echo construct to
 * output the data so we can capture it using normal output buffering.
 */
class TestLogger extends AbstractLogger
{
    public $messages = array();
    public $levels = array();
    public $contexts = array();

    public function log($level, $message, array $context = array())
    {
        $this->messages[] = $message;
        $this->levels[] = $level;
        $this->contexts[] = $context;
    }

    public function clearMessages()
    {
        $this->messages = $this->levels = $this->contexts = array();
    }
}
