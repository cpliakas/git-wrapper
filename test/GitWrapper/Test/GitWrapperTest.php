<?php

namespace GitWrapper\Test;

use GitWrapper\GitCommand;
use GitWrapper\GitWorkingCopy;
use GitWrapper\GitWrapper;
use GitWrapper\Test\Event\TestDispatcher;

class GitWrapperTest extends GitWrapperTestCase
{
    public function testSetGitBinary()
    {
        $binary = '/path/to/binary';
        $this->wrapper->setGitBinary($binary);
        $this->assertSame($binary, $this->wrapper->getGitBinary());
    }

    public function testSetDispatcher()
    {
        $dispatcher = new TestDispatcher();
        $this->wrapper->setDispatcher($dispatcher);
        $this->assertSame($dispatcher, $this->wrapper->getDispatcher());
    }

    public function testSetTimeout()
    {
        $timeout = mt_rand(1, 60);
        $this->wrapper->setTimeout($timeout);
        $this->assertSame($timeout, $this->wrapper->getTimeout());
    }

    public function testEnvVar()
    {
        $var = $this->randomString();
        $value = $this->randomString();

        $this->wrapper->setEnvVar($var, $value);
        $this->assertSame($value, $this->wrapper->getEnvVar($var));

        $envvars = $this->wrapper->getEnvVars();
        $this->assertSame($value, $envvars[$var]);

        $this->wrapper->unsetEnvVar($var);
        $this->assertNull($this->wrapper->getEnvVar($var));
    }

    public function testEnvVarDefault()
    {
        $var = $this->randomString();
        $default = $this->randomString();
        $this->assertSame($default, $this->wrapper->getEnvVar($var, $default));
    }

    public function testGitVersion()
    {
        $version = $this->wrapper->version();
        $this->assertGitVersion($version);
    }

    public function testSetPrivateKey()
    {
        $key = './test/id_rsa';
        $keyExpected = realpath($key);
        $sshWrapperExpected = realpath(__DIR__ . '/../../../bin/git-ssh-wrapper.sh');

        $this->wrapper->setPrivateKey($key);
        $this->assertSame($keyExpected, $this->wrapper->getEnvVar('GIT_SSH_KEY'));
        $this->assertSame(22, $this->wrapper->getEnvVar('GIT_SSH_PORT'));
        $this->assertSame($sshWrapperExpected, $this->wrapper->getEnvVar('GIT_SSH'));
    }

    public function testSetPrivateKeyPort()
    {
        $port = mt_rand(1024, 10000);
        $this->wrapper->setPrivateKey('./test/id_rsa', $port);
        $this->assertSame($port, $this->wrapper->getEnvVar('GIT_SSH_PORT'));
    }

    public function testSetPrivateKeyWrapper()
    {
        $sshWrapper = './test/dummy-wrapper.sh';
        $sshWrapperExpected = realpath($sshWrapper);
        $this->wrapper->setPrivateKey('./test/id_rsa', 22, $sshWrapper);
        $this->assertSame($sshWrapperExpected, $this->wrapper->getEnvVar('GIT_SSH'));
    }

    /**
     * @expectedException \GitWrapper\GitException
     */
    public function testSetPrivateKeyError()
    {
        $badKey = './test/id_rsa_bad';
        $this->wrapper->setPrivateKey($badKey);
    }

    /**
     * @expectedException \GitWrapper\GitException
     */
    public function testSetPrivateKeyWrapperError()
    {
        $badWrapper = './test/dummy-wrapper-bad.sh';
        $this->wrapper->setPrivateKey('./test/id_rsa', 22, $badWrapper);
    }

    public function testUnsetPrivateKey()
    {
        // Set and unset the private key.
        $key = './test/id_rsa';
        $sshWrapper = './test/dummy-wrapper.sh';
        $this->wrapper->setPrivateKey($key, 22, $sshWrapper);
        $this->wrapper->unsetPrivateKey();

        $this->assertNull($this->wrapper->getEnvVar('GIT_SSH_KEY'));
        $this->assertNull($this->wrapper->getEnvVar('GIT_SSH_PORT'));
        $this->assertNull($this->wrapper->getEnvVar('GIT_SSH'));
    }

    public function testGitCommand()
    {
        $version = $this->wrapper->git('--version');
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
        $version = $this->wrapper->run($command);
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
        $this->wrapper->run($command);
    }

    public function testWrapperExecutable()
    {
        $sshWrapper = realpath(__DIR__ . '/../../../bin/git-ssh-wrapper.sh');
        $this->assertTrue(is_executable($sshWrapper));
    }

    public function testWorkingCopy()
    {
        $directory = './' . $this->randomString();
        $git = $this->wrapper->workingCopy($directory);

        $this->assertTrue($git instanceof GitWorkingCopy);
        $this->assertSame($directory, $git->getDirectory());
        $this->assertSame($this->wrapper, $git->getWrapper());
    }

    public function testParseRepositoryName()
    {
        $nameGit = GitWrapper::parseRepositoryName('git@github.com:cpliakas/git-wrapper.git');
        $this->assertSame($nameGit, 'git-wrapper');

        $nameHttps = GitWrapper::parseRepositoryName('https://github.com/cpliakas/git-wrapper.git');
        $this->assertSame($nameHttps, 'git-wrapper');
    }

    public function testCloneWothoutDirectory()
    {
        $this->addBypassListener();
        $git = $this->wrapper->cloneRepository('file:///' . $this->randomString());
        $this->assertTrue($git->isCloned());
    }
}
