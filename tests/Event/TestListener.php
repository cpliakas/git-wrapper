<?php declare(strict_types=1);

namespace GitWrapper\Test\Event;

use GitWrapper\Event\GitEvent;

final class TestListener
{
    /**
     * The methods that were called.
     *
     * @var string[]
     */
    private $methods = [];

    /**
     * The event object passed to the onPrepare method.
     *
     * @var GitEvent
     */
    private $gitEvent;

    public function methodCalled(string $method): bool
    {
        return in_array($method, $this->methods, true);
    }

    public function getEvent(): GitEvent
    {
        return $this->gitEvent;
    }

    public function onPrepare(GitEvent $gitEvent): void
    {
        $this->methods[] = 'onPrepare';
        $this->gitEvent = $gitEvent;
    }

    public function onSuccess(): void
    {
        $this->methods[] = 'onSuccess';
    }

    public function onError(): void
    {
        $this->methods[] = 'onError';
    }

    public function onBypass(): void
    {
        $this->methods[] = 'onBypass';
    }
}
