<?php

declare(strict_types=1);

namespace GitWrapper;

use GitWrapper\Contract\Event\GitOutputListenerInterface;
use GitWrapper\Event\GitOutputEvent;
use GitWrapper\EventSubscriber\GitLoggerEventSubscriber;
use GitWrapper\Exception\GitException;
use GitWrapper\OutputListener\GitOutputStreamListener;
use GitWrapper\Process\GitProcess;
use GitWrapper\Strings\GitStrings;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\ExecutableFinder;

/**
 * A wrapper class around the Git binary.
 *
 * A GitWrapper object contains the necessary context to run Git commands such
 * as the path to the Git binary and environment variables. It also provides
 * helper methods to run Git commands as set up the connection to the GIT_SSH
 * wrapper script.
 */
final class GitWrapper
{
    /**
     * Path to the Git binary.
     *
     * @var string
     */
    private $gitBinary;

    /**
     * The timeout of the Git command in seconds.
     *
     * @var int
     */
    private $timeout = 60;

    /**
     * Environment variables defined in the scope of the Git command.
     *
     * @var string[]
     */
    private $env = [];

    /**
     * @var GitOutputListenerInterface
     */
    private $gitOutputListener;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(?string $gitBinary = null)
    {
        if ($gitBinary === null) {
            $finder = new ExecutableFinder();
            $gitBinary = $finder->find('git');
            if (! $gitBinary) {
                throw new GitException('Unable to find the Git executable.');
            }
        }

        $this->setGitBinary($gitBinary);
    }

    public function getDispatcher(): EventDispatcherInterface
    {
        if ($this->eventDispatcher === null) {
            $this->eventDispatcher = new EventDispatcher();
        }

        return $this->eventDispatcher;
    }

    public function setDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function setGitBinary(string $gitBinary): void
    {
        $this->gitBinary = $gitBinary;
    }

    public function getGitBinary(): string
    {
        return $this->gitBinary;
    }

    /**
     * @param mixed $value
     */
    public function setEnvVar(string $var, $value): void
    {
        $this->env[$var] = $value;
    }

    public function unsetEnvVar(string $var): void
    {
        unset($this->env[$var]);
    }

    /**
     * Returns an environment variable that is defined only in the scope of the
     * Git command.
     *
     * @param string $var The name of the environment variable, e.g. "HOME", "GIT_SSH".
     * @param mixed $default The value returned if the environment variable is not set, defaults to
     *   null.
     *
     * @return mixed
     */
    public function getEnvVar(string $var, $default = null)
    {
        return $this->env[$var] ?? $default;
    }

    /**
     * @return mixed[]
     */
    public function getEnvVars(): array
    {
        return $this->env;
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Set an alternate private key used to connect to the repository.
     *
     * This method sets the GIT_SSH environment variable to use the wrapper
     * script included with this library. It also sets the custom GIT_SSH_KEY
     * and GIT_SSH_PORT environment variables that are used by the script.
     *
     * @param string|null $wrapper Path the the GIT_SSH wrapper script, defaults to null which uses the
     *   script included with this library.
     */
    public function setPrivateKey(string $privateKey, int $port = 22, ?string $wrapper = null): void
    {
        if ($wrapper === null) {
            $wrapper = __DIR__ . '/../bin/git-ssh-wrapper.sh';
        }

        if (! $wrapperPath = realpath($wrapper)) {
            throw new GitException('Path to GIT_SSH wrapper script could not be resolved: ' . $wrapper);
        }

        if (! $privateKeyPath = realpath($privateKey)) {
            throw new GitException('Path private key could not be resolved: ' . $privateKey);
        }

        $this->setEnvVar('GIT_SSH', $wrapperPath);
        $this->setEnvVar('GIT_SSH_KEY', $privateKeyPath);
        $this->setEnvVar('GIT_SSH_PORT', $port);
    }

    /**
     * Unsets the private key by removing the appropriate environment variables.
     */
    public function unsetPrivateKey(): void
    {
        $this->unsetEnvVar('GIT_SSH');
        $this->unsetEnvVar('GIT_SSH_KEY');
        $this->unsetEnvVar('GIT_SSH_PORT');
    }

    public function addOutputListener(GitOutputListenerInterface $gitOutputListener): void
    {
        $this->getDispatcher()
            ->addListener(GitOutputEvent::class, [$gitOutputListener, 'handleOutput']);
    }

    public function addLoggerEventSubscriber(GitLoggerEventSubscriber $gitLoggerEventSubscriber): void
    {
        $this->getDispatcher()
            ->addSubscriber($gitLoggerEventSubscriber);
    }

    public function removeOutputListener(GitOutputListenerInterface $gitOutputListener): void
    {
        $this->getDispatcher()
            ->removeListener(GitOutputEvent::class, [$gitOutputListener, 'handleOutput']);
    }

    /**
     * Set whether or not to stream real-time output to STDOUT and STDERR.
     */
    public function streamOutput(bool $streamOutput = true): void
    {
        if ($streamOutput && ! isset($this->gitOutputListener)) {
            $this->gitOutputListener = new GitOutputStreamListener();
            $this->addOutputListener($this->gitOutputListener);
        }

        if (! $streamOutput && isset($this->gitOutputListener)) {
            $this->removeOutputListener($this->gitOutputListener);
            unset($this->gitOutputListener);
        }
    }

    /**
     * Returns an object that interacts with a working copy.
     *
     * @param string $directory Path to the directory containing the working copy.
     */
    public function workingCopy(string $directory): GitWorkingCopy
    {
        return new GitWorkingCopy($this, $directory);
    }

    /**
     * Returns the version of the installed Git client.
     */
    public function version(): string
    {
        return $this->git('--version');
    }

    /**
     * Executes a `git init` command.
     *
     * Create an empty git repository or reinitialize an existing one.
     *
     * @param mixed[] $options An associative array of command line options.
     */
    public function init(string $directory, array $options = []): GitWorkingCopy
    {
        $git = $this->workingCopy($directory);
        $git->init($options);
        $git->setCloned(true);

        return $git;
    }

    /**
     * Executes a `git clone` command and returns a working copy object.
     *
     * Clone a repository into a new directory. Use @see GitWorkingCopy::cloneRepository()
     * instead for more readable code.
     *
     * @param string $directory The directory that the repository will be cloned into. If null is
     *   passed, the directory will be generated from the URL with @see GitStrings::parseRepositoryName().
     * @param mixed[] $options
     */
    public function cloneRepository(string $repository, ?string $directory = null, array $options = []): GitWorkingCopy
    {
        if ($directory === null) {
            $directory = GitStrings::parseRepositoryName($repository);
        }

        $git = $this->workingCopy($directory);
        $git->cloneRepository($repository, $options);
        $git->setCloned(true);
        return $git;
    }

    /**
     * The command is simply a raw command line entry for everything after the Git binary.
     * For example, a `git config -l` command would be passed as `config -l` via the first argument of this method.
     *
     * @return string The STDOUT returned by the Git command.
     */
    public function git(string $commandLine, ?string $cwd = null): string
    {
        $command = new GitCommand($commandLine);
        $command->executeRaw(is_string($commandLine));
        $command->setDirectory($cwd);
        return $this->run($command);
    }

    /**
     * @return string The STDOUT returned by the Git command.
     */
    public function run(GitCommand $gitCommand, ?string $cwd = null): string
    {
        $process = new GitProcess($this, $gitCommand, $cwd);
        $process->run(function ($type, $buffer) use ($process, $gitCommand): void {
            $event = new GitOutputEvent($this, $process, $gitCommand, $type, $buffer);
            $this->getDispatcher()->dispatch($event);
        });

        return $gitCommand->notBypassed() ? $process->getOutput() : '';
    }
}
