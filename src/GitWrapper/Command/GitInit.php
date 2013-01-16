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
 * Class that models `git init` commands.
 *
 * Creates an empty git repository or reinitialize an existing one.
 */
class GitInit extends GitCommandAbstract
{
    /**
     * Constructs a GitInit object.
     *
     * @param string|null $directory
     *   The path to the directory that Git is being initialized in, pass null
     *   to initialize the current working directory.
     */
    public function __construct($directory = null)
    {
        if (null === $directory) {
            $this->addArgument($directory);
        }
    }

    /**
     * Implements GitCommandAbstract::getCommand().
     *
     * This class wraps `git init` commands.
     */
    public function getCommand()
    {
        return 'init';
    }
}
