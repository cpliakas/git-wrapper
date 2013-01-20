<?php

/**
 * A PHP wrapper around the Git command line utility.
 *
 * @license GNU General Public License, version 3
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @see https://github.com/cpliakas/git-wrapper
 * @copyright Copyright (c) 2013 Acquia, Inc.
 */

namespace GitWrapper;

use GitWrapper\Command\GitAdd;
use GitWrapper\Command\GitClone;
use GitWrapper\Command\GitConfig;
use GitWrapper\Command\GitCommandAbstract;
use GitWrapper\Command\GitCommit;
use GitWrapper\Command\GitInit;
use GitWrapper\Command\GitPush;
use GitWrapper\Command\GitRm;
use GitWrapper\Command\GitStatus;
use GitWrapper\Command\GitTag;

/**
 * Interacts with a working copy.
 *
 * All commands executed via an instance of this class act on the working copy
 * that is set through the constructor.
 */
class GitWorkingCopy
{
    /**
     * The GitWrapper object that likely instantiated this class.
     *
     * @var GitWrapper
     */
    protected $_wrapper;

    /**
     * Path to the directory containing the working copy.
     *
     * @var string
     */
    protected $_directory;

    /**
     * The output captured by the last run Git commnd(s).
     *
     * @var string
     */
    protected $_output = '';

    /**
     * Constructs a GitWorkingCopy object.
     *
     * @param GitWrapper $wrapper
     *   The GitWrapper object that likely instantiated this class.
     * @param string $directory
     *   Path to the directory containing the working copy.
     */
    public function __construct(GitWrapper $wrapper, $directory)
    {
        $this->_wrapper = $wrapper;
        $this->_directory = $directory;
    }

    /**
     * Returns the GitWrapper object that likely instantiated this class.
     *
     * @return GitWrapper
     */
    public function getWrapper()
    {
        return $this->_wrapper;
    }

    /**
     * Gets the path to the directory containing the working copy.
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->_directory;
    }

    /**
     * Gets the output captured by the last run Git commnd(s).
     *
     * @return string
     */
    public function getOutput()
    {
        $output = $this->_output;
        $this->_output = '';
        return $output;
    }

    /**
     * Clears the stored output captured by the last run Git command(s).
     *
     * @return GitWorkingCopy
     */
    public function clearOutput()
    {
        $this->_output = '';
        return $this;
    }

    /**
     * Returns true if there are changes to commit.
     *
     * @return bool
     *
     * @throws GitWrapper::Exception::GitException
     */
    public function hasChanges()
    {
        $output = $this->_wrapper->git('status -s', $this->_directory);
        return !empty($output);
    }

    /**
     * Runs a Git command and captures the output.
     *
     * @param GitCommandAbstract $command
     *   The Git command being executed.
     * @param array $options
     *   An associative array of command line options and flags.
     *
     * @return GitWorkingCopy
     *
     * @throws GitWrapper::Exception::GitException
     *
     * @see GitWrapper::run()
     */
    public function run(GitCommandAbstract $command, array $options)
    {
        $command->setOptions($options);
        $this->_output .= $this->_wrapper->run($command);
        return $this;
    }

    /**
     * @defgroup commands Git Commands
     *
     * All methods in this group correspond with Git commands, got example
     * "git add", "git commit", "git push", etc.
     *
     * @{
     */

    /**
     * Executes a `git add` command.
     *
     * Adds file contents to the index.
     *
     * @param string $filepattern
     *   Files to add content from. Fileglobs (e.g.  *.c) can be given to add
     *   all matching files. Also a leading directory name (e.g.  dir to add
     *   dir/file1 and dir/file2) can be given to add all files in the
     *   directory, recursively.
     * @param array $options
     *   Associative array of command line options and flags.
     *
     * @return GitWorkingCopy
     *
     * @throws GitWrapper::Exception::GitException
     *
     * @see GitWrapper::GitCommand::GitAdd
     */
    public function add($filepattern, array $options = array())
    {
        $add = new GitAdd($this->_directory, $filepattern);
        return $this->run($add, $options);
    }

    /**
     * Executes a `git clone` command.
     *
     * Clones a repository into the directory passed as the working copy to
     * GitWorkingCopy::__construct().
     *
     * Use GitWorkingCopy::clone() instead for more readable code.
     *
     * @param string $repository
     *   The URL of the Git repository.
     * @param array $options
     *   Associative array of command line options and flags.
     *
     * @return GitWorkingCopy
     *
     * @throws GitWrapper::Exception::GitException
     *
     * @see GitWrapper::GitCommand::GitClone
     */
    public function cloneRepository($repository, array $options = array())
    {
        $clone = new GitClone($repository, $this->_directory);
        return $this->run($clone, $options);
    }

    /**
     * Executes a `git commit` command.
     *
     * Records changes to the repository.
     *
     * @param string|null $log_message
     *   An optional log message passed as the "-m" option.
     * @param string|null $files
     *   The contents of these files will be committed without recording the
     *   changes already staged. Defaults to null which passes the "-a" flag.
     * @param array $options
     *   Associative array of command line options and flags.
     *
     * @return GitWorkingCopy
     *
     * @throws GitWrapper::Exception::GitException
     *
     * @see GitWrapper::GitCommand::GitCommit
     */
    public function commit($log_message = null, $files = null, array $options = array())
    {
        $commit = new GitCommit($this->_directory, $log_message, $files);
        return $this->run($commit, $options);
    }

    /**
     * Executes a `git config` command.
     *
     * Gets and sets repository or global options.
     *
     * @param string $option
     *   The configuration options being set.
     * @param string $value
     *   The value of the configuration option being set.
     * @param array $options
     *   Associative array of command line options and flags.
     *
     * @return GitWorkingCopy
     *
     * @throws GitWrapper::Exception::GitException
     *
     * @see GitWrapper::GitCommand::GitConfig
     */
    public function config($option = null, $value = null, array $options = array())
    {
        $config = new GitConfig($option, $value);
        return $this->run($config, $options);
    }

    /**
     * Executes a `git init` command.
     *
     * Creates an empty git repository or reinitialize an existing one.
     *
     * @param array $options
     *   Associative array of command line options and flags.
     *
     * @return GitWorkingCopy
     *
     * @throws GitWrapper::Exception::GitException
     *
     * @see GitWrapper::GitCommand::GitInit
     */
    public function init(array $options = array())
    {
        $init = new GitInit($this->_directory);
        return $this->run($init, $options);
    }

    /**
     * Executes a `git push` command.
     *
     * Updates remote refs along with associated objects.
     *
     * @param string|null $repository
     *   The "remote" repository that is destination of a push operation.
     * @param string|null $refspec
     *   Optionally pass a refspec to a remote repository.
     * @param array $options
     *   Associative array of command line options and flags.
     *
     * @return GitWorkingCopy
     *
     * @throws GitWrapper::Exception::GitException
     *
     * @see GitWrapper::GitCommand::GitPush
     */
    public function push($repository = null, $refspec = null, array $options = array())
    {
        $push = new GitPush($this->_directory, $repository, $refspec);
        return $this->run($push, $options);
    }

    /**
     * Executes a `git rm` command.
     *
     * Removes files from the working tree and from the index.
     *
     * @param string $filepattern
     *   Files to remove from version control. Fileglobs (e.g.  *.c) can be
     *   given to add all matching files. Also a leading directory name (e.g.
     *   dir to add dir/file1 and dir/file2) can be given to add all files in
     *   the directory, recursively.
     * @param array $options
     *   Associative array of command line options and flags.
     *
     * @return GitWorkingCopy
     *
     * @throws GitWrapper::Exception::GitException
     *
     * @see GitWrapper::GitCommand::GitAdd
     */
    public function rm($filepattern, array $options = array())
    {
        $rm = new GitRm($this->_directory, $filepattern);
        return $this->run($rm, $options);
    }

    /**
     * Executes a `git status` command.
     *
     * Shows the working tree status.
     *
     * @param string|null $pathspec
     *   Optionally pass a pathspec.
     * @param array $options
     *   Associative array of command line options and flags.
     *
     * @return GitWorkingCopy
     *
     * @throws GitWrapper::Exception::GitException
     *
     * @see GitWrapper::GitCommand::GitStatus
     */
    public function status($pathspec = null, array $options = array())
    {
        $status = new GitStatus($this->_directory, $pathspec);
        return $this->run($status, $options);
    }

    /**
     * Executes a `git tag` command.
     *
     * Creates, lists, deletes or verifies a tag.
     *
     * @param string|null $tagname
     *   The name of the tag.
     * @param string|null $commit
     *   The commit hash.
     * @param array $options
     *   Associative array of command line options and flags.
     *
     * @return GitWorkingCopy
     *
     * @throws GitWrapper::Exception::GitException
     *
     * @see GitWrapper::GitCommand::GitTag
     */
    public function tag($tagname = null, $commit = null, array $options = array())
    {
        $tag = new GitTag($this->_directory, $tagname, $commit);
        return $this->run($tag, $options);
    }

    /**
     * @} End of "defgroup command".
     */

    /**
     * Hackish, allows us to use "clone" as a method name.
     *
     * $throws \BadMethodCallException
     * @throws GitWrapper::Exception::GitException
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

    /**
     * Gets the output captured by the last run Git commnd(s).
     *
     * @return string
     *
     * @see GitWorkingCopy::getOutput()
     */
    public function __toString()
    {
        return $this->getOutput();
    }
}
