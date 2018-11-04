<?php declare(strict_types=1);

namespace GitWrapper\Event;

use GitWrapper\GitException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class GitLoggerEventSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    /**
     * Mapping of event to log level.
     *
     * @var string[]
     */
    private $logLevelMappings = [
        GitEvents::GIT_PREPARE => LogLevel::INFO,
        GitEvents::GIT_OUTPUT => LogLevel::DEBUG,
        GitEvents::GIT_SUCCESS => LogLevel::INFO,
        GitEvents::GIT_ERROR => LogLevel::ERROR,
        GitEvents::GIT_BYPASS => LogLevel::INFO,
    ];

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->setLogger($logger);
    }

    /**
     * {@inheritDoc}
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function setLogLevelMapping(string $eventName, string $logLevel): void
    {
        $this->logLevelMappings[$eventName] = $logLevel;
    }

    /**
     * Returns the log level mapping for an event.
     */
    public function getLogLevelMapping(string $eventName): string
    {
        if (! isset($this->logLevelMappings[$eventName])) {
            throw new GitException(sprintf('Unknown event "%s"', $eventName));
        }

        return $this->logLevelMappings[$eventName];
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            GitEvents::GIT_PREPARE => ['onPrepare', 0],
            GitEvents::GIT_OUTPUT => ['handleOutput', 0],
            GitEvents::GIT_SUCCESS => ['onSuccess', 0],
            GitEvents::GIT_ERROR => ['onError', 0],
            GitEvents::GIT_BYPASS => ['onBypass', 0],
        ];
    }

    /**
     * Adds a log message using the level defined in the mappings.
     *
     * @param mixed[] $context
     */
    public function log(GitEvent $gitEvent, string $message, array $context = [], ?string $eventName = null): void
    {
        // Provide backwards compatibility with Symfony 2.
        if ($eventName === null && method_exists($gitEvent, 'getName')) {
            $eventName = $gitEvent->getName();
        }

        $method = $this->getLogLevelMapping($eventName);
        $context += ['command' => $gitEvent->getProcess()->getCommandLine()];
        $this->logger->{$method}($message, $context);
    }

    public function onPrepare(GitEvent $gitEvent, ?string $eventName = null): void
    {
        $this->log($gitEvent, 'Git command preparing to run', [], $eventName);
    }

    public function handleOutput(GitOutputEvent $gitOutputEvent, ?string $eventName = null): void
    {
        $context = ['error' => $gitOutputEvent->isError() ? true : false];
        $this->log($gitOutputEvent, $gitOutputEvent->getBuffer(), $context, $eventName);
    }

    public function onSuccess(GitEvent $gitEvent, ?string $eventName = null): void
    {
        $this->log($gitEvent, 'Git command successfully run', [], $eventName);
    }

    public function onError(GitEvent $gitEvent, ?string $eventName = null): void
    {
        $this->log($gitEvent, 'Error running Git command', [], $eventName);
    }

    public function onBypass(GitEvent $gitEvent, ?string $eventName = null): void
    {
        $this->log($gitEvent, 'Git command bypassed', [], $eventName);
    }
}
