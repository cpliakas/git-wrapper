<?php
declare(strict_types=1);

namespace GitWrapper;


use GitWrapper\Event\GitLoggerEventSubscriber;
use GitWrapper\Event\GitOutputListenerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * A wrapper class around the Git binary.
 * A GitWrapper object contains the necessary context to run Git commands such
 * as the path to the Git binary and environment variables. It also provides
 * helper methods to run Git commands as set up the connection to the GIT_SSH
 * wrapper script.
 */
interface GitWrapperInterface
{
    public function getDispatcher(): EventDispatcherInterface;

    public function setDispatcher(EventDispatcherInterface $eventDispatcher): void;

    public function setGitBinary(string $gitBinary): void;

    public function getGitBinary(): string;

    /**
     * @param mixed $value
     */
    public function setEnvVar(string $var, $value): void;

    public function unsetEnvVar(string $var): void;

    /**
     * Returns an environment variable that is defined only in the scope of the
     * Git command.
     *
     * @param string $var The name of the environment variable, e.g. "HOME", "GIT_SSH".
     * @param mixed $default The value returned if the environment variable is not set, defaults to
     *   null.
     * @return mixed
     */
    public function getEnvVar(string $var, $default = null);

    /**
     * @return mixed[]
     */
    public function getEnvVars(): array;

    public function setTimeout(int $timeout): void;

    public function getTimeout(): int;

    /**
     * Set an alternate private key used to connect to the repository.
     * This method sets the GIT_SSH environment variable to use the wrapper
     * script included with this library. It also sets the custom GIT_SSH_KEY
     * and GIT_SSH_PORT environment variables that are used by the script.
     *
     * @param string|null $wrapper Path the the GIT_SSH wrapper script, defaults to null which uses the
     *   script included with this library.
     */
    public function setPrivateKey(string $privateKey, int $port = 22, ?string $wrapper = null): void;

    /**
     * Unsets the private key by removing the appropriate environment variables.
     */
    public function unsetPrivateKey(): void;

    public function addOutputListener(GitOutputListenerInterface $gitOutputListener): void;

    public function addLoggerEventSubscriber(GitLoggerEventSubscriber $gitLoggerEventSubscriber): void;

    public function removeOutputListener(GitOutputListenerInterface $gitOutputListener): void;

    /**
     * Set whether or not to stream real-time output to STDOUT and STDERR.
     */
    public function streamOutput(bool $streamOutput = true): void;

    /**
     * Returns an object that interacts with a working copy.
     *
     * @param string $directory Path to the directory containing the working copy.
     * @return GitWorkingCopyInterface
     */
    public function workingCopy(string $directory): GitWorkingCopyInterface;

    /**
     * Returns the version of the installed Git client.
     */
    public function version(): string;

    /**
     * Executes a `git init` command.
     * Create an empty git repository or reinitialize an existing one.
     *
     * @param string $directory
     * @param mixed[] $options An associative array of command line options.
     * @return GitWorkingCopyInterface
     */
    public function init(string $directory, array $options = []): GitWorkingCopyInterface;

    /**
     * Executes a `git clone` command and returns a working copy object.
     * Clone a repository into a new directory. Use @see GitWorkingCopy::cloneRepository()
     * instead for more readable code.
     *
     * @param string $repository The Git URL of the repository being cloned.
     * @param string $directory The directory that the repository will be cloned into. If null is
     *   passed, the directory will automatically be generated from the URL via
     *   the GitWrapper::parseRepositoryName() method.
     * @param mixed[] $options An associative array of command line options.
     * @return GitWorkingCopyInterface
     */
    public function cloneRepository(string $repository, ?string $directory = null, array $options = []): GitWorkingCopyInterface;

    /**
     * The command is simply a raw command line entry for everything after the Git binary.
     * For example, a `git config -l` command would be passed as `config -l` via the first argument of this method.
     *
     * @return string The STDOUT returned by the Git command.
     */
    public function git(string $commandLine, ?string $cwd = null): string;

    /**
     * @return string The STDOUT returned by the Git command.
     */
    public function run(GitCommand $gitCommand, ?string $cwd = null): string;
}