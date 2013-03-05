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
     * Properly escapes file patterns that are passed as arguments.
     *
     * This method only escape paths with files that have extensions. If the
     * path does not have an extension, there is no need to excape the periods.
     *
     * This is most useful for Git "add" and "rm" commands.
     *
     * @param string $filepattern
     *   The file pattern being escaped.
     *
     * @return string
     */
    public function escapeFilepattern($filepattern)
    {
        $path_info = pathinfo($filepattern);
        if (isset($path_info['extension'])) {
            $path_info['basename'] = str_replace('.', '\\.', $path_info['basename']);
        }
        return $path_info['dirname'] . DIRECTORY_SEPARATOR . $path_info['basename'];
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
     * Checks whether a repository has already been cloned to this directory.
     *
     * @return boolean
     */
    public function isCloned()
    {
        return is_dir($this->_directory . '/.git');
    }

    /**
     * Returns the output of a `git status -s` command.
     *
     * @return string
     *
     * @throws GitException
     */
    public function getStatus()
    {
        return $this->_wrapper->git('status -s', $this->_directory);
    }

    /**
     * Returns true if there are changes to commit.
     *
     * @return bool
     *
     * @throws GitException
     */
    public function hasChanges()
    {
        $output = $this->getStatus();
        return !empty($output);
    }

    /**
     * Runs a Git command and captures the output.
     *
     * @param array $args
     *   The arguments passed to the command method.
     * @param boolean $set_directory
     *   Set the working directory, defaults to true.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     *
     * @see GitWrapper::run()
     */
    public function run($args, $set_directory = true)
    {
        $command = call_user_func_array(array('GitWrapper\GitCommand', 'getInstance'), $args);
        if ($set_directory) {
            $command->setDirectory($this->_directory);
        }
        $this->_output .= $this->_wrapper->run($command);
        return $this;
    }

    /**
     * @defgroup commands Git Commands
     *
     * All methods in this group correspond with Git commands, for example
     * "git add", "git commit", "git push", etc.
     *
     * @{
     */

    /**
     * Executes a `git add` command.
     *
     * Add file contents to the index.
     *
     * @param string $filepattern
     *   Files to add content from. Fileglobs (e.g.  *.c) can be given to add
     *   all matching files. Also a leading directory name (e.g.  dir to add
     *   dir/file1 and dir/file2) can be given to add all files in the
     *   directory, recursively.
     * @param array $options
     *   An optional array of command line options.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     */
    public function add($filepattern, array $options = array())
    {
        $args = func_get_args();
        $args[0] = $this->escapeFilepattern($args[0]);
        array_unshift($args, 'add');
        return $this->run($args);
    }

    /**
     * Executes a `git bisect` command.
     *
     * Find by binary search the change that introduced a bug.
     *
     * @param string $sub_command
     *   The subcommand passed to `git bisect`.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     */
    public function bisect($sub_command)
    {
        $args = func_get_args();
        $arg[0] = 'bisect ' . escapeshellcmd($sub_command);
        return $this->run($args);
    }

    /**
     * Executes a `git branch` command.
     *
     * List, create, or delete branches.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     */
    public function branch()
    {
        $args = func_get_args();
        array_unshift($args, 'branch');
        return $this->run($args);
    }

    /**
     * Executes a `git checkout` command.
     *
     * Checkout a branch or paths to the working tree.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     */
    public function checkout()
    {
        $args = func_get_args();
        array_unshift($args, 'checkout');
        return $this->run($args);
    }

    /**
     * Executes a `git clone` command.
     *
     * Clone a repository into a new directory. Use GitWorkingCopy::clone()
     * instead for more readable code.
     *
     * @param string $repository
     *   The URL of the repository being cloned.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     */
    public function cloneRepository($repository)
    {
        $args = func_get_args();
        if (!isset($args[1]) || !is_string($args[1])) {
            array_unshift($args, $repository);
            $args[1] = GitWrapper::parseRepositoryName($repository);
        }
        array_unshift($args, 'clone');
        return $this->run($args, false);
    }

    /**
     * Executes a `git commit` command.
     *
     * Record changes to the repository. If only one argument is passed, it is
     * assumed to be the commit message. Therefore `$git->commit('Message');`
     * yields a `git commit -am "Message"` command.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     */
    public function commit()
    {
        $args = func_get_args();
        if (isset($args[0]) && is_string($args[0]) && !isset($args[1])) {
            $args[0] = array(
                'm' => $args[0],
                'a' => true,
            );
        }
        array_unshift($args, 'commit');
        return $this->run($args);
    }

    /**
     * Executes a `git config` command.
     *
     * Get and set repository options.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     */
    public function config()
    {
        $args = func_get_args();
        array_unshift($args, 'config');
        return $this->run($args);
    }

    /**
     * Executes a `git diff` command.
     *
     * Show changes between commits, commit and working tree, etc.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     */
    public function diff()
    {
        $args = func_get_args();
        array_unshift($args, 'diff');
        return $this->run($args);
    }

    /**
     * Executes a `git fetch` command.
     *
     * Download objects and refs from another repository.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     */
    public function fetch()
    {
        $args = func_get_args();
        array_unshift($args, 'fetch');
        return $this->run($args);
    }

    /**
     * Executes a `git grep` command.
     *
     * Print lines matching a pattern.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     */
    public function grep()
    {
        $args = func_get_args();
        array_unshift($args, 'grep');
        return $this->run($args);
    }

    /**
     * Executes a `git log` command.
     *
     * Show commit logs.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     */
    public function log()
    {
        $args = func_get_args();
        array_unshift($args, 'log');
        return $this->run($args);
    }

    /**
     * Executes a `git merge` command.
     *
     * Join two or more development histories together.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     */
    public function merge()
    {
        $args = func_get_args();
        array_unshift($args, 'merge');
        return $this->run($args);
    }

    /**
     * Executes a `git pull` command.
     *
     * Move or rename a file, a directory, or a symlink.
     *
     * @param string $source
     *   The file / directory being moved.
     * @param string $destination
     *   The target file / directory that the source is being move to.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     */
    public function mv($source, $destination)
    {
        $args = func_get_args();
        array_unshift($args, 'mv');
        return $this->run($args);
    }

    /**
     * Executes a `git pull` command.
     *
     * Fetch from and merge with another repository or a local branch.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     */
    public function pull()
    {
        $args = func_get_args();
        array_unshift($args, 'pull');
        return $this->run($args);
    }

    /**
     * Executes a `git push` command.
     *
     * Update remote refs along with associated objects.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     */
    public function push()
    {
        $args = func_get_args();
        array_unshift($args, 'push');
        return $this->run($args);
    }

    /**
     * Executes a `git rebase` command.
     *
     * Forward-port local commits to the updated upstream head.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     */
    public function rebase()
    {
        $args = func_get_args();
        array_unshift($args, 'rebase');
        return $this->run($args);
    }

    /**
     * Executes a `git reset` command.
     *
     * Reset current HEAD to the specified state.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     */
    public function reset()
    {
        $args = func_get_args();
        array_unshift($args, 'reset');
        return $this->run($args);
    }

    /**
     * Executes a `git rm` command.
     *
     * Remove files from the working tree and from the index.
     *
     * @param string $filepattern
     *   Files to remove from version control. Fileglobs (e.g.  *.c) can be
     *   given to add all matching files. Also a leading directory name (e.g.
     *   dir to add dir/file1 and dir/file2) can be given to add all files in
     *   the directory, recursively.
     * @param array $options
     *   An optional array of command line options.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     */
    public function rm($filepattern, array $options = array())
    {
        $args = func_get_args();
        $args[0] = $this->escapeFilepattern($args[0]);
        array_unshift($args, 'rm');
        return $this->run($args);
    }

    /**
     * Executes a `git show` command.
     *
     * Show various types of objects.
     *
     * @param string $object
     *   The names of objects to show. For a more complete list of ways to spell
     *   object names, see "SPECIFYING REVISIONS" section in gitrevisions(7).
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     */
    public function show($object)
    {
        $args = func_get_args();
        array_unshift($args, 'show');
        return $this->run($args);
    }

    /**
     * Executes a `git status` command.
     *
     * Show the working tree status.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     */
    public function status()
    {
        $args = func_get_args();
        array_unshift($args, 'status');
        return $this->run($args);
    }

    /**
     * Executes a `git tag` command.
     *
     * Create, list, delete or verify a tag object signed with GPG.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     */
    public function tag()
    {
        $args = func_get_args();
        array_unshift($args, 'tag');
        return $this->run($args);
    }

    /**
     * @} End of "defgroup command".
     */

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
