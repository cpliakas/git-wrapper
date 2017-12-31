<?php declare(strict_types=1);

namespace GitWrapper\Test\Event;

use GitWrapper\Event\GitOutputEvent;
use GitWrapper\Event\GitOutputListenerInterface;

final class TestOutputListener implements GitOutputListenerInterface
{
    /**
     * @var \GitWrapper\Event\GitOutputEvent
     */
    protected $gitOutputEvent;

    /**
     * @return GitWrapper\Event\GitOutputEvent
     */
    public function getLastEvent(): GitOutputEvent
    {
        return $this->gitOutputEvent;
    }

    public function handleOutput(GitOutputEvent $gitOutputEvent): void
    {
        $this->gitOutputEvent = $gitOutputEvent;
    }
}
