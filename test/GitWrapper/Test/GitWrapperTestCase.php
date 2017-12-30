<?php declare(strict_types=1);

namespace GitWrapper\Test;

use GitWrapper\Event\GitEvents;
use GitWrapper\GitException;
use GitWrapper\GitWrapper;
use GitWrapper\Test\Event\TestBypassListener;
use GitWrapper\Test\Event\TestListener;
use Nette\Utils\Random;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class GitWrapperTestCase extends TestCase
{
    public const REPO_DIR = 'build/test/repo';

    public const WORKING_DIR = 'build/test/wc';

    public const CONFIG_EMAIL = 'opensource@chrispliakas.com';

    public const CONFIG_NAME = 'Chris Pliakas';

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @var \GitWrapper\GitWrapper
     */
    protected $wrapper;

    /**
     * Overrides PHPUnit\Framework\TestCase::setUp().
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->filesystem = new Filesystem();
        $this->wrapper = new GitWrapper();
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
        $dispatcher = $this->wrapper->getDispatcher();
        $listener = new TestListener();

        $dispatcher->addListener(GitEvents::GIT_PREPARE, [$listener, 'onPrepare']);
        $dispatcher->addListener(GitEvents::GIT_SUCCESS, [$listener, 'onSuccess']);
        $dispatcher->addListener(GitEvents::GIT_ERROR, [$listener, 'onError']);
        $dispatcher->addListener(GitEvents::GIT_BYPASS, [$listener, 'onBypass']);

        return $listener;
    }

    /**
     * Adds the bypass listener so that Git commands are not run.
     */
    public function addBypassListener(): TestBypassListener
    {
        $listener = new TestBypassListener();
        $dispatcher = $this->wrapper->getDispatcher();
        $dispatcher->addListener(GitEvents::GIT_PREPARE, [$listener, 'onPrepare'], -5);
        return $listener;
    }

    /**
     * Asserts a correct Git version string was returned.
     *
     * @param string $version The version returned by the `git --version` command.
     */
    public function assertGitVersion(string $version): void
    {
        $match = preg_match('/^git version [.0-9]+/', $version);
        $this->assertNotEmpty($match);
    }

    /**
     * @param bool $catchException Whether to catch the exception to continue script execution.
     */
    public function runBadCommand(bool $catchException = false): void
    {
        try {
            $this->wrapper->git('a-bad-command');
        } catch (GitException $gitException) {
            if (! $catchException) {
                throw $gitException;
            }
        }
    }
}
