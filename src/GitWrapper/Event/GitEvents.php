<?php

/**
 * A PHP Git wrapper.
 *
 * @license GNU General Public License, version 3
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Copyright (c) 2013 Acquia, Inc.
 */

namespace GitWrapper\Event;

final class GitEvents
{
    /**
     * Event thrown prior to executing a git command.
     *
     * @var string
     */
    const GIT_COMMAND = 'git.command';
}
