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

use GitWrapper\Exception\GitException;

/**
 * Class that models `git clone` commands.
 *
 * Clones a repository into a new directory.
 */
class GitClone extends GitCommandAbstract
{
    /**
     * Constructs a GitClone object.
     *
     * If a directory is not passed, the repository will be checked out to
     * a directory in the current directory named afer the repository.
     *
     * @param string $repository
     *   The URL of the Git repository.
     * @param string|null $directory
     *   Path to the directory the repository will be cloned into.
     *
     * @throws GitException
     */
    public function __construct($repository, $directory = null)
    {
        // Use the name of the repo as the directory if not passed.
        if (null === $directory) {
            $scheme = parse_url($repository, PHP_URL_SCHEME);

            if (null === $scheme) {
                $parts = explode('/', $repository);
                $path = basename(end($parts), '.git');
                if (false === $path) {
                    throw new GitException('Repository URL not valid.');
                }
            } else {
                $strpos = strpos($repository, ':');
                $path = substr($repository, $strpos + 1);
            }

            $directory = basename($path, '.git');
        }

        $this
            ->addArgument($repository)
            ->addArgument($directory);
    }

    /**
     * Implements GitCommandAbstract::getCommand().
     *
     * This class wraps `git clone` commands.
     */
    public function getCommand()
    {
        return 'clone';
    }
}
