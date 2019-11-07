<?php

declare(strict_types=1);

namespace GitWrapper\Tests\Event;

use GitWrapper\Event\GitPrepareEvent;
use Iterator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class TestBypassEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): Iterator
    {
        yield GitPrepareEvent::class => [
            function (GitPrepareEvent $gitPrepareEvent): void {
                $this->onPrepare($gitPrepareEvent);
            }, -5,
        ];
    }

    public function onPrepare(GitPrepareEvent $gitPrepareEvent): void
    {
        $gitPrepareEvent->getCommand()->bypass();
    }
}
