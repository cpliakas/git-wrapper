<?php

declare(strict_types=1);

namespace GitWrapper\Test\Event;

use GitWrapper\Contract\Event\GitOutputListenerInterface;
use GitWrapper\Event\GitOutputEvent;

final class TestOutputListener implements GitOutputListenerInterface
{
    /**
     * @var GitOutputEvent
     */
    private $gitOutputEvent;

    public function getLastEvent(): GitOutputEvent
    {
        return $this->gitOutputEvent;
    }

    public function handleOutput(GitOutputEvent $gitOutputEvent): void
    {
        $this->gitOutputEvent = $gitOutputEvent;
    }
}
