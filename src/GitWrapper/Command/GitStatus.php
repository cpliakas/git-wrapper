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
 * Show the working tree status.
 */
class GitStatus extends GitCommandAbstract
{
    /**
     * Constructs a GitStatus object.
     *
     * @param string $directory The path to the working copy.
     * @param string|null pathspec
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
