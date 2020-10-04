<?php

declare(strict_types=1);

namespace GitWrapper\Tests\EventSubscriber;

use GitWrapper\EventSubscriber\GitLoggerEventSubscriber;
use GitWrapper\Exception\GitException;
use GitWrapper\GitCommand;
use GitWrapper\Tests\AbstractGitWrapperTestCase;
use GitWrapper\Tests\EventSubscriber\Source\TestLogger;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Throwable;

final class GitLoggerEventSubscriberTest extends AbstractGitWrapperTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        if (is_dir(self::REPO_DIR)) {
            $this->filesystem->remove(self::REPO_DIR);
        }
    }

    public function testSetLogLevelMapping(): void
    {
        $gitLoggerEventSubscriber = new GitLoggerEventSubscriber(new NullLogger());
        $gitLoggerEventSubscriber->setLogLevelMapping('test.event', 'test-level');

        $this->assertSame('test-level', $gitLoggerEventSubscriber->getLogLevelMapping('test.event'));
    }

    public function testGetInvalidLogLevelMapping(): void
    {
        $this->expectException(GitException::class);

        $gitLoggerEventSubscriber = new GitLoggerEventSubscriber(new NullLogger());
        $gitLoggerEventSubscriber->getLogLevelMapping('bad.event');
    }

    public function testRegisterLogger(): void
    {
        $logger = new TestLogger();
        $this->gitWrapper->addLoggerEventSubscriber(new GitLoggerEventSubscriber($logger));
        $git = $this->gitWrapper->init(self::REPO_DIR, [
            'bare' => true,
        ]);

        $this->assertSame('Git command preparing to run', $logger->messages[0]);
        $this->assertSame(
            'Initialized empty Git repository in ' . realpath(self::REPO_DIR) . "/\n",
            $logger->messages[1]
        );
        $this->assertSame('Git command successfully run', $logger->messages[2]);

        $this->assertArrayHasKey('command', $logger->contexts[0]);
        $this->assertArrayHasKey('command', $logger->contexts[1]);
        $this->assertArrayHasKey('error', $logger->contexts[1]);
        $this->assertArrayHasKey('command', $logger->contexts[2]);

        $this->assertSame(LogLevel::INFO, $logger->levels[0]);
        $this->assertSame(LogLevel::DEBUG, $logger->levels[1]);
        $this->assertSame(LogLevel::INFO, $logger->levels[2]);

        try {
            $logger->clearMessages();
            $git->commit('fatal: This operation must be run in a work tree');
        } catch (Throwable $throwable) {
            // Nothing to do, this is expected.
        }

        $this->assertSame('Error running Git command', $logger->messages[2]);
        $this->assertArrayHasKey('command', $logger->contexts[2]);
        $this->assertSame(LogLevel::ERROR, $logger->levels[2]);
    }

    public function testLogBypassedCommand(): void
    {
        $logger = new TestLogger();
        $this->gitWrapper->addLoggerEventSubscriber(new GitLoggerEventSubscriber($logger));

        $command = new GitCommand('status', [
            's' => true,
        ]);
        $command->bypass();

        $this->gitWrapper->run($command);

        $this->assertSame('Git command bypassed', $logger->messages[1]);
        $this->assertArrayHasKey('command', $logger->contexts[1]);
        $this->assertSame(LogLevel::INFO, $logger->levels[1]);
    }
}
