<?php

namespace GitWrapper\Test;

use Exception;
use GitWrapper\Event\GitLoggerListener;
use GitWrapper\GitCommand;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

class GitLoggerListenerTest extends GitWrapperTestCase
{
    protected function tearDown()
    {
        parent::tearDown();

        if (is_dir(self::REPO_DIR)) {
            $this->filesystem->remove(self::REPO_DIR);
        }
    }

    public function testGetLogger()
    {
        $log = new NullLogger();
        $listener = new GitLoggerListener($log);
        $this->assertSame($log, $listener->getLogger());
    }

    public function testSetLogLevelMapping()
    {
        $listener = new GitLoggerListener(new NullLogger());
        $listener->setLogLevelMapping('test.event', 'test-level');
        $this->assertSame('test-level', $listener->getLogLevelMapping('test.event'));
    }

    /**
     * @expectedException \DomainException
     */
    public function testGetInvalidLogLevelMapping()
    {
        $listener = new GitLoggerListener(new NullLogger());
        $listener->getLogLevelMapping('bad.event');
    }

    public function testRegisterLogger()
    {
        $logger = new TestLogger();
        $this->wrapper->addLoggerListener(new GitLoggerListener($logger));
        $git = $this->wrapper->init(self::REPO_DIR, ['bare' => true]);

        $this->assertSame('Git command preparing to run', $logger->messages[0]);
        $this->assertSame('Initialized empty Git repository in ' . realpath(self::REPO_DIR) . "/\n", $logger->messages[1]);
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
        } catch (Exception $e) {
            // Nothing to do, this is expected.
        }

        $this->assertSame('Error running Git command', $logger->messages[2]);
        $this->assertArrayHasKey('command', $logger->contexts[2]);
        $this->assertSame(LogLevel::ERROR, $logger->levels[2]);
    }

    public function testLogBypassedCommand()
    {
        $logger = new TestLogger();
        $this->wrapper->addLoggerListener(new GitLoggerListener($logger));

        $command = GitCommand::getInstance('status', ['s' => true]);
        $command->bypass();

        $this->wrapper->run($command);

        $this->assertSame('Git command bypassed', $logger->messages[1]);
        $this->assertArrayHasKey('command', $logger->contexts[1]);
        $this->assertSame(LogLevel::INFO, $logger->levels[1]);
    }
}
