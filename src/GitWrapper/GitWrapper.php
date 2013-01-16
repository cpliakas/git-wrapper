<?php

/**
 * A PHP wrapper around the Git command line utility.
 *
 * @mainpage
 *
 * @license GNU General Public License, version 3
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @see https://github.com/cpliakas/git-wrapper
 * @copyright Copyright (c) 2013 Acquia, Inc.
 */

namespace GitWrapper;

use GitWrapper\Command\Git;
use GitWrapper\Command\GitCommandAbstract;
use GitWrapper\Event\GitEvent;
use GitWrapper\Event\GitEvents;
use GitWrapper\Exception\GitException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * A wrapper class around the Git binary.
 *
 * A GitWrapper object contains the necessary context to run Git commands such
 * as the path to the Git binary and environment variables. It also provides
 * helper methods to run Git commands as set up the connection to the GIT_SSH
 * wrapper script.
 */
class GitWrapper
{
    /**
     * Symfony event dispatcher object used by this library to dispatch events.
     *
     * @var EventDispatcher
     */
    protected $_dispatcher;

    /**
     * Path to the Git binary.
     *
     * @var string
     */
    protected $_gitBinary;

    /**
     * Environment variables defined in the scope of the Git command.
     *
     * @var array
     */
    protected $_env = array();

    /**
     * Constructs a GitWrapper object.
     *
     * @param string|null $git_binary
     *   The path to the Git binary. Defaults to null, which uses Symfony's
     *   ExecutableFinder to resolve it automatically.
     *
     * @throws GitWrapper::Exception::GitException
     *   Throws an exception if the path to the Git binary couldn't be resolved
     *   by the ExecutableFinder class.
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
     * Gets the dispatcher used by this library to dispatch events.
     *
     * @return EventDispatcher
     */
    public function getDispatcher()
    {
        return $this->_dispatcher;
    }

    /**
     * Sets the dispatcher used by this library to dispatch events.
     *
     * @param EventDispatcher $dispatcher
     *   The Symfony event dispatcher object.
     *
     * @return GitWrapper
     */
    public function setDispatcher(EventDispatcher $dispatcher)
    {
        $this->_dispatcher = $dispatcher;
        return $this;
    }

    /**
     * Sets the path to the Git binary.
     *
     * @param string $git_binary
     *   Path to the Git binary.
     *
     * @return GitWrapper
     */
    public function setGitBinary($git_binary)
    {
        $this->_gitBinary = escapeshellcmd($git_binary);
        return $this;
    }

    /**
     * Returns the path to the Git binary.
     *
     * @return string
     */
    public function getGitBinary()
    {
        return $this->_gitBinary;
    }

    /**
     * Sets an environment variable that is defined only in the scope of the Git
     * command.
     *
     * @param string $var
     *   The name of the environment variable, e.g. "HOME", "GIT_SSH".
     * @param mixed $default
     *   The value of the environment variable is not set, defaults to null.
     *
     * @return GitWrapper
     */
    public function setEnvVar($var, $value)
    {
        $this->_env[$var] = $value;
        return $this;
    }

    /**
     * Unsets an environment variable that is defined only in the scope of the
     * Git command.
     *
     * @param string $var
     *   The name of the environment variable, e.g. "HOME", "GIT_SSH".
     *
     * @return GitWrapper
     */
    public function unsetEnvVar($var)
    {
        unset($this->_env[$var]);
        return $this;
    }

    /**
     * Returns an environment variable that is defined only in the scope of the
     * Git command.
     *
     * @param string $var
     *   The name of the environment variable, e.g. "HOME", "GIT_SSH".
     * @param mixed $default
     *   The value returned if the environment variable is not set, defaults to
     *   null.
     *
     * @return mixed
     */
    public function getEnvVar($var, $default = null)
    {
        return isset($this->_env[$var]) ? $this->_env[$var] : $default;
    }

    /**
     * Set an alternate private key used to connect to the repository.
     *
     * This method sets the GIT_SSH environment variable to use the wrapper
     * script included with this library. It also sets the custom GIT_SSH_KEY
     * and GIT_SSH_PORT environment variables that are used by the script.
     *
     * @param string $private_key
     *   Path to the private key.
     * @param int $port
     *   Port that the SSH server being connected to listens on, defaults to 22.
     * @param string|null $wrapper
     *   Path the the GIT_SSH wrapper script, defaults to null which uses the
     *   script included with this library.
     *
     * @return GitWrapper
     */
    public function setPrivateKey($private_key, $port = 22, $wrapper = null)
    {
        if (null === $wrapper) {
            $wrapper = realpath(__DIR__ . '/../../bin/git-ssh-wrapper.sh');
        }

        $this
            ->setEnvVar('GIT_SSH', $wrapper)
            ->setEnvVar('GIT_SSH_KEY', realpath($private_key))
            ->setEnvVar('GIT_SSH_PORT', $port);

        return $this;
    }

    /**
     * Returns an object that interacts with a working copy.
     *
     * @param string $directory
     *   Path to the directory containing the working copy.
     *
     * @return GitWorkingCopy
     */
    public function workingCopy($directory)
    {
        return new GitWorkingCopy($this, $directory);
    }

    /**
     * Runs a Git command using a single flag.
     *
     * @param string $flag
     *   The flag to pass as an option. Do not precede with "--".
     *
     * @return string
     *   The STDOUT returned by the Git command.
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
     * @throws GitWrapper::Exception::GitException
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
     * @throws GitWrapper::Exception::GitException
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
     * @throws GitWrapper::Exception::GitException
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
     * @throws GitWrapper::Exception::GitException
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
     * @throws GitWrapper::Exception::GitException
     */
    public function infoPath()
    {
        return $this->runGit('info-path');
    }

    /**
     * Runs an arbitrary Git command.
     *
     * The command is simply a raw command line entry for everything after the
     * Git binary. For example, a `git config -l` command would be passed as
     * `congig -l` via this method.
     *
     * Note that no events are thrown by this method.
     *
     * @param string $command
     *   The raw command containing the Git optios and arguments. The Git
     *   binary should be omitted.
     * @param string|null $cwd
     *   The current working directory the Git process will run under, defaults
     *   to null which inherits the working directory of the PHP process.
     * @param array|null $env
     *   An associative array of environment variables set for the Git process.
     *   Defaults to null which inherits the evironment variables from the PHP
     *   process.
     *
     * @return string
     *   The STDOUT returned by the Git command.
     *
     * @throws GitWrapper::Exception::GitException
     *
     * @see Process
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
     *   The Git command being executed.
     *
     * @return string
     *   The STDOUT returned by the Git command.
     *
     * @throws GitWrapper::Exception::GitException
     *
     * @see Process
     */
    public function run(GitCommandAbstract $command)
    {
        try {
            // Build the command, set the environment variables and working dir.
            $command_line = rtrim($this->_gitBinary . ' ' . $command->getCommandLine());
            if (null !== $cwd = $command->getDirectory()) {
              $cwd = realpath($cwd);
            }
            $env = ($this->_env) ? $this->_env : null;
            $process = new Process($command_line, $cwd, $env);

            // Dispatch the GitEvents::GIT_COMMAND event.
            $event = new GitEvent($this, $process, $command);
            $this->_dispatcher->dispatch(GitEvents::GIT_COMMAND, $event);

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
