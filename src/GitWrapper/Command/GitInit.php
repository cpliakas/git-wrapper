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
 * Create an empty git repository or reinitialize an existing one.
 */
class GitInit extends GitCommandAbstract
{
    /**
     * Constructs a GitInit object.
     *
     * @param string|null $directory
     */
    public function __construct($directory = null)
    {
        if (null === $directory) {
            $this->addArgument($directory);
        }
    }

    /**
     * This class wraps Git clone commands.
     *
     * {@inheritdoc}
     */
    public function getCommand() {
        return 'branch';
    }
}
