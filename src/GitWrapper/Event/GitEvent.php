<?php

/**
 * A PHP Git wrapper.
 *
 * @license GNU General Public License, version 3
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Copyright (c) 2013 Acquia, Inc.
 */

namespace GitWrapper\Event;

use GitWrapper\Command\GitCommandAbstract;
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
     * @var GitCommandAbstract
     */
    protected $_command;

    /**
     * Constructs a GitEvent object.
     *
     * @param GitWrapper $wrapper
     * @param Process $process
     * @param GitCommandAbstract $command
     */
    public function __construct(GitWrapper $wrapper, Process $process, GitCommandAbstract $command)
    {
        $this->_wrapper = $wrapper;
        $this->_process = $process;
        $this->_command = $command;
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

    /**
     * @return GitCommandAbstract
     */
    public function getCommand()
    {
        return $this->_command;
    }
}
