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
        $this->assertEquals($binary, $this->wrapper->getGitBinary());
    }

    public function testSetDispatcher()
    {
        $dispatcher = new TestDispatcher();
        $this->wrapper->setDispatcher($dispatcher);
        $this->assertEquals($dispatcher, $this->wrapper->getDispatcher());
    }

    public function testSetTimeout()
    {
        $timeout = mt_rand(1, 60);
        $this->wrapper->setTimeout($timeout);
        $this->assertEquals($timeout, $this->wrapper->getTimeout());
    }

    public function testEnvVar()
    {
        $var = $this->randomString();
        $value = $this->randomString();

        $this->wrapper->setEnvVar($var, $value);
        $this->assertEquals($value, $this->wrapper->getEnvVar($var));

        $envvars = $this->wrapper->getEnvVars();
        $this->assertEquals($value, $envvars[$var]);

        $this->wrapper->unsetEnvVar($var);
        $this->assertNull($this->wrapper->getEnvVar($var));
    }

    public function testEnvVarDefault()
    {
        $var = $this->randomString();
        $default = $this->randomString();
        $this->assertEquals($default, $this->wrapper->getEnvVar($var, $default));
    }

    public function testProcOptions()
    {
        $value = (bool) mt_rand(0, 1);
        $options = array('suppress_errors' => $value);
        $this->wrapper->setProcOptions($options);
        $this->assertEquals($options, $this->wrapper->getProcOptions());
    }

    public function testGitVersion()
    {
        $version = $this->wrapper->version();
        $this->assertGitVersion($version);
    }

    public function testCopyDefaultSshWrapperTo()
    {
        $env = $this->wrapper->getDefaultEnvVars();
        $result = $this->wrapper->copyDefaultSshWrapperTo('build/test/git-wrapper.sh');
        $this->assertFileEquals($env['GIT_SSH'], 'build/test/git-wrapper.sh');
        $this->assertSame($this->wrapper, $result);
        $this->assertTrue(is_executable('build/test/git-wrapper.sh'), 'Copied GIT wrapper should be executable');
    }

    public function testSetSshPrivateKey()
    {
        $key = './test/id_rsa';
        $keyExpected = realpath($key);

        $result = $this->wrapper->setSshPrivateKey($key);
        $this->assertEquals($keyExpected, $this->wrapper->getEnvVar('GIT_SSH_KEY'));
        $this->assertSame($this->wrapper, $result);
    }

    /**
     * @expectedException \GitWrapper\GitException
     */
    public function testSetSshPrivateKeyError()
    {
        $badKey = './test/id_rsa_bad';
        $this->wrapper->setSshPrivateKey($badKey);
    }

    public function testSetSshPort()
    {
        $port = mt_rand(1024, 10000);
        $this->wrapper->setSshPort($port);
        $this->assertEquals($port, $this->wrapper->getEnvVar('GIT_SSH_PORT'));
    }

    public function testSetSshWrapper()
    {
        $sshWrapper = './test/dummy-wrapper.sh';
        $sshWrapperExpected = realpath($sshWrapper);
        $this->wrapper->setSshWrapper($sshWrapper);
        $this->assertEquals($sshWrapperExpected, $this->wrapper->getEnvVar('GIT_SSH'));
    }

    /**
     * @expectedException \GitWrapper\GitException
     */
    public function testSetSshWrapperError()
    {
        $badWrapper = './test/dummy-wrapper-bad.sh';
        $this->wrapper->setSshWrapper($badWrapper);
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

    /**
     * @expectedException \BadMethodCallException
     */
    public function testCallError()
    {
        $this->wrapper->badMethod();
    }

    public function testWorkingCopy()
    {
        $directory = './' . $this->randomString();
        $git = $this->wrapper->workingCopy($directory);

        $this->assertTrue($git instanceof GitWorkingCopy);
        $this->assertEquals($directory, $git->getDirectory());
        $this->assertEquals($this->wrapper, $git->getWrapper());
    }

    public function testParseRepositoryName()
    {
        $nameGit = GitWrapper::parseRepositoryName('git@github.com:cpliakas/git-wrapper.git');
        $this->assertEquals($nameGit, 'git-wrapper');

        $nameHttps = GitWrapper::parseRepositoryName('https://github.com/cpliakas/git-wrapper.git');
        $this->assertEquals($nameHttps, 'git-wrapper');
    }

    public function testCloneWothoutDirectory()
    {
        $this->addBypassListener();
        $this->wrapper->clone('file:///' . $this->randomString());
    }

    /**
     * Tests deprecated method setPrivateKey(). Should be removed then.
     * @deprecated
     */
    public function testSetPrivateKey()
    {
        $errorReporting = error_reporting(E_ALL & ~E_USER_DEPRECATED);
        $key = './test/id_rsa';
        $keyExpected = realpath($key);

        $this->wrapper->setPrivateKey($key);
        $this->assertEquals($keyExpected, $this->wrapper->getEnvVar('GIT_SSH_KEY'));
        error_reporting($errorReporting);
    }

    /**
     * Tests deprecated method setPrivateKey(). Should be removed then.
     * @deprecated
     */
    public function testSetPrivateKeyPort()
    {
        $errorReporting = error_reporting(E_ALL & ~E_USER_DEPRECATED);
        $port = mt_rand(1024, 10000);
        $this->wrapper->setPrivateKey('./test/id_rsa', $port);
        $this->assertEquals($port, $this->wrapper->getEnvVar('GIT_SSH_PORT'));
        error_reporting($errorReporting);
    }

    /**
     * Tests deprecated method setPrivateKey(). Should be removed then.
     * @deprecated
     */
    public function testSetPrivateKeyWrapper()
    {
        $errorReporting = error_reporting(E_ALL & ~E_USER_DEPRECATED);
        $sshWrapper = './test/dummy-wrapper.sh';
        $sshWrapperExpected = realpath($sshWrapper);
        $this->wrapper->setPrivateKey('./test/id_rsa', 22, $sshWrapper);
        $this->assertEquals($sshWrapperExpected, $this->wrapper->getEnvVar('GIT_SSH'));
        error_reporting($errorReporting);
    }

    /**
     * Tests deprecated method unsetPrivateKey(). Should be removed then.
     * @deprecated
     */
    public function testUnsetPrivateKey()
    {
        $errorReporting = error_reporting(E_ALL & ~E_USER_DEPRECATED);
        // Set and unset the private key.
        $key = './test/id_rsa';
        $sshWrapper = './test/dummy-wrapper.sh';
        $this->wrapper
            ->setSshWrapper($sshWrapper)
            ->setSshPrivateKey($key)
            ->setSshPort(22);
        $this->wrapper->unsetPrivateKey();

        $this->assertNull($this->wrapper->getEnvVar('GIT_SSH_KEY'));
        $this->assertNull($this->wrapper->getEnvVar('GIT_SSH_PORT'));
        $this->assertNull($this->wrapper->getEnvVar('GIT_SSH'));
        error_reporting($errorReporting);
    }
}
