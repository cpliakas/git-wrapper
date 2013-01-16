<?php

/**
 * A PHP Git wrapper.
 *
 * @license GNU General Public License, version 3
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Copyright (c) 2013 Acquia, Inc.
 */

namespace GitWrapper\Command;

/**
 * Update remote refs along with associated objects.
 */
class GitPush extends GitCommandAbstract
{
    /**
     * Constructs a GitPush object.
     *
     * @param string $directory The path to the working copy.
     * @param string|null $repository
     *   The "remote" repository that is destination of a push operation.
     * @param string|null $refspec
     */
    public function __construct($directory, $repository = null, $refspec = null)
    {
        $this->_directory = $directory;

        if ($repository !== null) {
            $this->addArgument($repository);
        }

        if ($refspec !== null) {
            $this->addArgument($refspec);
        }
    }

    /**
     * Implements GitCommandAbstract::getCommand().
     *
     * This class wraps `git push` commands.
     */
    public function getCommand()
    {
        return 'push';
    }
}
