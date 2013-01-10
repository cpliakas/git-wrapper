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

    /**
     * Event thrown prior to executing a git branch command.
     *
     * @var string
     */
    const GIT_BRANCH = 'git.branch';

    /**
     * Event thrown prior to executing a git clone command.
     *
     * @var string
     */
    const GIT_CLONE = 'git.clone';

    /**
     * Event thrown prior to executing a git commit command.
     *
     * @var string
     */
    const GIT_COMMIT = 'git.commit';

    /**
     * Event thrown prior to executing a git init command.
     *
     * @var string
     */
    const GIT_INIT = 'git.init';

    /**
     * Event thrown prior to executing a git push command.
     *
     * @var string
     */
    const GIT_PUSH = 'git.push';
}
