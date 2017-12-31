<?php declare(strict_types=1);

namespace GitWrapper\Event;

use DomainException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class GitLoggerListener implements EventSubscriberInterface, LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Mapping of event to log level.
     *
     * @var string[]
     */
    protected $logLevelMappings = [
        GitEvents::GIT_PREPARE => LogLevel::INFO,
        GitEvents::GIT_OUTPUT => LogLevel::DEBUG,
        GitEvents::GIT_SUCCESS => LogLevel::INFO,
        GitEvents::GIT_ERROR => LogLevel::ERROR,
        GitEvents::GIT_BYPASS => LogLevel::INFO,
    ];

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

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Sets the log level mapping for an event.
     *
     * @param string|false $logLevel
     *
     */
    public function setLogLevelMapping(string $eventName, $logLevel): void
    {
        $this->logLevelMappings[$eventName] = $logLevel;
    }

    /**
     * Returns the log level mapping for an event.
     */
    public function getLogLevelMapping(string $eventName): string
    {
        if (! isset($this->logLevelMappings[$eventName])) {
            throw new DomainException('Unknown event: ' . $eventName);
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
    public function log(GitEvent $event, string $message, array $context = [], ?string $eventName = null): void
    {
        // Provide backwards compatibility with Symfony 2.
        if ($eventName === null && method_exists($event, 'getName')) {
            $eventName = $event->getName();
        }

        $method = $this->getLogLevelMapping($eventName);
        if ($method !== false) {
            $context += ['command' => $event->getProcess()->getCommandLine()];
            $this->logger->{$method}($message, $context);
        }
    }

    public function onPrepare(GitEvent $event, ?string $eventName = null): void
    {
        $this->log($event, 'Git command preparing to run', [], $eventName);
    }

    public function handleOutput(GitOutputEvent $event, ?string $eventName = null): void
    {
        $context = ['error' => $event->isError() ? true : false];
        $this->log($event, $event->getBuffer(), $context, $eventName);
    }

    public function onSuccess(GitEvent $event, ?string $eventName = null): void
    {
        $this->log($event, 'Git command successfully run', [], $eventName);
    }

    public function onError(GitEvent $event, ?string $eventName = null): void
    {
        $this->log($event, 'Error running Git command', [], $eventName);
    }

    public function onBypass(GitEvent $event, ?string $eventName = null): void
    {
        $this->log($event, 'Git command bypassed', [], $eventName);
    }
}
