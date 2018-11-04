<?php declare(strict_types=1);

namespace GitWrapper;

use GitWrapper\Event\GitEvent;
use GitWrapper\Event\GitEvents;
use RuntimeException;
use Symfony\Component\Process\Process;

final class GitProcess extends Process
{
    /**
     * @var GitWrapper
     */
    protected $gitWrapper;

    /**
     * @var GitCommand
     */
    protected $gitCommand;

    public function __construct(GitWrapper $gitWrapper, GitCommand $gitCommand, ?string $cwd = null)
    {
        $this->gitWrapper = $gitWrapper;
        $this->gitCommand = $gitCommand;

        // Build the command line options, flags, and arguments.
        $commandLine = $gitCommand->getCommandLine();
        $gitBinary = $gitWrapper->getGitBinary();
        if (is_string($commandLine)) {
            // Support for executing an arbitrary git command.
            $commandLine = '"' . $gitBinary . '" ' . $commandLine;
        } else {
            array_unshift($commandLine, $gitBinary);
        }

        // Resolve the working directory of the Git process. Use the directory
        // in the command object if it exists.
        $cwd = $this->resolveWorkingDirectory($cwd, $gitCommand);

        // Finalize the environment variables, an empty array is converted
        // to null which enherits the environment of the PHP process.
        $env = $gitWrapper->getEnvVars();
        if (! $env) {
            $env = null;
        }

        parent::__construct($commandLine, $cwd, $env, null, (float) $gitWrapper->getTimeout());
    }

    /**
     * {@inheritdoc}
     */
    public function start(?callable $callback = null, array $env = []): void
    {
        $this->dispatchGitEvent(GitEvents::GIT_PREPARE);

        if ($this->gitCommand->notBypassed()) {
            parent::start($callback, $env);
        } else {
            $this->dispatchGitEvent(GitEvents::GIT_BYPASS);
        }
    }

    public function wait(?callable $callback = null): int
    {
        if (! $this->gitCommand->notBypassed()) {
            return -1;
        }

        try {
            $exitCode = parent::wait($callback);

            if ($this->isSuccessful()) {
                $this->dispatchGitEvent(GitEvents::GIT_SUCCESS);
            } else {
                $output = $this->getErrorOutput();

                if (trim($output) === '') {
                    $output = $this->getOutput();
                }

                throw new GitException($output);
            }
        } catch (RuntimeException $runtimeException) {
            $this->dispatchGitEvent(GitEvents::GIT_ERROR);
            throw new GitException($runtimeException->getMessage(), $runtimeException->getCode(), $runtimeException);
        }

        return $exitCode;
    }

    private function resolveWorkingDirectory(?string $cwd, GitCommand $gitCommand): ?string
    {
        if ($cwd !== null) {
            return $cwd;
        }

        $directory = $gitCommand->getDirectory();
        if ($directory === null) {
            return $cwd;
        }

        if (! $cwd = realpath($directory)) {
            throw new GitException('Path to working directory could not be resolved: ' . $directory);
        }

        return $cwd;
    }

    private function dispatchGitEvent(string $eventName): void
    {
        $this->gitWrapper->getDispatcher()->dispatch(
            $eventName,
            new GitEvent($this->gitWrapper, $this, $this->gitCommand)
        );
    }
}
