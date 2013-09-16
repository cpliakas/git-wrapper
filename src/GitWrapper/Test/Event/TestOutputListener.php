<?php

namespace GitWrapper\Test\Event;

use GitWrapper\Event\GitOutputEvent;
use GitWrapper\Event\GitOutputListenerInterface;

class TestOutputListener implements GitOutputListenerInterface
{
    /**
     * @var GitOutputEvent
     */
    protected $_event;

    /**
     * @return GitOutputEvent
     */
    public function getLastEvent()
    {
        return $this->_event;
    }

    public function handleOutput(GitOutputEvent $event)
    {
        $this->_event = $event;
    }
}
