<?php

/**
 * A PHP Git wrapper.
 *
 * @license GNU General Public License, version 3
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Copyright (c) 2013 Acquia, Inc.
 */

namespace GitWrapper;

use GitWrapper\Command\Git;
use GitWrapper\Command\GitCommandAbstract;
use GitWrapper\Event\GitEvent;
use GitWrapper\Exception\GitException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Base class for executing Git commands.
 */
class GitWrapper
{
    /**
     * @var EventDispatcher
     */
    protected $_dispatcher;

    /**
     * @var string
     */
    protected $_gitBinary;

    /**
     * @var array
     */
    protected $_env = array();

    /**
     * Constructs a Git object.
     *
     * @param string|null $git_binary
     *   The path to the Git binary. Defaults to null, which uses Symfony's
     *   ExecutableFinder class to get it.
     *
     * @throws GitException
     */
    public function __construct($git_binary = null)
    {
        $this->_dispatcher = new EventDispatcher();

        if (null === $git_binary) {
            $finder = new ExecutableFinder();
            $git_binary = $finder->find('git');
            if (!$git_binary) {
                throw new GitException('Unable to find the Git executable.');
            }
        }

        $this->setGitBinary($git_binary);
    }

    /**
     * @return EventDispatcher
     */
    public function getDispatcher()
    {
        return $this->_dispatcher;
    }

    /**
     * @param EventDispatcher $dispatcher
     * @return GitWrapper
     */
    public function setDispatcher(EventDispatcher $dispatcher)
    {
        $this->_dispatcher = $dispatcher;
        return $this;
    }

    /**
     * @param string $git_binary
     * @return GitWrapper
     */
    public function setGitBinary($git_binary)
    {
        $this->_gitBinary = escapeshellcmd($git_binary);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGitBinary()
    {
        return $this->_gitBinary;
    }

    /**
     * Sets an environment variable.
     *
     * @param string $var
     * @param mixed $value
     * @return GitWrapper
     */
    public function setEnvVar($var, $value)
    {
        $this->_env[$var] = $value;
        return $this;
    }

    /**
     * Unsets an environment variable.
     *
     * @param string $var
     * @return GitWrapper
     */
    public function unsetEnvVar($var)
    {
        unset($this->_env[$var]);
        return $this;
    }

    /**
     * Sets an environment variable.
     *
     * @param string $var
     * @param mixed $default
     * @return mixed
     */
    public function getEnvVar($var, $default = null)
    {
        return isset($this->_env[$var]) ? $this->_env[$var] : $default;
    }


    /**
     * Set an alternate private key used to connect to the repository.
     *
     * @param string $private_key path to the private key.
     * @param int $port The SSH port
     * @return GitWrapper
     */
    public function setPrivateKey($private_key, $port = '22', $wrapper = null)
    {
        if (null === $wrapper) {
            $wrapper = realpath(__DIR__ . '/../../bin/git-ssh-wrapper.sh');
        }

        $this
            ->setEnvVar('GIT_SSH', $wrapper)
            ->setEnvVar('GIT_SSH_KEY', $private_key)
            ->setEnvVar('GIT_SSH_PORT', $port);

        return $this;
    }

    /**
     * Returns a working copy object.
     *
     * @param string $directory
     * @return GitWorkingCopy
     */
    public function workingCopy($directory)
    {
        return new GitWorkingCopy($this, $directory);
    }

    /**
     * Runs the git binary using a single flag.
     *
     * @param string $flag
     *
     * @return string
     */
    public function runGit($flag)
    {
        $git = new Git();
        $git->setFlag('version');
        return $this->run($git);
    }

    /**
     * Returns the version if the installed Git client.
     *
     * @return string
     *
     * @throws GitException
     */
    public function version()
    {
        return $this->runGit('version');
    }

    /**
     * Returns the exec path.
     *
     * @return string
     *
     * @throws GitException
     */
    public function execPath()
    {
        return $this->runGit('exec-path');
    }

    /**
     * Returns the html path.
     *
     * @return string
     *
     * @throws GitException
     */
    public function htmlPath()
    {
        return $this->runGit('html-path');
    }

    /**
     * Returns the man path.
     *
     * @return string
     *
     * @throws GitException
     */
    public function manPath()
    {
        return $this->runGit('man-path');
    }

    /**
     * Returns the info path.
     *
     * @return string
     *
     * @throws GitException
     */
    public function infoPath()
    {
        return $this->runGit('info-path');
    }

    /**
     * Runs an arbitrary git command.
     *
     * The command is simply a raw command line entry for everything after the
     * Git binary. For example, a `git config -l` command would be passed as
     * `congig -l` via this method.
     *
     * Note that no events are thrown by this method.
     *
     * @param string $command
     * @param string|null $cwd
     * @param array|null $env
     * @return string
     *
     * @throws GitException
     */
    public function git($command, $cwd = null, $env = null)
    {
        try {
            $commandline = $this->_gitBinary . ' ' . $command;
            $process = new Process($commandline, $cwd, $env);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new \RuntimeException($process->getErrorOutput());
            }
        } catch (\RuntimeException $e) {
            throw new GitException($e->getMessage());
        }
        return $process->getOutput();
    }

    /**
     * Runs a Git command.
     *
     * @param GitCommandAbstract $command
     * @return string
     *
     * @throws GitException
     */
    public function run(GitCommandAbstract $command)
    {
        try {
            $command_line = rtrim($this->_gitBinary . ' ' . $command->getCommandLine());
            if (null !== $cwd = $command->getDirectory()) {
              $cwd = realpath($cwd);
            }
            $env = ($this->_env) ? $this->_env : null;
            $process = new Process($command_line, $cwd, $env);

            $event_name = $command->getEventName();
            $event = new GitEvent($this, $process);
            $this->_dispatcher->dispatch($event_name, $event);

            $process->run();
            if (!$process->isSuccessful()) {
                throw new \RuntimeException($process->getErrorOutput());
            }

        } catch (\RuntimeException $e) {
            throw new GitException($e->getMessage());
        }

        return $process->getOutput();
    }
}
