use GitWrapper\GitException;
use GitWrapper\GitWorkingCopy;
        $this->_wrapper->init(self::REPO_DIR, array('bare' => true));
        $git = $this->_wrapper->clone('file://' . realpath(self::REPO_DIR), $directory);
        touch($directory . '/move.me');
        mkdir($directory . '/a.directory', 0755);
        touch($directory . '/a.directory/remove.me');
        // Remove the working copy.
        self::rmdir($directory);
        self::rmdir(self::REPO_DIR);
            self::rmdir(self::WORKING_DIR);
     * @return GitWorkingCopy
        $git = $this->_wrapper->workingCopy($directory);
        $all_branches = 0;
            $all_branches++;
        $this->assertEquals($all_branches, 4);
        $remote_branches = $branches->remote();
        $this->assertEquals(count($remote_branches), 3);
        touch(self::WORKING_DIR . '/add.me');
        $branch_name = $this->randomString();
        $git->branch($branch_name);
        $this->assertTrue(strpos($branches, $branch_name) !== false);
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

        $this->assertEquals("On branch master\nYour branch is up-to-date with 'origin/master'.\n\nnothing to commit, working directory clean\n", $errorOutput);
    }

        $expected_type = Process::OUT;
        $this->assertEquals($expected_type, $event->getType());
        $stdout_suppress = stream_filter_append(STDOUT, 'suppress');
        stream_filter_remove($stdout_suppress);