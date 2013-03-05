<?php

namespace GitWrapper\Test;

use GitWrapper\GitCommand;
use GitWrapper\Test\Event\TestDispatcher;

class GitWrapperTest extends GitWrapperTestCase
{
    public function testSetGitBinary()
    {
        $binary = '/path/to/binary';
        $this->_wrapper->setGitBinary($binary);
        $this->assertEquals($binary, $this->_wrapper->getGitBinary());
    }

    public function testSetDispatcher()
    {
        $dispatcher = new TestDispatcher();
        $this->_wrapper->setDispatcher($dispatcher);
        $this->assertEquals($dispatcher, $this->_wrapper->getDispatcher());
    }

    public function testSetTimeout()
    {
        $timeout = mt_rand(1, 60);
        $this->_wrapper->setTimeout($timeout);
        $this->assertEquals($timeout, $this->_wrapper->getTimeout());
    }

    public function testEnvVar()
    {
        $var = $this->randomString();
        $value = $this->randomString();

        $this->_wrapper->setEnvVar($var, $value);
        $this->assertEquals($value, $this->_wrapper->getEnvVar($var));

        $envvars = $this->_wrapper->getEnvVars();
        $this->assertEquals($value, $envvars[$var]);

        $this->_wrapper->unsetEnvVar($var);
        $this->assertNull($this->_wrapper->getEnvVar($var));
    }

    public function testEnvVarDefault()
    {
        $var = $this->randomString();
        $default = $this->randomString();
        $this->assertEquals($default, $this->_wrapper->getEnvVar($var, $default));
    }

    public function testProcOptions()
    {
        $value = (bool) mt_rand(0, 1);
        $options = array('suppress_errors' => $value);
        $this->_wrapper->setProcOptions($options);
        $this->assertEquals($options, $this->_wrapper->getProcOptions());
    }

    public function testGitVersion()
    {
        $version = $this->_wrapper->version();
        $this->assertGitVersion($version);
    }

    public function testSetPrivateKey()
    {
        $key = './test/id_rsa';
        $key_expected = realpath($key);
        $ssh_wrapper_expected = realpath(__DIR__ . '/../../../bin/git-ssh-wrapper.sh');

        $this->_wrapper->setPrivateKey($key);
        $this->assertEquals($key_expected, $this->_wrapper->getEnvVar('GIT_SSH_KEY'));
        $this->assertEquals(22, $this->_wrapper->getEnvVar('GIT_SSH_PORT'));
        $this->assertEquals($ssh_wrapper_expected, $this->_wrapper->getEnvVar('GIT_SSH'));
    }

    public function testSetPrivateKeyPort()
    {
        $port = mt_rand(1024, 10000);
        $this->_wrapper->setPrivateKey('./test/id_rsa', $port);
        $this->assertEquals($port, $this->_wrapper->getEnvVar('GIT_SSH_PORT'));
    }

    public function testSetPrivateKeyWrapper()
    {
        $ssh_wrapper = './test/dummy-wrapper.sh';
        $ssh_wrapper_expected = realpath($ssh_wrapper);
        $this->_wrapper->setPrivateKey('./test/id_rsa', 22, $ssh_wrapper);
        $this->assertEquals($ssh_wrapper_expected, $this->_wrapper->getEnvVar('GIT_SSH'));
    }

    /**
     * @expectedException \GitWrapper\GitException
     */
    public function testSetPrivateKeyError()
    {
        $bad_key = './test/id_rsa_bad';
        $this->_wrapper->setPrivateKey($bad_key);
    }

    /**
     * @expectedException \GitWrapper\GitException
     */
    public function testSetPrivateKeyWrapperError()
    {
        $bad_wrapper = './test/dummy-wrapper-bad.sh';
        $this->_wrapper->setPrivateKey('./test/id_rsa', 22, $bad_wrapper);
    }

    public function testGitCommand()
    {
        $version = $this->_wrapper->git('--version');
        $this->assertGitVersion($version);
    }

    /**
     * @expectedException \GitWrapper\GitException
     */
    public function testGitCommandError()
    {
        $this->runBadCommand();
    }

    public function testGitRun()
    {
        $command = GitCommand::getInstance();
        $command->setFlag('version');
        $command->setDirectory('./test'); // Directory just has to exist.
        $version = $this->_wrapper->run($command);
        $this->assertGitVersion($version);
    }

    /**
     * @expectedException \GitWrapper\GitException
     */
    public function testGitRunDirectoryError()
    {
        $command = GitCommand::getInstance();
        $command->setFlag('version');
        $command->setDirectory('/some/bad/directory');
        $this->_wrapper->run($command);
    }

    public function testWrapperExecutable()
    {
        $ssh_wrapper = realpath(__DIR__ . '/../../../bin/git-ssh-wrapper.sh');
        $this->assertTrue(is_executable($ssh_wrapper));
    }
}
