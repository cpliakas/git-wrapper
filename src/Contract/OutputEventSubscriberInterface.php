<?php

declare(strict_types=1);

namespace GitWrapper\Contract;

use GitWrapper\Event\GitOutputEvent;

interface OutputEventSubscriberInterface
{
    public function handleOutput(GitOutputEvent $gitOutputEvent): void;
}
