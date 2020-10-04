<?php

declare(strict_types=1);

namespace GitWrapper\Event;

use GitWrapper\GitCommand;
use GitWrapper\GitWrapper;
use Symfony\Component\Process\Process;

/**
 * Event instance passed when output is returned from Git commands.
 */
final class GitOutputEvent extends AbstractGitEvent
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $buffer;

    public function __construct(
        GitWrapper $gitWrapper,
        Process $process,
        GitCommand $gitCommand,
        string $type,
        string $buffer
    ) {
        parent::__construct($gitWrapper, $process, $gitCommand);

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
