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
use GitWrapper\Command\GitCommit;
use GitWrapper\Command\GitInit;
use GitWrapper\Command\GitPush;

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
     * @param string $filepattern
     * @param array $options
     *
     * @see GitAdd::__construct()
     */
    public function add($filepattern, array $options = array())
    {
        $add = new GitAdd($this->_directory, $filepattern);
        $add->setOptions($options);
        return $this->_wrapper->run($add);
    }

    /**
     * @param string|null $log_message
     * @param string|null $files
     * @param array $options
     *
     * @see GitCommit::__construct()
     */
    public function commit($log_message = null, $files = null, array $options = array())
    {
        $commit = new GitCommit($this->_directory, $log_message, $files);
        $commit->setOptions($options);
        return $this->_wrapper->run($commit);
    }

    /**
     * @param array $options
     *
     * @see GitInit::__construct()
     */
    public function init(array $options = array())
    {
        $init = new GitInit($this->_directory);
        $init->setOptions($options);
        return $this->_wrapper->run($init);
    }

    /**
     * @param string|null $repository
     *   The "remote" repository that is destination of a push operation.
     * @param string|null $refspec
     * @param array $options
     */
    public function push($repository = null, $refspec = null, array $options = array())
    {
        $push = new GitPush($this->_directory, $repository, $refspec);
        $push->setOptions($options);
        return $this->_wrapper->run($push);
    }
}
