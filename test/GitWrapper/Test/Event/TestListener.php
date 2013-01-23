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
    protected $_methods = array();

    /**
     * The event object passed to the onPrepare method.
     *
     * @var GitEvent
     */
    protected $_event;

    public function methodCalled($method)
    {
        return in_array($method, $this->_methods);
    }

    /**
     * @return GitEvent
     */
    public function getEvent()
    {
        return $this->_event;
    }

    public function onPrepare(GitEvent $event)
    {
        $this->_methods[] = 'onPrepare';
        $this->_event = $event;
    }

    public function onSuccess(GitEvent $event)
    {
        $this->_methods[] = 'onSuccess';
    }

    public function onError(GitEvent $event)
    {
        $this->_methods[] = 'onError';
    }

    public function onBypass(GitEvent $event)
    {
        $this->_methods[] = 'onBypass';
    }
}
