<?php

declare(strict_types=1);

namespace GitWrapper\EventSubscriber;

use GitWrapper\Contract\OutputEventSubscriberInterface;
use GitWrapper\Event\GitOutputEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractOutputEventSubscriber implements EventSubscriberInterface, OutputEventSubscriberInterface
{
    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            GitOutputEvent::class => 'handleOutput',
        ];
    }
}
