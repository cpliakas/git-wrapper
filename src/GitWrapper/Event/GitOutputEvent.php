<?php

namespace GitWrapper\Event;

use GitWrapper\GitCommand;
use GitWrapper\GitWrapper;
use Symfony\Component\Process\Process;

/**
 * Event instance passed when output is returned from Git commands.
 */
class GitOutputEvent extends GitEvent
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $buffer;

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
    public function __construct(GitWrapper $wrapper, Process $process, GitCommand $command, $type, $buffer)
    {
        parent::__construct($wrapper, $process, $command);
        $this->type = $type;
        $this->buffer = $buffer;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getBuffer()
    {
        return $this->buffer;
    }

    /**
     * Tests wheter the buffer was captured from STDERR.
     */
    public function isError()
    {
        return (Process::ERR == $this->type);
    }
}
