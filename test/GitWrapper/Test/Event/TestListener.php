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

    public function methodCalled($method)
    {
        return in_array($method, $this->_methods);
    }

    public function onCommand(GitEvent $event)
    {
        $this->_methods[] = 'onCommand';
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
