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
 * Class that models `git config` commands.
 *
 * Gets and sets repository or global options.
 */
class GitConfig extends GitCommandAbstract
{
    /**
     * Constructs a GitConfig object.
     *
     * If either $option or $value are null, no additional arguments are passed
     * to the `git config` command.
     *
     * @param string|null $option
     *   The configuration options being set.
     * @param string|null $value
     *   The value of the configuration option being set.
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
