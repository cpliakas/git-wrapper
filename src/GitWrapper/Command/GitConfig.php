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
 * Get and set repository or global options.
 */
class GitConfig extends GitCommandAbstract
{
    /**
     * Constructs a GitConfig object.
     *
     * @param string|null $option
     * @param string|null $value
     */
    public function __construct($option = null, $value = null)
    {
        if ($option !== null && $value !== null) {
            $this->addArgument($option);
            $this->addArgument($value);
        }
    }

    /**
     * Implements GitCommandAbstract::getCommand().
     *
     * This class wraps `git config` commands.
     */
    public function getCommand()
    {
        return 'config';
    }
}
