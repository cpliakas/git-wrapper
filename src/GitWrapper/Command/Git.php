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
 * Class that models arbirarty Git commands.
 */
class Git extends GitCommandAbstract
{
    /**
     * Implements GitCommandAbstract::getCommand().
     */
    public function getCommand()
    {
        return '';
    }
}
