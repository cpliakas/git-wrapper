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

    /**
     * @param int $length
     * @param string $charlist
     * @return string
     */
    public function randomString(int $length = 10, string $charlist = '0-9a-z'): string
    {
        return Random::generate($length, $charlist);
    }

    /**
     * Adds the test listener for all events, returns the listener.
     */
    public function addListener(): TestListener
    {
        $dispatcher = $this->gitWrapper->getDispatcher();
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
        $dispatcher = $this->gitWrapper->getDispatcher();
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
