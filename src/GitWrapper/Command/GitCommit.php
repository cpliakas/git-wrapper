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
 * Class that models `git commit` commands.
 *
 * Records changes to the repository.
 */
class GitCommit extends GitCommandAbstract
{
    /**
     * Constructs a GitCommit object.
     *
     * @param string $directory
     *   Path to the directory containing the working copy.
     * @param string|null $log_message
     *   An optional log message passed as the "-m" option.
     * @param string|null $files
     *   The contents of these files will be committed without recording the
     *   changes already staged. Defaults to null which passes the "-a" flag.
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
