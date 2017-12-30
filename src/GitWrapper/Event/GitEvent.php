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
     * @var GitWrapper
     */
    protected $wrapper;

    /**
     * @var Process
     */
    protected $process;

    /**
     * @var GitCommand
     */
    protected $command;

    public function __construct(GitWrapper $wrapper, Process $process, GitCommand $command)
    {
        $this->wrapper = $wrapper;
        $this->process = $process;
        $this->command = $command;
    }

    public function getWrapper(): GitWrapper
    {
        return $this->wrapper;
    }

    public function getProcess(): Process
    {
        return $this->process;
    }

    public function getCommand(): GitCommand
    {
        return $this->command;
    }
}
