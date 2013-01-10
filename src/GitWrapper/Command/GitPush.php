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
 * Update remote refs along with associated objects.
 */
class GitPush extends GitCommandAbstract
{
    /**
     * Constructs a GitPush object.
     *
     * @param string $working_copy The path to the working copy.
     * @param string $repository
     *   The "remote" repository that is destination of a push operation.
     * @param string $refspec
     */
    public function __construct($working_copy, $repository = null, $refspec = null)
    {
        $this->_workingCopy = $working_copy;

        if ($repository !== null) {
            $this->addArgument($repository);
        }

        if ($refspec !== null) {
            $this->addArgument($refspec);
        }
    }

    /**
     * This class wraps Git push commands.
     *
     * {@inheritdoc}
     */
    public function getCommand() {
        return 'push';
    }
}
