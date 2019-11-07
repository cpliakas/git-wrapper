<?php

declare(strict_types=1);

namespace GitWrapper\EventSubscriber;

use GitWrapper\Event\GitOutputEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractOutputEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            GitOutputEvent::class => 'handleOutput',
        ];
    }

    abstract public function handleOutput(GitOutputEvent $gitOutputEvent): void;
}
