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
    protected $gitWrapper;

    /**
     * @var Process
     */
    protected $process;

    /**
     * @var GitCommand
     */
    protected $gitCommand;

    public function __construct(GitWrapper $gitWrapper, Process $process, GitCommand $gitCommand)
    {
        $this->gitWrapper = $gitWrapper;
        $this->process = $process;
        $this->gitCommand = $gitCommand;
    }

    public function getWrapper(): GitWrapper
    {
        return $this->gitWrapper;
    }

    public function getProcess(): Process
    {
        return $this->process;
    }

    public function getCommand(): GitCommand
    {
        return $this->gitCommand;
    }
}
