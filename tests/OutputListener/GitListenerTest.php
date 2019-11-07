<?php

declare(strict_types=1);

namespace GitWrapper\Tests\OutputListener;

use GitWrapper\Event\GitSuccessEvent;
use GitWrapper\GitCommand;
use GitWrapper\Tests\AbstractGitWrapperTestCase;
use Symfony\Component\Process\Process;

final class GitListenerTest extends AbstractGitWrapperTestCase
{
    public function testListener(): void
    {
        $listener = $this->addListener();
        $this->gitWrapper->version();

        $this->assertTrue($listener->methodCalled('onPrepare'));
        $this->assertTrue($listener->methodCalled('onSuccess'));
        $this->assertFalse($listener->methodCalled('onError'));
        $this->assertFalse($listener->methodCalled('onBypass'));
    }

    public function testListenerError(): void
    {
        $listener = $this->addListener();
        $this->runBadCommand(true);

        $this->assertTrue($listener->methodCalled('onPrepare'));
        $this->assertFalse($listener->methodCalled('onSuccess'));
        $this->assertTrue($listener->methodCalled('onError'));
        $this->assertFalse($listener->methodCalled('onBypass'));
    }

    public function testGitBypass(): void
    {
        $this->addBypassListener();
        $listener = $this->addListener();

        $output = $this->gitWrapper->version();

        $this->assertTrue($listener->methodCalled('onPrepare'));
        $this->assertFalse($listener->methodCalled('onSuccess'));
        $this->assertFalse($listener->methodCalled('onError'));
        $this->assertTrue($listener->methodCalled('onBypass'));

        $this->assertEmpty($output);
    }

    public function testEvent(): void
    {
        $process = new Process([]);
        $command = new GitCommand();

        $event = new GitSuccessEvent($this->gitWrapper, $process, $command);

        $this->assertSame($this->gitWrapper, $event->getWrapper());
        $this->assertSame($process, $event->getProcess());
        $this->assertSame($command, $event->getCommand());
    }
}
