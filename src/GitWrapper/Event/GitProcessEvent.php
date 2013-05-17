<?php

/**
 * A PHP wrapper around the Git command line utility.
 *
 * @license GNU General Public License, version 3
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @see https://github.com/cpliakas/git-wrapper
 * @copyright Copyright (c) 2013 Acquia, Inc.
 * @author  Ryan Hamilton-Schumacher <ryan@38pages.com>
 */

namespace GitWrapper\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Process event instance passed as a result of git.process commands.
 */
class GitProcessEvent extends Event
{
    /**
     * The GitEvent object that the git.process event will use.
     *
     * @var GitEvent
     */
    protected $_event;

    /**
     * The type of output (out or err)
     *
     * @var string
     */
    protected $_type;

    /**
     * Some bytes from the output in real-time
     *
     * @var string
     */
    protected $_buffer;

    /**
     * Constructs a GitProcessEvent object.
     *
     * @param GitEvent $event
     *   The GitEvent object that the git.process event will use.
     * @param string $type
     *   The type of output (out or err)
     * @param string $buffer
     *   Some bytes from the output in real-time
     */
    public function __construct(GitEvent $event, $type, $buffer)
    {
        $this->_event = $event;
        $this->_type = $type;
        $this->_buffer = $buffer;
    }

    /**
     * Gets the GitWrapper object that likely instantiated this class.
     *
     * @return GitWrapper
     */
    public function getWrapper()
    {
        return $this->_event->_wrapper;
    }

    /**
     * Gets the Process object being run.
     *
     * @return Process
     */
    public function getProcess()
    {
        return $this->_event->_process;
    }

    /**
     * Gets the GitCommand object being executed.
     *
     * @return GitCommand
     */
    public function getCommand()
    {
        return $this->_event->_command;
    }

    /**
     * Gets the output type object being executed.
     *
     * @return string (out or err)
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Gets the output type object being executed.
     *
     * @return string (out or err)
     */
    public function getBuffer()
    {
        return $this->_buffer;
    }
}
