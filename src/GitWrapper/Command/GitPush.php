<?php

/**
 * A PHP wrapper around the Git command line utility.
 *
 * @license GNU General Public License, version 3
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @see https://github.com/cpliakas/git-wrapper
 * @copyright Copyright (c) 2013 Acquia, Inc.
 */

namespace GitWrapper\Command;

/**
 * Class that models `git push` commands.
 *
 * Updates remote refs along with associated objects.
 */
class GitPush extends GitCommandAbstract
{
    /**
     * Constructs a GitPush object.
     *
     * @param string $directory
     *   Path to the directory containing the working copy.
     * @param string|null $repository
     *   The "remote" repository that is destination of a push operation.
     * @param string|null $refspec
     *   Optionally pass a refspec to a remote repository.
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
