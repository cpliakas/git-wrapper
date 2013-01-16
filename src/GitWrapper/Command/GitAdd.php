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
 * Class that models `git add` commands.
 *
 * Adds file contents to the index.
 */
class GitAdd extends GitCommandAbstract
{
    /**
     * Constructs a GitAdd object.
     *
     * @param string $directory
     *   Path to the directory containing the working copy.
     * @param string $filepattern
     *   Files to add content from. Fileglobs (e.g.  *.c) can be given to add
     *   all matching files. Also a leading directory name (e.g.  dir to add
     *   dir/file1 and dir/file2) can be given to add all files in the
     *   directory, recursively.
     */
    public function __construct($directory, $filepattern)
    {
        $this->_directory = $directory;
        $this->addArgument($this->escapeFilepattern($filepattern));
    }

    /**
     * Implements GitCommandAbstract::getCommand().
     *
     * This class wraps `git add` commands.
     */
    public function getCommand()
    {
        return 'add';
    }
}
