<?php declare(strict_types=1);

namespace GitWrapper\Test;

use GitWrapper\GitCommand;
use GitWrapper\GitWorkingCopy;
use GitWrapper\GitWrapper;
use GitWrapper\Test\Event\TestDispatcher;

final class GitWrapperTest extends AbstractGitWrapperTestCase
{
    public function testSetGitBinary(): void
    {
        $binary = '/path/to/binary';
        $this->gitWrapper->setGitBinary($binary);
        $this->assertSame($binary, $this->gitWrapper->getGitBinary());
    }

    public function testSetDispatcher(): void
    {
        $dispatcher = new TestDispatcher();
        $this->gitWrapper->setDispatcher($dispatcher);
        $this->assertSame($dispatcher, $this->gitWrapper->getDispatcher());
    }

    public function testSetTimeout(): void
    {
        $timeout = random_int(1, 60);
        $this->gitWrapper->setTimeout($timeout);
        $this->assertSame($timeout, $this->gitWrapper->getTimeout());
    }

    public function testEnvVar(): void
    {
        $var = $this->randomString();
        $value = $this->randomString();

        $this->gitWrapper->setEnvVar($var, $value);
        $this->assertSame($value, $this->gitWrapper->getEnvVar($var));

        $envvars = $this->gitWrapper->getEnvVars();
        $this->assertSame($value, $envvars[$var]);

        $this->gitWrapper->unsetEnvVar($var);
        $this->assertNull($this->gitWrapper->getEnvVar($var));
    }

    public function testEnvVarDefault(): void
    {
        $var = $this->randomString();
        $default = $this->randomString();
        $this->assertSame($default, $this->gitWrapper->getEnvVar($var, $default));
    }

    public function testGitVersion(): void
    {
        $version = $this->gitWrapper->version();
        $this->assertGitVersion($version);
    }

    public function testSetPrivateKey(): void
    {
        $key = './test/id_rsa';
        $keyExpected = realpath($key);
        $sshWrapperExpected = realpath(__DIR__ . '/../../../bin/git-ssh-wrapper.sh');

        $this->gitWrapper->setPrivateKey($key);
        $this->assertSame($keyExpected, $this->gitWrapper->getEnvVar('GIT_SSH_KEY'));
        $this->assertSame(22, $this->gitWrapper->getEnvVar('GIT_SSH_PORT'));
        $this->assertSame($sshWrapperExpected, $this->gitWrapper->getEnvVar('GIT_SSH'));
    }

    public function testSetPrivateKeyPort(): void
    {
        $port = random_int(1024, 10000);
        $this->gitWrapper->setPrivateKey('./test/id_rsa', $port);
        $this->assertSame($port, $this->gitWrapper->getEnvVar('GIT_SSH_PORT'));
    }

    public function testSetPrivateKeyWrapper(): void
    {
        $sshWrapper = './test/dummy-wrapper.sh';
        $sshWrapperExpected = realpath($sshWrapper);
        $this->gitWrapper->setPrivateKey('./test/id_rsa', 22, $sshWrapper);
        $this->assertSame($sshWrapperExpected, $this->gitWrapper->getEnvVar('GIT_SSH'));
    }

    /**
     * @expectedException \GitWrapper\GitException
     */
    public function testSetPrivateKeyError(): void
    {
        $badKey = './test/id_rsa_bad';
        $this->gitWrapper->setPrivateKey($badKey);
    }

    /**
     * @expectedException \GitWrapper\GitException
     */
    public function testSetPrivateKeyWrapperError(): void
    {
        $badWrapper = './test/dummy-wrapper-bad.sh';
        $this->gitWrapper->setPrivateKey('./test/id_rsa', 22, $badWrapper);
    }

    public function testUnsetPrivateKey(): void
    {
        // Set and unset the private key.
        $key = './test/id_rsa';
        $sshWrapper = './test/dummy-wrapper.sh';
        $this->gitWrapper->setPrivateKey($key, 22, $sshWrapper);
        $this->gitWrapper->unsetPrivateKey();

        $this->assertNull($this->gitWrapper->getEnvVar('GIT_SSH_KEY'));
        $this->assertNull($this->gitWrapper->getEnvVar('GIT_SSH_PORT'));
        $this->assertNull($this->gitWrapper->getEnvVar('GIT_SSH'));
    }

    public function testGitCommand(): void
    {
        $version = $this->gitWrapper->git('--version');
        $this->assertGitVersion($version);
    }

    /**
     * @expectedException \GitWrapper\GitException
     */
    public function testGitCommandError(): void
    {
        $this->runBadCommand();
    }

    public function testGitRun(): void
    {
        $command = GitCommand::getInstance();
        $command->setFlag('version');
        $command->setDirectory('./test'); // Directory just has to exist.
        $version = $this->gitWrapper->run($command);
        $this->assertGitVersion($version);
    }

    /**
     * @expectedException \GitWrapper\GitException
     */
    public function testGitRunDirectoryError(): void
    {
        $command = GitCommand::getInstance();
        $command->setFlag('version');
        $command->setDirectory('/some/bad/directory');
        $this->gitWrapper->run($command);
    }

    public function testWrapperExecutable(): void
    {
        $sshWrapper = dirname(dirname(dirname(__DIR__))) . '/bin/git-ssh-wrapper.sh';
        $this->assertTrue(is_executable($sshWrapper));
    }

    public function testWorkingCopy(): void
    {
        $directory = './' . $this->randomString();
        $git = $this->gitWrapper->workingCopy($directory);

        $this->assertInstanceOf(GitWorkingCopy::class, $git);
        $this->assertSame($directory, $git->getDirectory());
        $this->assertSame($this->gitWrapper, $git->getWrapper());
    }

    public function testParseRepositoryName(): void
    {
        $nameGit = GitWrapper::parseRepositoryName('git@github.com:cpliakas/git-wrapper.git');
        $this->assertSame($nameGit, 'git-wrapper');

        $nameHttps = GitWrapper::parseRepositoryName('https://github.com/cpliakas/git-wrapper.git');
        $this->assertSame($nameHttps, 'git-wrapper');
    }

    public function testCloneWothoutDirectory(): void
    {
        $this->addBypassListener();
        $git = $this->gitWrapper->cloneRepository('file:///' . $this->randomString());
        $this->assertTrue($git->isCloned());
    }
}
