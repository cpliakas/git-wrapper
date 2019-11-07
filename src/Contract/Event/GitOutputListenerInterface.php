<?php

declare(strict_types=1);

namespace GitWrapper\Contract\Event;

use GitWrapper\Event\GitOutputEvent;

interface GitOutputListenerInterface
{
    public function handleOutput(GitOutputEvent $gitOutputEvent): void;
}
