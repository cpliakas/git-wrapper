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
        $path_info = pathinfo($filepattern);
        // Only escape paths with files that have extensions.
        // If the path does not have an extension, no extension
        // element will be returned
        if (!isset($path_info['extension'])) {
          $path_info['basename'] = str_replace('.', '\\.', $path_info['basename']);
        }
        $this->addArgument($path_info['dirname'] . '/' . $path_info['basename']);
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
