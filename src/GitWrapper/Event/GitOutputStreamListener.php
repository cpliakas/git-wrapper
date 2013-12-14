<?php

namespace GitWrapper\Event;

/**
 * Event handler that streams real-time output from Git commands to STDOUT and
 * STDERR.
 */
class GitOutputStreamListener implements GitOutputListenerInterface
{
    public function handleOutput(GitOutputEvent $event)
    {
        $handler = $event->isError() ? STDERR : STDOUT;
        fputs($handler, $event->getBuffer());
    }
}
