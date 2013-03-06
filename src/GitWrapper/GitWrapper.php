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

use GitWrapper\Event\GitEvent;
use GitWrapper\Event\GitEvents;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var EventDispatcherInterface
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
     * The timeout of the Git command in seconds, defaults to 60.
     *
     * @var int
     */
    protected $_timeout = 60;

    /**
     * An array of options passed to the proc_open() function.
     *
     * @var array
     */
    protected $_procOptions = array();

    /**
     * Constructs a GitWrapper object.
     *
     * @param string|null $git_binary
     *   The path to the Git binary. Defaults to null, which uses Symfony's
     *   ExecutableFinder to resolve it automatically.
     *
     * @throws GitException
     *   Throws an exception if the path to the Git binary couldn't be resolved
     *   by the ExecutableFinder class.
     */
    public function __construct($git_binary = null)
    {
        $this->_dispatcher = new EventDispatcher();

        if (null === $git_binary) {
            // @codeCoverageIgnoreStart
            $finder = new ExecutableFinder();
            $git_binary = $finder->find('git');
            if (!$git_binary) {
                throw new GitException('Unable to find the Git executable.');
            }
            // @codeCoverageIgnoreEnd
        }

        $this->setGitBinary($git_binary);
    }

    /**
     * Gets the dispatcher used by this library to dispatch events.
     *
     * @return EventDispatcherInterface
     */
    public function getDispatcher()
    {
        return $this->_dispatcher;
    }

    /**
     * Sets the dispatcher used by this library to dispatch events.
     *
     * @param EventDispatcherInterface $dispatcher
     *   The Symfony event dispatcher object.
     *
     * @return GitWrapper
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher)
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
     * Returns the associative array of environment variables that are defined
     * only in the scope of the Git command.
     *
     * @return array
     */
    public function getEnvVars()
    {
        return $this->_env;
    }

    /**
     * Sets the timeout of the Git command.
     *
     * @param int $timeout
     *   The timeout in seconds.
     *
     * @return GitWrapper
     */
    public function setTimeout($timeout)
    {
        $this->_timeout = (int) $timeout;
        return $this;
    }

    /**
     * Gets the timeout of the Git command.
     *
     * @return int
     *   The timeout in seconds.
     */
    public function getTimeout()
    {
        return $this->_timeout;
    }

    /**
     * Sets the options passed to proc_open() when executing the Git command.
     *
     * @param array $timeout
     *   The options passed to proc_open().
     *
     * @return GitWrapper
     */
    public function setProcOptions(array $options)
    {
        $this->_procOptions = $options;
        return $this;
    }

    /**
     * Gets the options passed to proc_open() when executing the Git command.
     *
     * @return array
     */
    public function getProcOptions()
    {
        return $this->_procOptions;
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
     *
     * @throws GitException
     *   Thrown when any of the paths cannot be resolved.
     */
    public function setPrivateKey($private_key, $port = 22, $wrapper = null)
    {
        if (null === $wrapper) {
            $wrapper = __DIR__ . '/../../bin/git-ssh-wrapper.sh';
        }
        if (!$wrapper_path = realpath($wrapper)) {
            throw new GitException('Path to GIT_SSH wrapper script could not be resolved: ' . $wrapper);
        }
        if (!$private_key_path = realpath($private_key)) {
            throw new GitException('Path private key could not be resolved: ' . $private_key);
        }

        return $this
            ->setEnvVar('GIT_SSH', $wrapper_path)
            ->setEnvVar('GIT_SSH_KEY', $private_key_path)
            ->setEnvVar('GIT_SSH_PORT', (int) $port);
    }

    /**
     * Unsets the private key by removing the appropriate environment variables.
     *
     * @return GitWrapper
     */
    public function unsetPrivateKey()
    {
        return $this
            ->unsetEnvVar('GIT_SSH')
            ->unsetEnvVar('GIT_SSH_KEY')
            ->unsetEnvVar('GIT_SSH_PORT');
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
     * Returns the version of the installed Git client.
     *
     * @return string
     *
     * @throws GitException
     */
    public function version()
    {
        return $this->git('--version');
    }

    /**
     * Parses name of the repository from the path.
     *
     * For example, passing the "git@github.com:cpliakas/git-wrapper.git"
     * repository would return "git-wrapper".
     *
     * @param string $repository
     *   The repository URL.
     *
     * @return string
     */
    public static function parseRepositoryName($repository)
    {
        $scheme = parse_url($repository, PHP_URL_SCHEME);

        if (null === $scheme) {
            $parts = explode('/', $repository);
            $path = end($parts);
        } else {
            $strpos = strpos($repository, ':');
            $path = substr($repository, $strpos + 1);
        }

        return basename($path, '.git');
    }

    /**
     * Executes a `git init` command.
     *
     * Create an empty git repository or reinitialize an existing one.
     *
     * @param string $directory
     *   The directory being initialized.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     *
     * @see GitWorkingCopy::cloneRepository()
     *
     * @ingroup commands
     */
    public function init($directory, array $options = array())
    {
        $git = $this->workingCopy($directory);
        $git->init($options);
        $git->setCloned(true);
        return $git;
    }

    /**
     * Executes a `git clone` command and returns a working copy object.
     *
     * Clone a repository into a new directory. Use GitWorkingCopy::clone()
     * instead for more readable code.
     *
     * @param string $repository
     *   The Git URL of the repository being cloned.
     * @param string $directory
     *   The directory that the repository will be cloned into. If null is
     *   passed, the directory will automatically be generated from the URL via
     *   the GitWrapper::parseRepositoryName() method.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     *
     * @see GitWorkingCopy::cloneRepository()
     *
     * @ingroup commands
     */
    public function cloneRepository($repository, $directory = null, array $options = array())
    {
        if (null === $directory) {
            $directory = self::parseRepositoryName($repository);
        }
        $git = $this->workingCopy($directory);
        $git->clone($repository, $options);
        $git->setCloned(true);
        return $git;
    }

    /**
     * Runs an arbitrary Git command.
     *
     * The command is simply a raw command line entry for everything after the
     * Git binary. For example, a `git config -l` command would be passed as
     * `config -l` via the first argument of this method.
     *
     * Note that no events are thrown by this method.
     *
     * @param string $command_line
     *   The raw command containing the Git options and arguments. The Git
     *   binary should not be in the command, for example `git config -l` would
     *   translate to "config -l".
     * @param string|null $cwd
     *   The working directory of the Git process. Defaults to null which uses
     *   the current working directory of the PHP process.
     *
     * @return string
     *   The STDOUT returned by the Git command.
     *
     * @throws GitException
     *
     * @see GitWrapper::run()
     */
    public function git($command_line, $cwd = null)
    {
        $command = GitCommand::getInstance($command_line);
        $command->setDirectory($cwd);
        return $this->run($command);
    }

    /**
     * Runs a Git command.
     *
     * @param GitCommand $command
     *   The Git command being executed.
     * @param string|null $cwd
     *   Explicitly specify the working directory of the Git process. Defaults
     *   to null which automatically sets the working directory based on the
     *   command being executed relative to the working copy.
     *
     * @return string
     *   The STDOUT returned by the Git command.
     *
     * @throws GitException
     *
     * @see Process
     */
    public function run(GitCommand $command, $cwd = null)
    {
        $event = null;

        try {

            // Build the command line options, flags, and arguments.
            $command_line = rtrim($this->_gitBinary . ' ' . $command->getCommandLine());

            // Resolve the working directory of the Git process. Use the
            // directory in the command object if it exists.
            if (null === $cwd) {
                if (null !== $directory = $command->getDirectory()) {
                    if (!$cwd = realpath($directory)) {
                        throw new GitException('Path to working directory could not be resolved: ' . $directory);
                    }
                }
            }

            // Finalize the environment variables, an empty array is converted
            // to null which enherits the environment of the PHP process.
            $env = ($this->_env) ? $this->_env : null;

            $process = new Process($command_line, $cwd, $env, null, $this->_timeout, $this->_procOptions);
            $event = new GitEvent($this, $process, $command);

            // Throw the "git.command.prepare" event prior to executing.
            $this->_dispatcher->dispatch(GitEvents::GIT_PREPARE, $event);

            // Execute command if it is not flagged to be bypassed and throw the
            // "git.command.success" event, otherwise do not execute the comamnd
            // and throw the "git.command.bypass" event.
            if ($command->notBypassed()) {
                $process->run();
                if ($process->isSuccessful()) {
                    $this->_dispatcher->dispatch(GitEvents::GIT_SUCCESS, $event);
                } else {
                    throw new \RuntimeException($process->getErrorOutput());
                }
            } else {
                $this->_dispatcher->dispatch(GitEvents::GIT_BYPASS, $event);
            }

        } catch (\RuntimeException $e) {
            if ($event !== null) {
                // Throw the "git.command.error" event.
                $this->_dispatcher->dispatch(GitEvents::GIT_ERROR, $event);
            }
            throw new GitException($e->getMessage());
        }

        return $process->getOutput();
    }

    /**
     * Hackish, allows us to use "clone" as a method name.
     *
     * $throws \BadMethodCallException
     * @throws GitException
     */
    public function __call($method, $args)
    {
        if ('clone' == $method) {
            return call_user_func_array(array($this, 'cloneRepository'), $args);
        } else {
            $class = get_called_class();
            $message = "Call to undefined method $class::$method()";
            throw new \BadMethodCallException($message);
        }
    }
}
