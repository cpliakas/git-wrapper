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
 * Record changes to the repository.
 */
class GitCommit extends GitCommandAbstract
{
    /**
     * Constructs a GitCommit object.
     *
     * @param string $directory The path to the working copy.
     * @param string|null $log_message
     * @param string|null $files
     *   The files to stage, defaults to null which omits and sets the "a" flag.
     */
    public function __construct($directory, $log_message = null, $files = null)
    {
        $this->_directory = $directory;

        if ($log_message !== null) {
            $this->setOption('m', $log_message);
        }

        if ($files === null) {
            $this->setFlag('a');
        } else {
            $this->addArgument($files);
        }
    }

    /**
     * Implements GitCommandAbstract::getCommand().
     *
     * This class wraps `git commit` commands.
     */
    public function getCommand()
    {
        return 'commit';
    }
}
