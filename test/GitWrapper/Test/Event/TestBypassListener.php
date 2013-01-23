<?php

namespace GitWrapper\Test\Event;

use Symfony\Component\EventDispatcher\Event;

class TestBypassListener
{
    public function onCommand(Event $event)
    {
        $event->getCommand()->bypass();
    }
}
