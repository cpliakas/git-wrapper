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
 * Arbitrary git commands.
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
