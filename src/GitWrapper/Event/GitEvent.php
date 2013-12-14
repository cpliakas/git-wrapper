<?php

namespace GitWrapper\Event;

use GitWrapper\GitCommand;
use GitWrapper\GitWrapper;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Process\Process;

/**
 * Event instance passed as a result of git.* commands.
 */
class GitEvent extends Event
{
    /**
     * The GitWrapper object that likely instantiated this class.
     *
     * @var GitWrapper
     */
    protected $wrapper;

    /**
     * The Process object being run.
     *
     * @var Process
     */
    protected $process;

    /**
     * The GitCommand object being executed.
     *
     * @var GitCommand
     */
    protected $command;

    /**
     * Constructs a GitEvent object.
     *
     * @param GitWrapper $wrapper
     *   The GitWrapper object that likely instantiated this class.
     * @param Process $process
     *   The Process object being run.
     * @param GitCommand $command
     *   The GitCommand object being executed.
     */
    public function __construct(GitWrapper $wrapper, Process $process, GitCommand $command)
    {
        $this->wrapper = $wrapper;
        $this->process = $process;
        $this->command = $command;
    }

    /**
     * Gets the GitWrapper object that likely instantiated this class.
     *
     * @return GitWrapper
     */
    public function getWrapper()
    {
        return $this->wrapper;
    }

    /**
     * Gets the Process object being run.
     *
     * @return Process
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * Gets the GitCommand object being executed.
     *
     * @return GitCommand
     */
    public function getCommand()
    {
        return $this->command;
    }
}
