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
 * Clone a repository into a new directory.
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
     * @throws \RuntimeException
     */
    public function __construct($repository, $directory = null)
    {
        // Use the name of the repo as the directory if not passed.
        if (null === $directory) {
            $scheme = parse_url($repository, PHP_URL_SCHEME);

            if (null === $repository) {
                $path = parse_url($url, PHP_URL_PATH);
                if (false === $path) {
                    throw new \RuntimeException('Repository URL not valid.');
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
