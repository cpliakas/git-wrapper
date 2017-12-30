<?php

namespace GitWrapper\Event;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GitLoggerListener implements EventSubscriberInterface, LoggerAwareInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Mapping of event to log level.
     *
     * @var array
     */
    protected $logLevelMappings = [
        GitEvents::GIT_PREPARE => LogLevel::INFO,
        GitEvents::GIT_OUTPUT  => LogLevel::DEBUG,
        GitEvents::GIT_SUCCESS => LogLevel::INFO,
        GitEvents::GIT_ERROR   => LogLevel::ERROR,
        GitEvents::GIT_BYPASS  => LogLevel::INFO,
    ];

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->setLogger($logger);
    }

    /**
     * {@inheritDoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Sets the log level mapping for an event.
     *
     * @param string $eventName
     * @param string|false $logLevel
     *
     * @return \GitWrapper\Event\GitLoggerListener
     */
    public function setLogLevelMapping($eventName, $logLevel)
    {
        $this->logLevelMappings[$eventName] = $logLevel;
        return $this;
    }

    /**
     * Returns the log level mapping for an event.
     *
     * @param string $eventName
     *
     * @return string
     *
     * @throws \DomainException
     */
    public function getLogLevelMapping($eventName)
    {
        if (!isset($this->logLevelMappings[$eventName])) {
            throw new \DomainException('Unknown event: ' . $eventName);
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
            GitEvents::GIT_OUTPUT  => ['handleOutput', 0],
            GitEvents::GIT_SUCCESS => ['onSuccess', 0],
            GitEvents::GIT_ERROR   => ['onError', 0],
            GitEvents::GIT_BYPASS  => ['onBypass', 0],
        ];
    }

    /**
     * Adds a log message using the level defined in the mappings.
     *
     * @param \GitWrapper\Event\GitEvent $event
     * @param string $message
     * @param array $context
     * @param string $eventName
     *
     * @throws \DomainException
     */
    public function log(GitEvent $event, $message, array $context = [], $eventName = NULL)
    {
        // Provide backwards compatibility with Symfony 2.
        if (empty($eventName) && method_exists($event, 'getName')) {
            $eventName = $event->getName();
        }
        $method = $this->getLogLevelMapping($eventName);
        if ($method !== false) {
            $context += ['command' => $event->getProcess()->getCommandLine()];
            $this->logger->$method($message, $context);
        }
    }

    public function onPrepare(GitEvent $event, $eventName = NULL)
    {
        $this->log($event, 'Git command preparing to run', [], $eventName);
    }

    public function handleOutput(GitOutputEvent $event, $eventName = NULL)
    {
        $context = ['error' => $event->isError() ? true : false];
        $this->log($event, $event->getBuffer(), $context, $eventName);
    }

    public function onSuccess(GitEvent $event, $eventName = NULL)
    {
        $this->log($event, 'Git command successfully run', [], $eventName);
    }

    public function onError(GitEvent $event, $eventName = NULL)
    {
        $this->log($event, 'Error running Git command', [], $eventName);
    }

    public function onBypass(GitEvent $event, $eventName = NULL)
    {
        $this->log($event, 'Git command bypassed', [], $eventName);
    }
}
