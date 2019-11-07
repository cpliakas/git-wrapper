<?php

declare(strict_types=1);

namespace GitWrapper\Tests\Event;

use GitWrapper\Event\AbstractGitEvent;

final class TestBypassListener
{
    public function onPrepare(AbstractGitEvent $gitEvent): void
    {
        $gitEvent->getCommand()->bypass();
    }
}
