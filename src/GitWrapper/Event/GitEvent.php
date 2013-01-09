<?php

/**
 * A PHP Git wrapper.
 *
 * @license GNU General Public License, version 3
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Copyright (c) 2013 Acquia, Inc.
 */

namespace GitWrapper\Event;

use GitWrapper\GitWrapper;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Process\Process;

/**
 * Event instance passed as a result of git.* commands.
 */
class GitEvent extends Event
{
    /**
     * @var GitWrapper
     */
    protected $_wrapper;

    /**
     * @var Process
     */
    protected $_process;

    /**
     * Constructs a GitEvent object.
     *
     * @param GitWrapper $wrapper
     * @param Process $process
     */
    public function __construct(GitWrapper $wrapper, Process $process)
    {
        $this->_wrapper = $wrapper;
        $this->_process = $process;
    }

    /**
     * @return GitWrapper
     */
    public function getWrapper()
    {
        return $this->_wrapper;
    }

    /**
     * @return Process
     */
    public function getProcess()
    {
        return $this->_process;
    }
}
