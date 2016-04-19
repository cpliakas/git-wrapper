<?php

namespace GitWrapper\Test;

use GitWrapper\GitException;
use GitWrapper\GitWorkingCopy;
use Symfony\Component\Process\Process;

class GitWorkingCopyTest extends GitWrapperTestCase
{
    const REMOTE_REPO_DIR = 'build/test/remote';

    /**
     * Creates and initializes the local repository used for testing.
     */
    public function setUp()
    {
        parent::setUp();

        // Create the local repository.
        $this->wrapper->init(self::REPO_DIR, array('bare' => true));

        // Clone the local repository.
        $directory = 'build/test/wc_init';
        $git = $this->wrapper->clone('file://' . realpath(self::REPO_DIR), $directory);
        $git->config('user.email', self::CONFIG_EMAIL);
        $git->config('user.name', self::CONFIG_NAME);

        // Create the initial structure.
        file_put_contents($directory . '/change.me', "unchanged\n");
        $this->filesystem->touch($directory . '/move.me');
        $this->filesystem->mkdir($directory . '/a.directory', 0755);
        $this->filesystem->touch($directory . '/a.directory/remove.me');

        // Initial commit.
        $git
            ->add('*')
            ->commit('Initial commit.')
            ->push('origin', 'master', array('u' => true))
        ;

        // Create a branch, add a file.
        $branch = 'test-branch';
        file_put_contents($directory . '/branch.txt', "$branch\n");
        $git
            ->checkoutNewBranch($branch)
            ->add('branch.txt')
            ->commit('Committed testing branch.')
            ->push('origin', $branch, array('u' => true))
        ;

        // Create a tag of the branch.
        $git
            ->tag('test-tag')
            ->pushTags()
        ;

        $this->filesystem->remove($directory);
    }

    /**
     * Removes the local repository.
     */
    public function tearDown()
    {
        parent::tearDown();

        $this->filesystem->remove(self::REPO_DIR);

        if (is_dir(self::WORKING_DIR)) {
            $this->filesystem->remove(self::WORKING_DIR);
        }

        if (is_dir(self::REMOTE_REPO_DIR)) {
            $this->filesystem->remove(self::REMOTE_REPO_DIR);
        }
    }

    /**
     * Clones the local repo and returns an initialized GitWorkingCopy object.
     *
     * @param string $directory
     *   The directory that the repository is being cloned to, defaults to
     *   "test/wc".
     *
     * @return \GitWrapper\GitWorkingCopy
     */
    public function getWorkingCopy($directory = self::WORKING_DIR)
    {
        $git = $this->wrapper->workingCopy($directory);
        $git
            ->cloneRepository('file://' . realpath(self::REPO_DIR))
            ->config('user.email', self::CONFIG_EMAIL)
            ->config('user.name', self::CONFIG_NAME)
            ->clearOutput()
        ;
        return $git;
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testCallError()
    {
        $git = $this->getWorkingCopy();
        $git->badMethod();
    }

    public function testIsCloned()
    {
        $git = $this->getWorkingCopy();
        $this->assertTrue($git->isCloned());
    }

    public function testGetOutput()
    {
        $git = $this->getWorkingCopy();

        // Test getting output of a simple status command.
        $output = (string) $git->status();
        $this->assertTrue(strpos($output, 'nothing to commit') !== false);

        // Getting output should clear the buffer.
        $cleared = (string) $git;
        $this->assertEmpty($cleared);
    }

    public function testClearOutput()
    {
        $git = $this->getWorkingCopy();

        // Put stuff in the output buffer.
        $git->status();

        $git->clearOutput();
        $output = $git->getOutput();
        $this->assertEmpty($output);
    }

    public function testHasChanges()
    {
        $git = $this->getWorkingCopy();
        $this->assertFalse($git->hasChanges());

        file_put_contents(self::WORKING_DIR . '/change.me', "changed\n");
        $this->assertTrue($git->hasChanges());
    }

    public function testGetBranches()
    {
        $git = $this->getWorkingCopy();
        $branches = $git->getBranches();

        $this->assertTrue($branches instanceof \GitWrapper\GitBranches);

        // Dumb count checks. Is there a better way to do this?
        $allBranches = 0;
        foreach ($branches as $branch) {
            $allBranches++;
        }
        $this->assertEquals($allBranches, 4);

        $remoteBranches = $branches->remote();
        $this->assertEquals(count($remoteBranches), 3);
    }

    public function testFetchAll()
    {
        $git = $this->getWorkingCopy();

        $output = rtrim((string) $git->fetchAll());

        $this->assertEquals('Fetching origin', $output);
    }

    public function testGitAdd()
    {
        $git = $this->getWorkingCopy();
        $this->filesystem->touch(self::WORKING_DIR . '/add.me');

        $git->add('add.me');

        $match = (bool) preg_match('@A\\s+add\\.me@s', $git->getStatus());
        $this->assertTrue($match);
    }

    public function testGitApply()
    {
        $git = $this->getWorkingCopy();

        $patch = <<<PATCH
diff --git a/FileCreatedByPatch.txt b/FileCreatedByPatch.txt
new file mode 100644
index 0000000..dfe437b
--- /dev/null
+++ b/FileCreatedByPatch.txt
@@ -0,0 +1 @@
+contents

PATCH;
        file_put_contents(self::WORKING_DIR . '/patch.txt', $patch);
        $git->apply('patch.txt');
        $this->assertRegExp('@\?\?\\s+FileCreatedByPatch\\.txt@s', $git->getStatus());
        $this->assertEquals("contents\n", file_get_contents(self::WORKING_DIR . '/FileCreatedByPatch.txt'));
    }

    public function testGitRm()
    {
        $git = $this->getWorkingCopy();
        $git->rm('a.directory/remove.me');
        $this->assertFalse(is_file(self::WORKING_DIR . '/a.directory/remove.me'));
    }

    public function testGitMv()
    {
        $git = $this->getWorkingCopy();
        $git->mv('move.me', 'moved');

        $this->assertFalse(is_file(self::WORKING_DIR . '/move.me'));
        $this->assertTrue(is_file(self::WORKING_DIR . '/moved'));
    }

    public function testGitBranch()
    {
        $branchName = $this->randomString();

        // Create the branch.
        $git = $this->getWorkingCopy();
        $git->branch($branchName);

        // Get list of local branches.
        $branches = (string) $git->branch();

        // Check that our branch is there.
        $this->assertTrue(strpos($branches, $branchName) !== false);
    }

    public function testGitLog()
    {
        $git = $this->getWorkingCopy();
        $output = (string) $git->log();
        return $this->assertTrue(strpos($output, 'Initial commit.') !== false);
    }

    public function testGitConfig()
    {
        $git = $this->getWorkingCopy();
        $email = rtrim((string) $git->config('user.email'));
        $this->assertEquals('opensource@chrispliakas.com', $email);
    }

    public function testGitTag()
    {
        $tag = $this->randomString();

        $git = $this->getWorkingCopy();
        $git
            ->tag($tag)
            ->pushTag($tag)
        ;

        $tags = (string) $git->tag();
        $this->assertTrue(strpos($tags, $tag) !== false);
    }

    public function testGitClean()
    {
        $git = $this->getWorkingCopy();

        file_put_contents(self::WORKING_DIR . '/untracked.file', "untracked\n");

        $result = $git
            ->clean('-d', '-f')
        ;

        $this->assertSame($git, $result);
        $this->assertFileNotExists(self::WORKING_DIR . '/untracked.file');
    }

    public function testGitReset()
    {
        $git = $this->getWorkingCopy();
        file_put_contents(self::WORKING_DIR . '/change.me', "changed\n");

        $this->assertTrue($git->hasChanges());
        $git->reset(array('hard' => true));
        $this->assertFalse($git->hasChanges());
    }

    public function testGitStatus()
    {
        $git = $this->getWorkingCopy();
        file_put_contents(self::WORKING_DIR . '/change.me', "changed\n");
        $output = (string) $git->status(array('s' => true));
        $this->assertEquals(" M change.me\n", $output);
    }

    public function testGitPull()
    {
        $git = $this->getWorkingCopy();
        $output = (string) $git->pull();
        $this->assertEquals("Already up-to-date.\n", $output);
    }

    public function testGitArchive()
    {
        $archiveName = uniqid().'.tar';
        $archivePath = '/tmp/'.$archiveName;
        $git = $this->getWorkingCopy();
        $output = (string) $git->archive('HEAD', array('o' => $archivePath));
        $this->assertEquals("", $output);
        $this->assertFileExists($archivePath);
    }

    /**
     * This tests an odd case where sometimes even though a command fails and an exception is thrown
     * the result of Process::getErrorOutput() is empty because the output is sent to STDOUT instead of STDERR. So
     * there's a code path in GitProcess::run() to check the output from Process::getErrorOutput() and if it's empty use
     * the result from Process::getOutput() instead
     */
    public function testGitPullErrorWithEmptyErrorOutput()
    {
        $git = $this->getWorkingCopy();

        try {
            $git->commit('Nothing to commit so generates an error / not error');
        } catch(GitException $exception) {
            $errorOutput = $exception->getMessage();
        }

        $this->assertTrue(strpos($errorOutput, "Your branch is up-to-date with 'origin/master'.") !== false);
    }

    public function testGitDiff()
    {
        $git = $this->getWorkingCopy();
        file_put_contents(self::WORKING_DIR . '/change.me', "changed\n");
        $output = (string) $git->diff();
        $this->assertTrue(strpos($output, 'diff --git a/change.me b/change.me') === 0);
    }

    public function testGitGrep()
    {
        $git = $this->getWorkingCopy();
        $output = (string) $git->grep('changed', '--', '*.me');
        $this->assertTrue(strpos($output, 'change.me') === 0);
    }

    public function testGitShow()
    {
        $git = $this->getWorkingCopy();
        $output = (string) $git->show('test-tag');
        $this->assertTrue(strpos($output, 'commit ') === 0);
    }

    public function testGitBisect()
    {
        $git = $this->getWorkingCopy();
        $output = (string) $git->bisect('help');
        $this->assertTrue(stripos($output, 'usage: git bisect') === 0);
    }

    public function testGitRemote()
    {
        $git = $this->getWorkingCopy();
        $output = (string) $git->remote();
        $this->assertEquals(rtrim($output), 'origin');
    }

    public function testRebase()
    {
        $git = $this->getWorkingCopy();
        $git
            ->checkout('test-branch')
            ->clearOutput()
        ;

        $output = (string) $git->rebase('test-branch', 'master');
        $this->assertTrue(strpos($output, 'First, rewinding head') === 0);
    }

    public function testMerge()
    {
        $git = $this->getWorkingCopy();
        $git
            ->checkout('test-branch')
            ->checkout('master')
            ->clearOutput()
        ;

        $output = (string) $git->merge('test-branch');
        $this->assertTrue(strpos($output, 'Updating ') === 0);
    }

    public function testOutputListener()
    {
        $git = $this->getWorkingCopy();

        $listener = new Event\TestOutputListener();
        $git->getWrapper()->addOutputListener($listener);

        $git->status();
        $event = $listener->getLastEvent();

        $expectedType = Process::OUT;
        $this->assertEquals($expectedType, $event->getType());

        $this->assertTrue(stripos($event->getBuffer(), 'nothing to commit') !== false);
    }

    public function testLiveOutput()
    {
        $git = $this->getWorkingCopy();

        // Capture output written to STDOUT and use echo so we can suppress and
        // capture it using normal output buffering.
        stream_filter_register('suppress', '\GitWrapper\Test\StreamSuppressFilter');
        $stdoutSuppress = stream_filter_append(STDOUT, 'suppress');

        $git->getWrapper()->streamOutput(true);
        ob_start();
        $git->status();
        $contents = ob_get_contents();
        ob_end_clean();

        $this->assertTrue(stripos($contents, 'nothing to commit') !== false);

        $git->clearOutput();
        $git->getWrapper()->streamOutput(false);
        ob_start();
        $git->status();
        $empty = ob_get_contents();
        ob_end_clean();

        $this->assertEmpty($empty);

        stream_filter_remove($stdoutSuppress);
    }

    public function testCommitWithAuthor()
    {
        $git = $this->getWorkingCopy();
        file_put_contents(self::WORKING_DIR . '/commit.txt', "created\n");

        $this->assertTrue($git->hasChanges());

        $git
            ->add('commit.txt')
            ->commit(array(
                'm' => 'Committed testing branch.',
                'a' => true,
                'author' => 'test <test@lol.com>'
            ))
        ;

        $output = (string) $git->log();
        $this->assertContains('Committed testing branch', $output);
        $this->assertContains('Author: test <test@lol.com>', $output);
    }

    public function testIsTracking()
    {
        $git = $this->getWorkingCopy();

        // The master branch is a remote tracking branch.
        $this->assertTrue($git->isTracking());

        // Create a new branch without pushing it, so it does not have a remote.
        $git->checkoutNewBranch('non-tracking-branch');
        $this->assertFalse($git->isTracking());
    }

    public function testIsUpToDate()
    {
        $git = $this->getWorkingCopy();

        // The default test branch is up-to-date with its remote.
        $git->checkout('test-branch');
        $this->assertTrue($git->isUpToDate());

        // If we create a new commit, we are still up-to-date.
        file_put_contents(self::WORKING_DIR . '/commit.txt', "created\n");
        $git
            ->add('commit.txt')
            ->commit(array(
                'm' => '1 commit ahead. Still up-to-date.',
                'a' => true,
            ))
        ;
        $this->assertTrue($git->isUpToDate());

        // Reset the branch to its first commit, so that it is 1 commit behind.
        $git->reset(
            'HEAD~2',
            array('hard' => true)
        );

        $this->assertFalse($git->isUpToDate());
    }

    public function testIsAhead()
    {
        $git = $this->getWorkingCopy();

        // The default master branch is not ahead of the remote.
        $this->assertFalse($git->isAhead());

        // Create a new commit, so that the branch is 1 commit ahead.
        file_put_contents(self::WORKING_DIR . '/commit.txt', "created\n");
        $git
            ->add('commit.txt')
            ->commit(array('m' => '1 commit ahead.'))
        ;

        $this->assertTrue($git->isAhead());
    }

    public function testIsBehind()
    {
        $git = $this->getWorkingCopy();

        // The default test branch is not behind the remote.
        $git->checkout('test-branch');
        $this->assertFalse($git->isBehind());

        // Reset the branch to its parent commit, so that it is 1 commit behind.
        $git->reset(
            'HEAD^',
            array('hard' => true)
        );

        $this->assertTrue($git->isBehind());
    }

    public function testNeedsMerge()
    {
        $git = $this->getWorkingCopy();

        // The default test branch does not need to be merged with the remote.
        $git->checkout('test-branch');
        $this->assertFalse($git->needsMerge());

        // Reset the branch to its parent commit, so that it is 1 commit behind.
        // This does not require the branches to be merged.
        $git->reset(
            'HEAD^',
            array('hard' => true)
        );
        $this->assertFalse($git->needsMerge());

        // Create a new commit, so that the branch is also 1 commit ahead. Now a
        // merge is needed.
        file_put_contents(self::WORKING_DIR . '/commit.txt', "created\n");
        $git
            ->add('commit.txt')
            ->commit(array('m' => '1 commit ahead.'))
        ;
        $this->assertTrue($git->needsMerge());

        // Merge the remote, so that we are no longer behind, but only ahead. A
        // merge should then no longer be needed.
        $git->merge('@{u}');
        $this->assertFalse($git->needsMerge());
    }

    /**
     * @dataProvider addRemoteDataProvider
     */
    public function testAddRemote($options, $asserts)
    {
        $this->createRemote();
        $git = $this->getWorkingCopy();
        $git->addRemote('remote', 'file://' . realpath(self::REMOTE_REPO_DIR), $options);
        $this->assertTrue($git->hasRemote('remote'));
        foreach ($asserts as $method => $parameters) {
            array_unshift($parameters, $git);
            call_user_func_array(array($this, $method), $parameters);
        }
    }

    public function addRemoteDataProvider()
    {
        return array(
            // Test default options: nothing is fetched.
            array(
                array(),
                array(
                    'assertNoRemoteBranches' => array(array('remote/master', 'remote/remote-branch')),
                    'assertNoGitTag' => array('remote-tag'),
                    'assertNoRemoteMaster' => array(),
                ),
            ),
            // The fetch option should retrieve the remote branches and tags,
            // but not set up a master branch.
            array(
                array('-f' => true),
                array(
                    'assertRemoteBranches' => array(array('remote/master', 'remote/remote-branch')),
                    'assertGitTag' => array('remote-tag'),
                    'assertNoRemoteMaster' => array(),
                ),
            ),
            // The --no-tags options should omit importing tags.
            array(
                array('-f' => true, '--no-tags' => true),
                array(
                    'assertRemoteBranches' => array(array('remote/master', 'remote/remote-branch')),
                    'assertNoGitTag' => array('remote-tag'),
                    'assertNoRemoteMaster' => array(),
                ),
            ),
            // The -t option should limit the remote branches that are imported.
            // By default git fetch only imports the tags of the fetched
            // branches. No tags were added to the master branch, so the tag
            // should not be imported.
            array(
                array('-f' => true, '-t' => array('master')),
                array(
                    'assertRemoteBranches' => array(array('remote/master')),
                    'assertNoRemoteBranches' => array(array('remote/remote-branch')),
                    'assertNoGitTag' => array('remote-tag'),
                    'assertNoRemoteMaster' => array(),
                ),
            ),
            // The -t option in combination with the --tags option should fetch
            // all tags, so now the tag should be there.
            array(
                array('-f' => true, '-t' => array('master'), '--tags' => true),
                array(
                    // @todo Versions prior to git 1.9.0 do not fetch the
                    //   branches when the `--tags` option is specified.
                    //   Uncomment this line when Travis CI updates to a more
                    //   recent version of git.
                    // @see https://github.com/git/git/blob/master/Documentation/RelNotes/1.9.0.txt
                    // 'assertRemoteBranches' => array(array('remote/master')),
                    'assertNoRemoteBranches' => array(array('remote/remote-branch')),
                    'assertGitTag' => array('remote-tag'),
                    'assertNoRemoteMaster' => array(),
                ),
            ),
            // The -m option should set up a remote master branch.
            array(
                array('-f' => true, '-m' => 'remote-branch'),
                array(
                    'assertRemoteBranches' => array(array('remote/master', 'remote/remote-branch')),
                    'assertGitTag' => array('remote-tag'),
                    'assertRemoteMaster' => array(),
                ),
            ),
        );
    }

    public function testRemoveRemote()
    {
        $this->createRemote();
        $git = $this->getWorkingCopy();
        $git->addRemote('remote', 'file://' . realpath(self::REMOTE_REPO_DIR));
        $this->assertTrue($git->hasRemote('remote'));

        // The remote should be gone after it is removed.
        $git->removeRemote('remote');
        $this->assertFalse($git->hasRemote('remote'));
    }

    public function testHasRemote()
    {
        $this->createRemote();
        $git = $this->getWorkingCopy();
        // The remote should be absent before it is added.
        $this->assertFalse($git->hasRemote('remote'));
        $git->addRemote('remote', 'file://' . realpath(self::REMOTE_REPO_DIR));
        // The remote should be present after it is added.
        $this->assertTrue($git->hasRemote('remote'));
    }

    public function testGetRemote()
    {
        $this->createRemote();
        $git = $this->getWorkingCopy();
        $path = 'file://' . realpath(self::REMOTE_REPO_DIR);
        $git->addRemote('remote', $path);

        // Both the 'fetch' and 'push' URIs should be populated and point to the
        // correct location.
        $remote = $git->getRemote('remote');
        $this->assertEquals($path, $remote['fetch']);
        $this->assertEquals($path, $remote['push']);
    }

    public function testGetRemotes()
    {
        $this->createRemote();
        $git = $this->getWorkingCopy();

        // Since our working copy is a clone, the 'origin' remote should be
        // present by default.
        $remotes = $git->getRemotes();
        $this->assertArrayHasKey('origin', $remotes);
        $this->assertArrayNotHasKey('remote', $remotes);

        // If we add a second remote, both it and the 'origin' remotes should be
        // present.
        $git->addRemote('remote', 'file://' . realpath(self::REMOTE_REPO_DIR));
        $remotes = $git->getRemotes();
        $this->assertArrayHasKey('origin', $remotes);
        $this->assertArrayHasKey('remote', $remotes);
    }

    /**
     * @dataProvider getRemoteUrlDataProvider
     */
    public function testGetRemoteUrl($remote, $operation, $expected)
    {
        $this->createRemote();
        $git = $this->getWorkingCopy();
        $git->addRemote('remote', 'file://' . realpath(self::REMOTE_REPO_DIR));
        $this->assertEquals('file://' . realpath($expected), $git->getRemoteUrl($remote, $operation));
    }

    public function getRemoteUrlDataProvider() {
        return array(
            array('origin', 'fetch', self::REPO_DIR),
            array('origin', 'push', self::REPO_DIR),
            array('remote', 'fetch', self::REMOTE_REPO_DIR),
            array('remote', 'push', self::REMOTE_REPO_DIR),
        );
    }

    protected function assertGitTag(GitWorkingCopy $repository, $tag)
    {
        $repository->run(array('rev-parse ' . $tag));
    }

    protected function assertNoGitTag(GitWorkingCopy $repository, $tag)
    {
        try {
            $repository->run(array('rev-parse ' . $tag));
        } catch (GitException $e) {
            // Expected result. The tag does not exist.
            return;
        }
        throw new \Exception("Expecting that the tag '$tag' doesn't exist, but it does.");
    }

    protected function assertRemoteMaster(GitWorkingCopy $repository)
    {
        $repository->run(array('rev-parse remote/HEAD'));
    }

    protected function assertNoRemoteMaster(GitWorkingCopy $repository)
    {
        try {
            $repository->run(array('rev-parse remote/HEAD'));
        } catch (GitException $e) {
            // Expected result. The remote master does not exist.
            return;
        }
        throw new \Exception("Expecting that the remote master doesn't exist, but it does.");
    }

    protected function assertRemoteBranches(GitWorkingCopy $repository, $branches)
    {
        foreach ($branches as $branch) {
            $this->assertRemoteBranch($repository, $branch);
        }
    }

    protected function assertRemoteBranch(GitWorkingCopy $repository, $branch)
    {
        $branches = $repository->getBranches()->remote();
        $this->assertArrayHasKey($branch, array_flip($branches));
    }

    protected function assertNoRemoteBranches(GitWorkingCopy $repository, $branches)
    {
        foreach ($branches as $branch) {
            $this->assertNoRemoteBranch($repository, $branch);
        }
    }

    protected function assertNoRemoteBranch(GitWorkingCopy $repository, $branch)
    {
        $branches = $repository->getBranches()->remote();
        $this->assertArrayNotHasKey($branch, array_flip($branches));
    }

    protected function createRemote()
    {
        // Create a clone of the working copy that will serve as a remote.
        $git = $this->wrapper->clone('file://' . realpath(self::REPO_DIR), self::REMOTE_REPO_DIR);
        $git->config('user.email', self::CONFIG_EMAIL);
        $git->config('user.name', self::CONFIG_NAME);

        // Make a change to the remote repo.
        file_put_contents(self::REMOTE_REPO_DIR . '/remote.file', "remote code\n");
        $git
            ->add('*')
            ->commit('Remote change.')
        ;

        // Create a branch.
        $branch = 'remote-branch';
        file_put_contents(self::REMOTE_REPO_DIR . '/remote-branch.txt', "$branch\n");
        $git
            ->checkoutNewBranch($branch)
            ->add('*')
            ->commit('Commit remote testing branch.')
        ;

        // Create a tag.
        $git->tag('remote-tag');
    }
}
