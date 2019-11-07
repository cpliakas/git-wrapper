<?php

declare(strict_types=1);

namespace GitWrapper\Tests;

use GitWrapper\Event\GitBypassEvent;
use GitWrapper\Event\GitErrorEvent;
use GitWrapper\Event\GitPrepareEvent;
use GitWrapper\Event\GitSuccessEvent;
use GitWrapper\Exception\GitException;
use GitWrapper\GitWrapper;
use GitWrapper\Tests\Event\TestBypassListener;
use GitWrapper\Tests\Event\TestListener;
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
    public const CONFIG_EMAIL = 'opensource@chrispliakas.com';

    /**
     * @var string
     */
    public const CONFIG_NAME = 'Chris Pliakas';

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
        parent::setUp();
        $this->filesystem = new Filesystem();
        $this->gitWrapper = new GitWrapper();
    }

    public function randomString(): string
    {
        return Random::generate();
    }

    /**
     * Adds the test listener for all events, returns the listener.
     */
    public function addListener(): TestListener
    {
        $dispatcher = $this->gitWrapper->getDispatcher();
        $listener = new TestListener();

        $dispatcher->addListener(GitPrepareEvent::class, [$listener, 'onPrepare']);
        $dispatcher->addListener(GitSuccessEvent::class, [$listener, 'onSuccess']);
        $dispatcher->addListener(GitErrorEvent::class, [$listener, 'onError']);
        $dispatcher->addListener(GitBypassEvent::class, [$listener, 'onBypass']);

        return $listener;
    }

    /**
     * Adds the bypass listener so that Git commands are not run.
     */
    public function addBypassListener(): TestBypassListener
    {
        $listener = new TestBypassListener();
        $dispatcher = $this->gitWrapper->getDispatcher();
        $dispatcher->addListener(GitPrepareEvent::class, [$listener, 'onPrepare'], -5);
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

    /**
     * @param bool $catchException Whether to catch the exception to continue script execution.
     */
    public function runBadCommand(bool $catchException = false): void
    {
        try {
            $this->gitWrapper->git('a-bad-command');
        } catch (GitException $gitException) {
            if (! $catchException) {
                throw $gitException;
            }
        }
    }
}
