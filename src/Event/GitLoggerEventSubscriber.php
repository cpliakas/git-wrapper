<?php

declare(strict_types=1);

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
        GitPrepareEvent::class => LogLevel::INFO,
        GitOutputEvent::class => LogLevel::DEBUG,
        GitSuccessEvent::class => LogLevel::INFO,
        GitErrorEvent::class => LogLevel::ERROR,
        GitBypassEvent::class => LogLevel::INFO,
    ];

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Required by interface
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
    public static function getSubscribedEvents(): array
    {
        return [
            GitPrepareEvent::class => ['onPrepare', 0],
            GitOutputEvent::class => ['handleOutput', 0],
            GitSuccessEvent::class => ['onSuccess', 0],
            GitErrorEvent::class => ['onError', 0],
            GitBypassEvent::class => ['onBypass', 0],
        ];
    }

    /**
     * Adds a log message using the level defined in the mappings.
     *
     * @param mixed[] $context
     */
    public function log(
        AbstractGitEvent $gitEvent,
        string $message,
        array $context = [],
        ?string $eventName = null
    ): void {
        // Provide backwards compatibility with Symfony 2.
        if ($eventName === null && method_exists($gitEvent, 'getName')) {
            $eventName = $gitEvent->getName();
        }

        $method = $this->getLogLevelMapping($eventName);
        $context += ['command' => $gitEvent->getProcess()->getCommandLine()];
        $this->logger->{$method}($message, $context);
    }

    public function onPrepare(GitPrepareEvent $gitPrepareEvent, ?string $eventName = null): void
    {
        $this->log($gitPrepareEvent, 'Git command preparing to run', [], $eventName);
    }

    public function handleOutput(GitOutputEvent $gitOutputEvent, ?string $eventName = null): void
    {
        $context = ['error' => $gitOutputEvent->isError() ? true : false];
        $this->log($gitOutputEvent, $gitOutputEvent->getBuffer(), $context, $eventName);
    }

    public function onSuccess(GitSuccessEvent $gitSuccessEvent, ?string $eventName = null): void
    {
        $this->log($gitSuccessEvent, 'Git command successfully run', [], $eventName);
    }

    public function onError(GitErrorEvent $gitErrorEvent, ?string $eventName = null): void
    {
        $this->log($gitErrorEvent, 'Error running Git command', [], $eventName);
    }

    public function onBypass(GitBypassEvent $gitBypassEvent, ?string $eventName = null): void
    {
        $this->log($gitBypassEvent, 'Git command bypassed', [], $eventName);
    }
}
