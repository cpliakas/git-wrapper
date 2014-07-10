<?php

namespace GitWrapper;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessUtils;

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
     * @param \GitWrapper\GitWrapper $git
     * @param \GitWrapper\GitCommand $command
     * @param string|null $cwd
     */
    public function __construct(GitWrapper $git, GitCommand $command, $cwd = null)
    {
        $this->git = $git;
        $this->command = $command;

        // Build the command line options, flags, and arguments.
        $binary = ProcessUtils::escapeArgument($git->getGitBinary());
        $commandLine = rtrim($binary . ' ' . $command->getCommandLine());

        // Resolve the working directory of the Git process. Use the directory
        // in the command object if it exists.
        if (null === $cwd) {
            if (null !== $directory = $command->getDirectory()) {
                if (!$cwd = realpath($directory)) {
                    throw new GitException('Path to working directory could not be resolved: ' . $directory);
                }
            }
        }

        // Finalize the environment variables, an empty array is converted
        // to null which enherits the environment of the PHP process.
        $env = $git->getEnvVars();
        if (!$env) {
            $env = null;
        }

        parent::__construct($commandLine, $cwd, $env, null, $git->getTimeout(), $git->getProcOptions());
    }

    /**
     * {@inheritdoc}
     */
    public function run($callback = null)
    {
        $event = new Event\GitEvent($this->git, $this, $this->command);
        $dispatcher = $this->git->getDispatcher();

        try {

            // Throw the "git.command.prepare" event prior to executing.
            $dispatcher->dispatch(Event\GitEvents::GIT_PREPARE, $event);

            // Execute command if it is not flagged to be bypassed and throw the
            // "git.command.success" event, otherwise do not execute the comamnd
            // and throw the "git.command.bypass" event.
            if ($this->command->notBypassed()) {
                parent::run($callback);

                if ($this->isSuccessful()) {
                    $dispatcher->dispatch(Event\GitEvents::GIT_SUCCESS, $event);
                } else {
                    $output = $this->getErrorOutput();

                    if(trim($output) == '') {
                        $output = $this->getOutput();
                    }

                    throw new \RuntimeException($output);
                }
            } else {
                $dispatcher->dispatch(Event\GitEvents::GIT_BYPASS, $event);
            }

        } catch (\RuntimeException $e) {
            $dispatcher->dispatch(Event\GitEvents::GIT_ERROR, $event);
            throw new GitException($e->getMessage());
        }
    }
}
