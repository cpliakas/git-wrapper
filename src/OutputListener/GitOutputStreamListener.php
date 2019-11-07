<?php

declare(strict_types=1);

namespace GitWrapper\OutputListener;

use GitWrapper\Contract\Event\GitOutputListenerInterface;
use GitWrapper\Event\GitOutputEvent;

/**
 * Event handler that streams real-time output from Git commands to STDOUT and
 * STDERR.
 */
final class GitOutputStreamListener implements GitOutputListenerInterface
{
    public function handleOutput(GitOutputEvent $gitOutputEvent): void
    {
        $handler = $gitOutputEvent->isError() ? STDERR : STDOUT;
        fputs($handler, $gitOutputEvent->getBuffer());
    }
}
