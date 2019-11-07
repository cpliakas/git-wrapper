<?php

declare(strict_types=1);

namespace GitWrapper\Tests;

use GitWrapper\Event\GitPrepareEvent;
use GitWrapper\Exception\GitException;
use GitWrapper\GitWrapper;
use GitWrapper\Tests\Event\TestBypassListener;
use GitWrapper\Tests\EventSubscriber\Source\TestEventSubscriber;
use Nette\Utils\Random;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractGitWrapperTestCase extends TestCase
{
    /**
     * @var string
     */
    public const REPO_DIR = 'build/test/repo';

    /**
     * @var string
     */
    public const WORKING_DIR = 'build/tests/wc';

    /**
     * @var string
     */
    public const CONFIG_EMAIL = 'testing@email.com';

    /**
     * @var string
     */
    public const CONFIG_NAME = 'Testing name';

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var GitWrapper
     */
    protected $gitWrapper;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->gitWrapper = new GitWrapper();
    }

    public function registerAndReturnEventSubscriber(): TestEventSubscriber
    {
        $eventDispatcher = $this->gitWrapper->getDispatcher();
        $testEventSubscriber = new TestEventSubscriber();
        $eventDispatcher->addSubscriber($testEventSubscriber);

        return $testEventSubscriber;
    }

    /**
     * Adds the bypass listener so that Git commands are not run.
     */
    public function addBypassListener(): TestBypassListener
    {
        $listener = new TestBypassListener();
        $dispatcher = $this->gitWrapper->getDispatcher();

        $dispatcher->addListener(GitPrepareEvent::class, function (GitPrepareEvent $gitPrepareEvent) use (
            $listener
        ): void {
            $listener->onPrepare($gitPrepareEvent);
        }, -5);

        return $listener;
    }

    /**
     * Asserts a correct Git version string was returned.
     *
     * @param string $version The version returned by the `git --version` command.
     */
    public function assertGitVersion(string $version): void
    {
        $match = preg_match('#^git version [.0-9]+#', $version);
        $this->assertNotEmpty($match);
    }

    public function runBadCommand(bool $catchException = false): void
    {
        try {
            $this->gitWrapper->git('a-bad-command');
        } catch (GitException $gitException) {
            if (! $catchException) {
                return;
            }

            throw $gitException;
        }
    }

    protected function randomString(): string
    {
        return Random::generate();
    }
}
