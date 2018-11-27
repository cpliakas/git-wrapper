<?php declare(strict_types=1);

namespace GitWrapper;

use GitWrapper\Event\GitEvents;
use GitWrapper\Event\GitLoggerEventSubscriber;
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
final class GitWrapper implements GitWrapperInterface
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

    /**
     * @inheritdoc
     */
    public function getDispatcher(): EventDispatcherInterface
    {
        if ($this->eventDispatcher === null) {
            $this->eventDispatcher = new EventDispatcher();
        }

        return $this->eventDispatcher;
    }

    /**
     * @inheritdoc
     */
    public function setDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @inheritdoc
     */
    public function setGitBinary(string $gitBinary): void
    {
        $this->gitBinary = $gitBinary;
    }

    /**
     * @inheritdoc
     */
    public function getGitBinary(): string
    {
        return $this->gitBinary;
    }

    /**
     * @inheritdoc
     */
    public function setEnvVar(string $var, $value): void
    {
        $this->env[$var] = $value;
    }

    /**
     * @inheritdoc
     */
    public function unsetEnvVar(string $var): void
    {
        unset($this->env[$var]);
    }

    /**
     * @inheritdoc
     */
    public function getEnvVar(string $var, $default = null)
    {
        return $this->env[$var] ?? $default;
    }

    /**
     * @inheritdoc
     */
    public function getEnvVars(): array
    {
        return $this->env;
    }

    /**
     * @inheritdoc
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @inheritdoc
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function unsetPrivateKey(): void
    {
        $this->unsetEnvVar('GIT_SSH');
        $this->unsetEnvVar('GIT_SSH_KEY');
        $this->unsetEnvVar('GIT_SSH_PORT');
    }

    /**
     * @inheritdoc
     */
    public function addOutputListener(GitOutputListenerInterface $gitOutputListener): void
    {
        $this->getDispatcher()
            ->addListener(GitEvents::GIT_OUTPUT, [$gitOutputListener, 'handleOutput']);
    }

    /**
     * @inheritdoc
     */
    public function addLoggerEventSubscriber(GitLoggerEventSubscriber $gitLoggerEventSubscriber): void
    {
        $this->getDispatcher()
            ->addSubscriber($gitLoggerEventSubscriber);
    }

    /**
     * @inheritdoc
     */
    public function removeOutputListener(GitOutputListenerInterface $gitOutputListener): void
    {
        $this->getDispatcher()
            ->removeListener(GitEvents::GIT_OUTPUT, [$gitOutputListener, 'handleOutput']);
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function workingCopy(string $directory): GitWorkingCopyInterface
    {
        return new GitWorkingCopy($this, $directory);
    }

    /**
     * @inheritdoc
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

        /** @var string $path */
        return basename($path, '.git');
    }

    /**
     * @inheritdoc
     */
    public function init(string $directory, array $options = []): GitWorkingCopyInterface
    {
        $git = $this->workingCopy($directory);
        $git->init($options);
        $git->setCloned(true);

        return $git;
    }

    /**
     * @inheritdoc
     */
    public function cloneRepository(string $repository, ?string $directory = null, array $options = []): GitWorkingCopyInterface
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
     * @inheritdoc
     */
    public function git(string $commandLine, ?string $cwd = null): string
    {
        $command = new GitCommand($commandLine);
        $command->executeRaw(is_string($commandLine));
        $command->setDirectory($cwd);
        return $this->run($command);
    }

    /**
     * @inheritdoc
     */
    public function run(GitCommand $gitCommand, ?string $cwd = null): string
    {
        $process = new GitProcess($this, $gitCommand, $cwd);
        $process->run(function ($type, $buffer) use ($process, $gitCommand): void {
            $event = new GitOutputEvent($this, $process, $gitCommand, $type, $buffer);
            $this->getDispatcher()->dispatch(GitEvents::GIT_OUTPUT, $event);
        });

        return $gitCommand->notBypassed() ? $process->getOutput() : '';
    }
}
