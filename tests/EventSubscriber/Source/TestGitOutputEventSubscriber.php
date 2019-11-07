<?php

declare(strict_types=1);

namespace GitWrapper\Tests\EventSubscriber\Source;

use GitWrapper\Event\GitOutputEvent;
use GitWrapper\EventSubscriber\AbstractOutputEventSubscriber;

final class TestGitOutputEventSubscriber extends AbstractOutputEventSubscriber
{
    /**
     * @var GitOutputEvent
     */
    private $gitOutputEvent;

    public function handleOutput(GitOutputEvent $gitOutputEvent): void
    {
        $this->gitOutputEvent = $gitOutputEvent;
    }

    /**
     * For testing
     */
    public function getLastEvent(): GitOutputEvent
    {
        return $this->gitOutputEvent;
    }
}
