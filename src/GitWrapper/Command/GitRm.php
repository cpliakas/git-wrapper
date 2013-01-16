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
 * Class that models `git rm` commands.
 *
 * Removes files from the working tree and from the index.
 */
class GitRm extends GitCommandAbstract
{
    /**
     * Constructs a GitAdd object.
     *
     * @param string $directory
     *   Path to the directory containing the working copy.
     * @param string $filepattern
     *   Files to remove from version control. Fileglobs (e.g.  *.c) can be
     *   given to add all matching files.
     */
    public function __construct($directory, $filepattern)
    {
        $this->_directory = $directory;
        $this->addArgument($this->escapeFilepattern($filepattern));
    }

    /**
     * Implements GitCommandAbstract::getCommand().
     *
     * This class wraps `git rm` commands.
     */
    public function getCommand()
    {
        return 'rm';
    }
}
