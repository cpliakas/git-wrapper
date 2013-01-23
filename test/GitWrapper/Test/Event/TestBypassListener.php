<?php

namespace GitWrapper\Test\Event;

use GitWrapper\Event\GitEvent;

class TestBypassListener
{
    public function onCommand(GitEvent $event)
    {
        $event->getCommand()->bypass();
    }
}
