<?php

/**
 * A PHP wrapper around the Git command line utility.
 *
 * @license GNU General Public License, version 3
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @see https://github.com/cpliakas/git-wrapper
 * @copyright Copyright (c) 2013 Acquia, Inc.
 */

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
     * @param GitWrapper $wrapper
     *   The GitWrapper object that likely instantiated this class.
     * @param Process $process
     *   The Process object being run.
     * @param GitCommand $command
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
