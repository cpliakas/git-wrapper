<?php

namespace GitWrapper\Test;

use GitWrapper\GitCommand;

class GitCommandTest extends GitWrapperTestCase
{

    const TEST_REPO = 'git://github.com/cpliakas/git-wrapper-test.git';
    const WORKING_DIR = 'test/repo';

    /**
     * @return \GitWrapper\GitWorkingCopy
     */
    public function getWorkingCopy()
    {
        return $this->_wrapper->workingCopy(self::WORKING_DIR);
    }

    public function testCommand()
    {
        $command = $this->randomString();
        $argument = $this->randomString();
        $flag = $this->randomString();
        $option_name = $this->randomString();
        $option_value = $this->randomString();

        $git = GitCommand::getInstance($command)
            ->addArgument($argument)
            ->setFlag($flag)
            ->setOption($option_name, $option_value);

        $expected = "$command --$flag --$option_name='$option_value' '$argument'";
        $command_line = $git->getCommandLine();

        $this->assertEquals($expected, $command_line);
    }

    public function testOption()
    {
        $option_name = $this->randomString();
        $option_value = $this->randomString();

        $git = GitCommand::getInstance()
            ->setOption($option_name, $option_value);

        $this->assertEquals($option_value, $git->getOption($option_name));

        $git->unsetOption($option_name);
        $this->assertNull($git->getOption($option_name));
    }

    public function testEscapeFilepattern()
    {
        $filepattern = 'a.directory/test.txt';
        $expected = 'a.directory/test\\.txt';

        $git = $this->getWorkingCopy();
        $this->assertEquals($expected, $git->escapeFilepattern($filepattern));
    }

    public function testGitInit()
    {
        $directory = self::WORKING_DIR . '-init';
        $this->_wrapper->init($directory);
        $this->assertFileExists($directory . '/.git');
        self::rmdir($directory);
    }

    public function testGitClone()
    {
        $git = $this->getWorkingCopy();
        $git->clone(self::TEST_REPO);
        $this->assertFileExists(self::WORKING_DIR . '/.git');
        $this->assertFalse($git->hasChanges());
    }

    public function testGitCloneWithoutDirectory()
    {
        $git = $this->_wrapper->clone(self::TEST_REPO);
        $this->assertTrue(is_dir('git-wrapper-test'));
        self::rmdir('./git-wrapper-test');
    }

    /**
     * @depends testGitClone
     */
    public function testGitAdd()
    {
        touch(self::WORKING_DIR . '/add.me');

        $git = $this->getWorkingCopy();
        $git->add('add.me');

        $match = (bool) preg_match('@A\\s+add\\.me@s', $git->getStatus());
        $this->assertTrue($match);
    }

    /**
     * @depends testGitClone
     */
    public function testGitRm()
    {
        $git = $this->getWorkingCopy();
        $git->rm('a.directory/remove.me');

        $match = (bool) preg_match('@D\\s+a\\.directory/remove\\.me@s', $git->getStatus());
        $this->assertTrue($match);
    }

    /**
     * @depends testGitAdd
     */
    public function testGitCommit()
    {
        $message = $this->randomString();

        $git = $this->getWorkingCopy();
        $git->config('user.email', 'opensource@chrispliakas.com');
        $git->config('user.name', 'Chris Pliakas');
        $git->commit($message);

        $last_log = $this->_wrapper->git('log -n 1', self::WORKING_DIR);
        $match = (bool) preg_match("@\\s+$message@s", $last_log);
        $this->assertTrue($match);
    }

    /**
     * @depends testGitCommit
     */
    public function testGitPush()
    {
        // Let's not actually push anything.
        $this->addBypassListener();
        $listener = $this->addListener();

        $git = $this->getWorkingCopy();
        $git->push();

        $command = $listener->getEvent()->getCommand();
        $this->assertEquals('push', $command->getCommandLine());
    }

    /**
     * @depends testGitCommit
     */
    public function testGitCommitArgs()
    {
        $commit = GitCommand::getInstance('commit', 'files', array('m' => 'log message'));
        $expected = "commit -m 'log message' 'files'";
        $this->assertEquals($expected, $commit->getCommandLine());
    }

    public static function tearDownAfterClass()
    {
        self::rmdir(self::WORKING_DIR);
    }

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
}
