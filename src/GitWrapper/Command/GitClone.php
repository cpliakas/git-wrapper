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
 * Clones a git repository.
 */
class GitClone extends GitCommandAbstract
{
    /**
     * Constructs a GitClone object.
     *
     * If a directory is not passed, the repository will be checked out to
     * a directory in the current directory named afer the repository.
     *
     * @param string $repo_url
     * @param string|null $target_dir
     */
    public function __construct($repository, $directory = null)
    {
        // Use the name of the repo as the directory if not passed.
        if (null === $directory) {
            $path = parse_url($repository, PHP_URL_PATH);
            if (false === $path) {
                throw new \RuntimeException('Repository URL not valid.');
            }
            $directory = basename($path, '.git');
        }

        $this
            ->addArgument($repository)
            ->addArgument($directory);
    }

    /**
     * This class wraps Git clone commands.
     *
     * {@inheritdoc}
     */
    public function getCommand() {
        return 'clone';
    }
}
