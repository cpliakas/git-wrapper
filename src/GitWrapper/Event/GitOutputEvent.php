<?php declare(strict_types=1);

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

    public function __construct(
        GitWrapper $wrapper,
        Process $process,
        GitCommand $command,
        string $type,
        string $buffer
    ) {
        parent::__construct($wrapper, $process, $command);
        $this->type = $type;
        $this->buffer = $buffer;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getBuffer(): string
    {
        return $this->buffer;
    }

    public function isError(): bool
    {
        return $this->type === Process::ERR;
    }
}
