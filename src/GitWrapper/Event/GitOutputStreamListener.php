<?php

/**
 * A PHP wrapper around the Git command line utility.
 *
 * @license GNU General Public License, version 3
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @see https://github.com/cpliakas/git-wrapper
 * @copyright Copyright (c) 2013 Acquia, Inc.
 */

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
