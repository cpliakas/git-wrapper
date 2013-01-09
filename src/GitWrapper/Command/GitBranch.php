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
 * List, create, or delete branches.
 */
class GitBranch extends GitCommandAbstract
{
    /**
     * This class wraps Git clone commands.
     *
     * {@inheritdoc}
     */
    public function getCommand() {
        return 'branch';
    }
}
