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
 * Class that models `git tag` commands.
 *
 * Creates, lists, deletes or verifies a tag.
 */
class GitTag extends GitCommandAbstract
{
    /**
     * Constructs a GitTag object.
     *
     * @param string $directory
     *   Path to the directory containing the working copy.
     * @param string|null $tagname
     *   The name of the tag.
     * @param string|null $commit
     *   The commit hash.
     */
    public function __construct($directory, $tagname = null, $commit = null)
    {
        $this->_directory = $directory;

        if ($tagname !== null) {
            $this->addArgument($tagname);
            // Only add the second argument if the first one was passed.
            if ($commit !== null) {
                $this->addArgument($commit);
            }
        }
    }

    /**
     * Implements GitCommandAbstract::getCommand().
     *
     * This class wraps `git tag` commands.
     */
    public function getCommand()
    {
        return 'tag';
    }
}
