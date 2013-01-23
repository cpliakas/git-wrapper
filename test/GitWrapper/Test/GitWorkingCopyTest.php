<?php

namespace GitWrapper\Test;

use GitWrapper\Command\Git;
use GitWrapper\GitWorkingCopy;

class GitWorkingCopyTest extends GitWrapperTestCase
{
    public function getRandomWorkingCopy()
    {
        $directory = './' . $this->randomString();
        return $this->_wrapper->workingCopy($directory);
    }

    public function testWorkingCopy()
    {
        $directory = './' . $this->randomString();
        $git = $this->_wrapper->workingCopy($directory);

        $this->assertTrue($git instanceof GitWorkingCopy);
        $this->assertEquals($directory, $git->getDirectory());
        $this->assertEquals($this->_wrapper, $git->getWrapper());
    }

    public function testGitRun()
    {
        $git = $this->getRandomWorkingCopy();
        $command = new Git();
        $version = $git->run($command, array('version' => true));
        $this->assertGitVersion($version);
    }

    public function testClearOutput()
    {
        $git = $this->getRandomWorkingCopy();
        $command = new Git();
        $git->run($command, array('version' => true));
        $git->clearOutput();
        $this->assertEmpty($git->getOutput());
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testCallError()
    {
        $git = $this->getRandomWorkingCopy();
        $git->badMethod();
    }

}