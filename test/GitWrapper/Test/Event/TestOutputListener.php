<?php

namespace GitWrapper\Test\Event;

use GitWrapper\Event\GitOutputEvent;
use GitWrapper\Event\GitOutputListenerInterface;

class TestOutputListener implements GitOutputListenerInterface
{
    /**
     * @var \GitWrapper\Event\GitOutputEvent
     */
    protected $event;

    /**
     * @return GitWrapper\Event\GitOutputEvent
     */
    public function getLastEvent()
    {
        return $this->event;
    }

    public function handleOutput(GitOutputEvent $event)
    {
        $this->event = $event;
    }
}
