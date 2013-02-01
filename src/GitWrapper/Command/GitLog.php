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
 * Class that models `git log` commands.
 *
 * Show commit logs.
 */
class GitLog extends GitCommandAbstract
{
    /**
     * Constructs a GitLog object.
     *
     * @param string $directory
     *   Path to the directory containing the working copy.
     * @param string|null $path
     *   Show only commits that are enough to explain how the files that match
     *   the specified paths came to be.
     * @param string|null $since_until
     *   Show only commits between the named two commits.
     */
    public function __construct($directory, $path = null, $since_until = null)
    {
        $this->_directory = $directory;

        if ($path !== null) {
            $this->addArgument($path);

            if ($since_until !== null) {
                $this->addArgument($since_until);
            }
        }
    }

    /**
     * Implements GitCommandAbstract::getCommand().
     *
     * This class wraps `git log` commands.
     */
    public function getCommand()
    {
        return 'log';
    }
}
