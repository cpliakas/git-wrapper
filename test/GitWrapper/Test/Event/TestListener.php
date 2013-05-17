<?php

namespace GitWrapper\Test\Event;

use GitWrapper\Event\GitEvent;
use GitWrapper\Event\GitProcessEvent;

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

    /**
     * The type of buffer from the processEvent passed to the onProcess method
     */
    protected $_onProcessBufferType;

    /**
     * The buffer string from the processEvent passed to the onProcess method
     */
    protected $_onProcessBuffer;


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

    /**
     * @return  string
     */
    public function getOnProcessBufferType()
    {
        return $this->_onProcessBufferType;
    }

    /**
     * @return  string
     */
    public function getOnProcessBuffer()
    {
        return $this->_onProcessBuffer;
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

    public function onProcess(GitProcessEvent $processEvent)
    {
        $this->_methods[] = 'onProcess';
        $this->_onProcessBufferType = $processEvent->getType();
        $this->_onProcessBuffer = $processEvent->getBuffer();
    }
}
