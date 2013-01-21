<?php

namespace GitWrapper\Test\Event;

use Symfony\Component\EventDispatcher\Event;

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

    public function onCommand(Event $event)
    {
        $this->_methods[] = 'onCommand';
    }

    public function onSuccess(Event $event)
    {
        $this->_methods[] = 'onSuccess';
    }

    public function onError(Event $event)
    {
        $this->_methods[] = 'onError';
    }
}
