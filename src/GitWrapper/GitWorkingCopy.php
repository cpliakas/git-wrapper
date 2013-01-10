<?php

/**
 * A PHP Git wrapper.
 *
 * @license GNU General Public License, version 3
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Copyright (c) 2013 Acquia, Inc.
 */

namespace GitWrapper;

use GitWrapper\Command\GitAdd;
use GitWrapper\Command\GitClone;
use GitWrapper\Command\GitCommandAbstract;
use GitWrapper\Command\GitCommit;
use GitWrapper\Command\GitInit;
use GitWrapper\Command\GitPush;
use GitWrapper\Command\GitRm;

/**
 * Makes it easier to work with working copies.
 */
class GitWorkingCopy
{
    /**
     * @var GitWrapper
     */
    protected $_wrapper;

    /**
     * The directory containing the working copy.
     *
     * @var string
     */
    protected $_directory;

    /**
     * The output captured by the last run commnd(s).
     */
    protected $_output = '';

    /**
     * Constructs a GitWorkingCopy object.
     *
     * @param GitWrapper $wrapper
     * @param string $directory The directory containing the working copy.
     */
    public function __construct(GitWrapper $wrapper, $directory)
    {
        $this->_wrapper = $wrapper;
        $this->_directory = $directory;
    }

    /**
     * @return GitWrapper
     */
    public function getWrapper()
    {
        return $this->_wrapper;
    }

    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->_directory;
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        $output = $this->_output;
        $this->_output = '';
        return $output;
    }

    /**
     * @return GitWorkingCopy
     */
    public function clearOutput()
    {
        $this->_output = '';
        return $this;
    }

    /**
     * @param GitCommandAbstract $command
     * @param array $options
     */
    public function run(GitCommandAbstract $command, array $options)
    {
        $command->setOptions($options);
        $this->_output .= $this->_wrapper->run($command);
        return $this;
    }

    /**
     * @param string $filepattern
     * @param array $options
     * @return GitWorkingCopy
     *
     * @see GitAdd
     */
    public function add($filepattern, array $options = array())
    {
        $add = new GitAdd($this->_directory, $filepattern);
        return $this->run($add, $options);
    }

    /**
     * @param string $repository
     * @param array $options
     * @return GitWorkingCopy
     *
     * @see GitClone
     */
    public function cloneRepository($repository, array $options = array())
    {
        $clone = new GitClone($repository, $this->_directory);
        return $this->run($clone, $options);
    }

    /**
     * @param string|null $log_message
     * @param string|null $files
     * @param array $options
     * @return GitWorkingCopy
     *
     * @see GitCommit
     */
    public function commit($log_message = null, $files = null, array $options = array())
    {
        $commit = new GitCommit($this->_directory, $log_message, $files);
        return $this->run($commit, $options);
    }

    /**
     * @param array $options
     * @return GitWorkingCopy
     *
     * @see GitInit
     */
    public function init(array $options = array())
    {
        $init = new GitInit($this->_directory);
        return $this->run($init, $options);
    }

    /**
     * @param string|null $repository
     *   The "remote" repository that is destination of a push operation.
     * @param string|null $refspec
     * @param array $options
     * @return GitWorkingCopy
     *
     * @see GitPush
     */
    public function push($repository = null, $refspec = null, array $options = array())
    {
        $push = new GitPush($this->_directory, $repository, $refspec);
        return $this->run($push, $options);
    }

    /**
     * @param string $filepattern
     * @param array $options
     * @return GitWorkingCopy
     *
     * @see GitAdd
     */
    public function rm($filepattern, array $options = array())
    {
        $rm = new GitRm($this->_directory, $filepattern);
        return $this->run($rm, $options);
    }

    /**
     * Hackish, allows us to use "clone" as a method name.
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
