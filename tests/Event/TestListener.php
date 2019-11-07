<?php

declare(strict_types=1);

namespace GitWrapper\Tests\Event;

use GitWrapper\Event\AbstractGitEvent;

final class TestListener
{
    /**
     * @var string[]
     */
    private $methods = [];

    public function methodCalled(string $method): bool
    {
        return in_array($method, $this->methods, true);
    }

    public function onPrepare(): void
    {
        $this->methods[] = 'onPrepare';
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
