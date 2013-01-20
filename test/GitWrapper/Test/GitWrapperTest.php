<?php

namespace GitWrapper\Test;

use GitWrapper\GitWrapper;
use GitWrapper\Test\Event\TestDispatcher;

class GitWrapperTest extends \PHPUnit_Framework_TestCase
{

    public function testSetGitBinary()
    {
        $wrapper = new GitWrapper();
        $binary = '/path/to/binary';
        $wrapper->setGitBinary($binary);
        $this->assertEquals($binary, $wrapper->getGitBinary());
    }

    public function testSetDispatcher()
    {
        $wrapper = new GitWrapper();
        $dispatcher = new TestDispatcher();
        $wrapper->setDispatcher($dispatcher);
        $this->assertEquals($dispatcher, $wrapper->getDispatcher());
    }

    public function testSetTimeout()
    {
        $wrapper = new GitWrapper();
        $timeout = mt_rand(1, 60);
        $wrapper->setTimeout($timeout);
        $this->assertEquals($timeout, $wrapper->getTimeout());
    }

    public function testGitCommand()
    {
        $wrapper = new GitWrapper();
        $version = $wrapper->git('--version');
        $this->assertNotEmpty($version);
    }
}