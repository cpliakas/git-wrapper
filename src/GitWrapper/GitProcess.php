<?php declare(strict_types=1);

namespace GitWrapper;

use GitWrapper\Event\GitEvent;
use GitWrapper\Event\GitEvents;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * GitProcess runs a Git command in an independent process.
 */
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
        $gitCommandLine = $gitCommand->getCommandLine();
        $commandLine = array_merge([$gitWrapper->getGitBinary()], (array) $gitCommandLine);

        // Support for executing an arbitrary git command.
        if (is_string($gitCommandLine)) {
            $commandLine = implode(' ', $commandLine);
        }

        // Resolve the working directory of the Git process. Use the directory
        // in the command object if it exists.
        if ($cwd === null) {
            $directory = $gitCommand->getDirectory();
            if ($directory !== null) {
                if (! $cwd = realpath($directory)) {
                    throw new GitException('Path to working directory could not be resolved: ' . $directory);
                }
            }
        }

        // Finalize the environment variables, an empty array is converted
        // to null which enherits the environment of the PHP process.
        $env = $gitWrapper->getEnvVars();
        if (! $env) {
            $env = null;
        }

        parent::__construct($commandLine, $cwd, $env, null, $gitWrapper->getTimeout());
    }

    /**
     * {@inheritdoc}
     */
    public function run(?callable $callback = null, array $env = []): int
    {
        $exitCode = -1;

        $event = new GitEvent($this->gitWrapper, $this, $this->gitCommand);
        $dispatcher = $this->gitWrapper->getDispatcher();

        try {
            // Throw the "git.command.prepare" event prior to executing.
            $dispatcher->dispatch(GitEvents::GIT_PREPARE, $event);

            // Execute command if it is not flagged to be bypassed and throw the
            // "git.command.success" event, otherwise do not execute the comamnd
            // and throw the "git.command.bypass" event.
            if ($this->gitCommand->notBypassed()) {
                $exitCode = parent::run($callback, $env);

                if ($this->isSuccessful()) {
                    $dispatcher->dispatch(GitEvents::GIT_SUCCESS, $event);
                } else {
                    $output = $this->getErrorOutput();

                    if (trim($output) === '') {
                        $output = $this->getOutput();
                    }

                    throw new RuntimeException($output);
                }
            } else {
                $dispatcher->dispatch(GitEvents::GIT_BYPASS, $event);
            }
        } catch (RuntimeException $runtimeException) {
            $dispatcher->dispatch(GitEvents::GIT_ERROR, $event);
            throw new GitException($runtimeException->getMessage());
        }

        return $exitCode;
    }
}
