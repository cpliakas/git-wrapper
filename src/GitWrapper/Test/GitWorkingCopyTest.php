<?php

namespace GitWrapper\Test;

use GitWrapper\GitWorkingCopy;

class GitWorkingCopyTest extends GitWrapperTestCase
{
    const REPO_DIR = 'test/repo';
    const WORKING_DIR = 'test/wc';

    /**
     * Creates and initializes the local repository used for testing.
     */
    public function setUp()
    {
        parent::setUp();

        // Create the local repository.
        $this->_wrapper->init(self::REPO_DIR, array('bare' => true));

        // Clone the local repository.
        $directory = 'test/wc_init';
        $git = $this->_wrapper->clone('file://' . realpath(self::REPO_DIR), $directory);

        // Create the initial structure.
        file_put_contents($directory . '/change.me', "unchanged\n");
        touch($directory . '/move.me');
        mkdir($directory . '/a.directory', 0755);
        touch($directory . '/a.directory/remove.me');

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
            ->checkout($branch, array('b' => true))
            ->add('branch.txt')
            ->commit('Committed testing branch.')
            ->push('origin', $branch, array('u' => true))
        ;

        // Create a tag of the branch.
        $git
            ->tag('test-tag')
            ->pushTags()
        ;

        // Remove the working copy.
        self::rmdir($directory);
    }

    /**
     * Removes the local repository.
     */
    public function tearDown()
    {
        parent::setUp();

        self::rmdir(self::REPO_DIR);

        if (is_dir(self::WORKING_DIR)) {
            self::rmdir(self::WORKING_DIR);
        }
    }

    /**
     * Clones the local repo and returns an initialized GitWorkingCopy object.
     *
     * @param string $directory
     *   The directory that the repository is being cloned to, defaults to
     *   "test/wc".
     *
     * @return GitWorkingCopy
     */
    public function getWorkingCopy($directory = self::WORKING_DIR)
    {
        $git = $this->_wrapper->workingCopy($directory);
        $git->cloneRepository('file://' . realpath(self::REPO_DIR));
        return $git;
    }

    /**
     * Recursive helper function to remove a non-empty directory.
     *
     * @param string $dir
     *   The directory being removed.
     *
     * @todo There has to be a more elegant, accepted way to do this.
     */
    public static function rmdir($dir)
    {
        $subdirs = array();
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if ('.' != $file && '..' != $file) {
                    $filepath = $dir . '/' . $file;
                    if (is_dir($filepath)) {
                        $subdirs[] = $filepath;
                    } else {
                        unlink($filepath);
                    }
                }
            }
            closedir($handle);
        }

        foreach ($subdirs as $subdir) {
            self::rmdir($subdir);
        }

        rmdir($dir);
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testCallError()
    {
        $git = $this->getWorkingCopy();
        $git->badMethod();
    }

    /**
     * @deprecated since version 1.0.0
     *
     * @see GitCommand::escapeFilepattern()
     */
    public function testEscapeFilepattern()
    {
        $git = $this->getWorkingCopy();
        $this->assertEquals($git->escapeFilepattern('./test.txt'), './test\.txt');
    }

    public function testIsCloned()
    {
        $git = $this->getWorkingCopy();
        $this->assertTrue($git->isCloned());
    }

    public function testGetOutput()
    {
        $git = $this->getWorkingCopy();

        // Assume last command was a clone.
        $output = (string) $git;
        $this->assertTrue(0 === strpos($output, 'Cloning into'));

        // Getting output should clear the buffer.
        $cleared = (string) $git;
        $this->assertEmpty($cleared);
    }

    public function testClearOutput()
    {
        $git = $this->getWorkingCopy();

        // Assume there is output in the buffer. The test above will fail if
        // there is not.
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
        $all_branches = 0;
        foreach ($branches as $branch) {
            $all_branches++;
        }
        $this->assertEquals($all_branches, 4);

        $remote_branches = $branches->remote();
        $this->assertEquals(count($remote_branches), 3);
    }

    public function testFetchAll()
    {
        $git = $this->getWorkingCopy();

        $git->clearOutput();
        $output = rtrim((string) $git->fetchAll());

        $this->assertEquals('Fetching origin', $output);
    }

    public function testGitAdd()
    {
        $git = $this->getWorkingCopy();
        touch(self::WORKING_DIR . '/add.me');

        $git->add('add.me');

        $match = (bool) preg_match('@A\\s+add\\.me@s', $git->getStatus());
        $this->assertTrue($match);
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
        $branch_name = $this->randomString();

        // Create the branch.
        $git = $this->getWorkingCopy();
        $git->branch($branch_name);

        // Get list of local branches.
        $git->clearOutput();
        $branches = (string) $git->branch();

        // Check that our branch is there.
        $this->assertTrue(strpos($branches, $branch_name) !== false);
    }

    public function testGitLog()
    {
        $git = $this->getWorkingCopy();

        $git->clearOutput();
        $output = (string) $git->log();

        return $this->assertTrue(strpos($output, 'Initial commit.') !== false);
    }

    public function testGitConfig()
    {
        $git = $this->getWorkingCopy();
        $git->config('user.email', 'opensource@chrispliakas.com');

        $git->clearOutput();
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

        $git->clearOutput();
        $tags = (string) $git->tag();
        $this->assertTrue(strpos($tags, $tag) !== false);
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

        $git->clearOutput();
        $output = (string) $git->status(array('s' => true));

        $this->assertEquals(" M change.me\n", $output);
    }

    public function testGitPull()
    {
        $git = $this->getWorkingCopy();

        $git->clearOutput();
        $output = (string) $git->pull();

        $this->assertEquals("Already up-to-date.\n", $output);
    }
}
