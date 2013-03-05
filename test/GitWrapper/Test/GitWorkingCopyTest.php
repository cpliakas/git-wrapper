<?php

namespace GitWrapper\Test;

use GitWrapper\GitCommand;
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

    /**
     * @expectedException \BadMethodCallException
     */
    public function testCallError()
    {
        $git = $this->getRandomWorkingCopy();
        $git->badMethod();
    }

}