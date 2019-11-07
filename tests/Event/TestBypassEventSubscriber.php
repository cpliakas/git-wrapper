<?php

declare(strict_types=1);

namespace GitWrapper\Tests\Event;

use GitWrapper\Event\GitPrepareEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class TestBypassEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            GitPrepareEvent::class => ['onPrepare', -5],
        ];
    }

    public function onPrepare(GitPrepareEvent $gitPrepareEvent): void
    {
        $gitPrepareEvent->getCommand()->bypass();
    }
}
