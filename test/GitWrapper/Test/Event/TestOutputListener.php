<?php declare(strict_types=1);

namespace GitWrapper\Test\Event;

use GitWrapper\Event\GitOutputEvent;
use GitWrapper\Event\GitOutputListenerInterface;

final class TestOutputListener implements GitOutputListenerInterface
{
    /**
     * @var \GitWrapper\Event\GitOutputEvent
     */
    protected $event;

    /**
     * @return GitWrapper\Event\GitOutputEvent
     */
    public function getLastEvent(): GitOutputEvent
    {
        return $this->event;
    }

    public function handleOutput(GitOutputEvent $event): void
    {
        $this->event = $event;
    }
}
