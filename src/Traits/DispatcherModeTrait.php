<?php declare(strict_types=1);

namespace GitWrapper\Traits;

use GitWrapper\Event\GitEvent;
use ReflectionClass;
use Symfony\Component\EventDispatcher\EventDispatcher;

trait DispatcherModeTrait
{
    /**
     * Determine the order of arguments for dispatch
     *
     * @param GitEvent $gitEvent
     * @param string $eventName
     * @return mixed[]
     */
    protected function arrangeDispatchArguments(GitEvent $gitEvent, string $eventName): array
    {
        $reflection = new ReflectionClass(EventDispatcher::class);
        $parameters = $reflection->getMethod('dispatch')->getParameters();
        // Symfony versions less than 4.3
        if (is_array($parameters) && count($parameters) === 2) {
            return [$eventName, $gitEvent];
        }
        // Symfony 4.3 and greater
        return [$gitEvent, $eventName];
    }
}
