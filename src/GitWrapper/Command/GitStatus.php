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
 * Class that models `git status` commands.
 *
 * Shows the working tree status.
 */
class GitStatus extends GitCommandAbstract
{
    /**
     * Constructs a GitStatus object.
     *
     * @param string $directory
     *   Path to the directory containing the working copy.
     * @param string|null pathspec
     *   Optionally pass a pathspec.
     */
    public function __construct($directory, $pathspec = null)
    {
        $this->_directory = $directory;
        if ($pathspec !== null) {
            $this->addArgument($pathspec);
        }
    }

    /**
     * Implements GitCommandAbstract::getCommand().
     *
     * This class wraps `git status` commands.
     */
    public function getCommand()
    {
        return 'status';
    }
}
