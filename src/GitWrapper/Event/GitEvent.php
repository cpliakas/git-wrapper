<?php declare(strict_types=1);

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
     * @var \GitWrapper\GitWrapper
     */
    protected $wrapper;

    /**
     * The Process object being run.
     *
     * @var \Symfony\Component\Process\Process
     */
    protected $process;

    /**
     * The GitCommand object being executed.
     *
     * @var \GitWrapper\GitCommand
     */
    protected $command;

    /**
     * Constructs a GitEvent object.
     *
     * @param \GitWrapper\GitWrapper $wrapper
     *   The GitWrapper object that likely instantiated this class.
     * @param \Symfony\Component\Process\Process $process
     *   The Process object being run.
     * @param \GitWrapper\GitCommand $command
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
     */
    public function getWrapper(): GitWrapper
    {
        return $this->wrapper;
    }

    /**
     * Gets the Process object being run.
     *
     */
    public function getProcess(): Process
    {
        return $this->process;
    }

    /**
     * Gets the GitCommand object being executed.
     *
     */
    public function getCommand(): GitCommand
    {
        return $this->command;
    }
}
