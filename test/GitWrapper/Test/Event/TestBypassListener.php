<?php

namespace GitWrapper\Test\Event;

use GitWrapper\Event\GitEvent;

class TestBypassListener
{
    public function onPrepare(GitEvent $event)
    {
        $event->getCommand()->bypass();
    }
}
