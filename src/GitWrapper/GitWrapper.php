<?php declare(strict_types=1);

namespace GitWrapper;

use GitWrapper\Event\GitEvents;
use GitWrapper\Event\GitLoggerListener;
use GitWrapper\Event\GitOutputEvent;
use GitWrapper\Event\GitOutputListenerInterface;
use GitWrapper\Event\GitOutputStreamListener;
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
class GitWrapper
{
    /**
     * Path to the Git binary.
     *
     * @var string
     */
    protected $gitBinary;

    /**
     * Environment variables defined in the scope of the Git command.
     *
     * @var string[]
     */
    protected $env = [];

    /**
     * The timeout of the Git command in seconds.
     *
     * @var int
     */
    protected $timeout = 60;

    /**
     * @var GitOutputListenerInterface
     */
    protected $streamListener;

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
        if (! isset($this->eventDispatcher)) {
            $this->eventDispatcher = new EventDispatcher();
        }

        return $this->eventDispatcher;
    }

    public function setDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->eventDispatcher = $dispatcher;
    }

    public function setGitBinary(string $gitBinary): void
    {
        $this->gitBinary = $gitBinary;
    }

    public function getGitBinary(): string
    {
        return $this->gitBinary;
    }

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
     * Returns the associative array of environment variables that are defined only in the scope of the Git command.
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
     * @param string $privateKey Path to the private key.
     * @param int $port Port that the SSH server being connected to listens on, defaults to 22.
     * @param string|null $wrapper Path the the GIT_SSH wrapper script, defaults to null which uses the
     *   script included with this library.
     */
    public function setPrivateKey(string $privateKey, int $port = 22, ?string $wrapper = null): void
    {
        if ($wrapper === null) {
            $wrapper = __DIR__ . '/../../bin/git-ssh-wrapper.sh';
        }

        if (! $wrapperPath = realpath($wrapper)) {
            throw new GitException('Path to GIT_SSH wrapper script could not be resolved: ' . $wrapper);
        }

        if (! $privateKeyPath = realpath($privateKey)) {
            throw new GitException('Path private key could not be resolved: ' . $privateKey);
        }

        $this->setEnvVar('GIT_SSH', $wrapperPath);
        $this->setEnvVar('GIT_SSH_KEY', $privateKeyPath);
        $this->setEnvVar('GIT_SSH_PORT', (int) $port);
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

    public function addOutputListener(GitOutputListenerInterface $listener): void
    {
        $this->getDispatcher()
            ->addListener(GitEvents::GIT_OUTPUT, [$listener, 'handleOutput']);
    }

    public function addLoggerListener(GitLoggerListener $listener): void
    {
        $this->getDispatcher()
            ->addSubscriber($listener);
    }

    public function removeOutputListener(GitOutputListenerInterface $listener): void
    {
        $this->getDispatcher()
            ->removeListener(GitEvents::GIT_OUTPUT, [$listener, 'handleOutput']);
    }

    /**
     * Set whether or not to stream real-time output to STDOUT and STDERR.
     */
    public function streamOutput(bool $streamOutput = true): void
    {
        if ($streamOutput && ! isset($this->streamListener)) {
            $this->streamListener = new GitOutputStreamListener();
            $this->addOutputListener($this->streamListener);
        }

        if (! $streamOutput && isset($this->streamListener)) {
            $this->removeOutputListener($this->streamListener);
            unset($this->streamListener);
        }
    }

    /**
     * Returns an object that interacts with a working copy.
     *
     * @param string $directory Path to the directory containing the working copy.
     *
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
     * For example, passing the "git@github.com:cpliakas/git-wrapper.git"
     * repository would return "git-wrapper".
     */
    public static function parseRepositoryName(string $repositoryUrl): string
    {
        $scheme = parse_url($repositoryUrl, PHP_URL_SCHEME);

        if ($scheme === null) {
            $parts = explode('/', $repositoryUrl);
            $path = end($parts);
        } else {
            $strpos = strpos($repositoryUrl, ':');
            $path = substr($repositoryUrl, $strpos + 1);
        }

        return basename($path, '.git');
    }

    /**
     * Executes a `git init` command.
     *
     * Create an empty git repository or reinitialize an existing one.
     *
     * @param string $directory The directory being initialized.
     * @param array $options An associative array of command line options.
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
     * @param string $repository The Git URL of the repository being cloned.
     * @param string $directory The directory that the repository will be cloned into. If null is
     *   passed, the directory will automatically be generated from the URL via
     *   the GitWrapper::parseRepositoryName() method.
     * @param array $options An associative array of command line options.
     */
    public function cloneRepository(string $repository, ?string $directory = null, array $options = []): GitWorkingCopy
    {
        if ($directory === null) {
            $directory = self::parseRepositoryName($repository);
        }

        $git = $this->workingCopy($directory);
        $git->cloneRepository($repository, $options);
        $git->setCloned(true);
        return $git;
    }

    /**
     * The command is simply a raw command line entry for everything after the
     * Git binary. For example, a `git config -l` command would be passed as
     * `config -l` via the first argument of this method.
     *
     * Note that no events are thrown by this method.
     *
     * @param string $commandLine The raw command containing the Git options and arguments. The Git
     *   binary should not be in the command, for example `git config -l` would
     *   translate to "config -l".
     * @param string|null $cwd The working directory of the Git process. Defaults to null which uses
     *   the current working directory of the PHP process.
     *
     * @return string The STDOUT returned by the Git command.
     */
    public function git(string $commandLine, ?string $cwd = null): string
    {
        $command = call_user_func_array('GitWrapper\\GitCommand::getInstance', (array) $commandLine);
        $command->executeRaw(is_string($commandLine));
        $command->setDirectory($cwd);
        return $this->run($command);
    }

    /**
     * @param \GitWrapper\GitCommand $command The Git command being executed.
     * @param string|null $cwd Explicitly specify the working directory of the Git process. Defaults
     *   to null which automatically sets the working directory based on the
     *   command being executed relative to the working copy.
     *
     * @return string The STDOUT returned by the Git command.
     */
    public function run(GitCommand $command, ?string $cwd = null): string
    {
        $process = new GitProcess($this, $command, $cwd);
        $process->run(function ($type, $buffer) use ($process, $command): void {
            $event = new GitOutputEvent($this, $process, $command, $type, $buffer);
            $this->getDispatcher()->dispatch(GitEvents::GIT_OUTPUT, $event);
        });

        return $command->notBypassed() ? $process->getOutput() : '';
    }
}
