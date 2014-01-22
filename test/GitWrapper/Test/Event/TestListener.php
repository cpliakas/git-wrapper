<?php

namespace GitWrapper\Test\Event;

use GitWrapper\Event\GitEvent;

class TestListener
{
    /**
     * The methods that were called.
     *
     * @var array
     */
    protected $methods = array();

    /**
     * The event object passed to the onPrepare method.
     *
     * @var \GitWrapper\Event\GitEvent
     */
    protected $event;

    public function methodCalled($method)
    {
        return in_array($method, $this->methods);
    }

    /**
     * @return \GitWrapper\Event\GitEvent
     */
    public function getEvent()
    {
        return $this->event;
    }

    public function onPrepare(GitEvent $event)
    {
        $this->methods[] = 'onPrepare';
        $this->event = $event;
    }

    public function onSuccess(GitEvent $event)
    {
        $this->methods[] = 'onSuccess';
    }

    public function onError(GitEvent $event)
    {
        $this->methods[] = 'onError';
    }

    public function onBypass(GitEvent $event)
    {
        $this->methods[] = 'onBypass';
    }
}
