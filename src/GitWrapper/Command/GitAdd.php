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
 * Add file contents to the index.
 */
class GitAdd extends GitCommandAbstract
{
    /**
     * Constructs a GitAdd object.
     *
     * @param string $directory The path to the working copy.
     * @param string $filepattern
     */
    public function __construct($directory, $filepattern)
    {
        $this->_directory = $directory;
        $this->addArgument($this->escapeFilepattern($filepattern));
    }

    /**
     * This class wraps Git add commands.
     *
     * {@inheritdoc}
     */
    public function getCommand()
    {
        return 'add';
    }
}
