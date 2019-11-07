<?php

declare(strict_types=1);

namespace GitWrapper\Tests\Event;

use GitWrapper\Event\GitPrepareEvent;

final class TestBypassListener
{
    public function onPrepare(GitPrepareEvent $gitPrepareEvent): void
    {
        $gitPrepareEvent->getCommand()->bypass();
    }
}
