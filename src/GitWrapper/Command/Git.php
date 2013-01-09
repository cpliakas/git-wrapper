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
class Git extends GitCommandAbstract
{
    /**
     * This class wraps the base git binary.
     *
     * {@inheritdoc}
     */
    public function getCommand()
    {
        return '';
    }
}
