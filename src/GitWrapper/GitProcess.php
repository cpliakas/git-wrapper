<?php declare(strict_types=1);

namespace GitWrapper;

use GitWrapper\Event\GitEvent;
use GitWrapper\Event\GitEvents;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * GitProcess runs a Git command in an independent process.
 */
class GitProcess extends Process
{
    /**
     * @var \GitWrapper\GitWrapper
     */
    protected $git;

    /**
     * @var \GitWrapper\GitCommand
     */
    protected $command;

    /**
     * Constructs a GitProcess object.
     *
     */
    public function __construct(GitWrapper $git, GitCommand $command, ?string $cwd = null)
    {
        $this->git = $git;
        $this->command = $command;

        // Build the command line options, flags, and arguments.
        $gitCommand = $command->getCommandLine();
        $commandLine = array_merge([$git->getGitBinary()], (array) $gitCommand);

        // Support for executing an arbitrary git command.
        if (is_string($gitCommand)) {
            $commandLine = join(' ', $commandLine);
        }

        // Resolve the working directory of the Git process. Use the directory
        // in the command object if it exists.
        if ($cwd === null) {
            $directory = $command->getDirectory();
            if ($directory !== null) {
                if (! $cwd = realpath($directory)) {
                    throw new GitException('Path to working directory could not be resolved: ' . $directory);
                }
            }
        }

        // Finalize the environment variables, an empty array is converted
        // to null which enherits the environment of the PHP process.
        $env = $git->getEnvVars();
        if (! $env) {
            $env = null;
        }

        parent::__construct($commandLine, $cwd, $env, null, $git->getTimeout());
    }

    /**
     * {@inheritdoc}
     */
    public function run(?callable $callback = null, array $env = []): int
    {
        $exitCode = -1;

        $event = new GitEvent($this->git, $this, $this->command);
        $dispatcher = $this->git->getDispatcher();

        try {
            // Throw the "git.command.prepare" event prior to executing.
            $dispatcher->dispatch(GitEvents::GIT_PREPARE, $event);

            // Execute command if it is not flagged to be bypassed and throw the
            // "git.command.success" event, otherwise do not execute the comamnd
            // and throw the "git.command.bypass" event.
            if ($this->command->notBypassed()) {
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
        } catch (RuntimeException $e) {
            $dispatcher->dispatch(GitEvents::GIT_ERROR, $event);
            throw new GitException($e->getMessage());
        }

        return $exitCode;
    }
}
