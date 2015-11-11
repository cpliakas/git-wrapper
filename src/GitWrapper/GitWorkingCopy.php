<?php

namespace GitWrapper;

use Symfony\Component\Process\ProcessUtils;

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
     * @var \GitWrapper\GitWrapper
     */
    protected $wrapper;

    /**
     * Path to the directory containing the working copy.
     *
     * @var string
     */
    protected $directory;

    /**
     * The output captured by the last run Git commnd(s).
     *
     * @var string
     */
    protected $output = '';

    /**
     * A boolean flagging whether the repository is cloned.
     *
     * If the variable is null, the a rudimentary check will be performed to see
     * if the directory looks like it is a working copy.
     *
     * @param bool|null
     */
    protected $cloned;

    /**
     * Constructs a GitWorkingCopy object.
     *
     * @param \GitWrapper\GitWrapper $wrapper
     *   The GitWrapper object that likely instantiated this class.
     * @param string $directory
     *   Path to the directory containing the working copy.
     */
    public function __construct(GitWrapper $wrapper, $directory)
    {
        $this->wrapper = $wrapper;
        $this->directory = $directory;
    }

    /**
     * Returns the GitWrapper object that likely instantiated this class.
     *
     * @return \GitWrapper\GitWrapper
     */
    public function getWrapper()
    {
        return $this->wrapper;
    }

    /**
     * Gets the path to the directory containing the working copy.
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Gets the output captured by the last run Git commnd(s).
     *
     * @return string
     */
    public function getOutput()
    {
        $output = $this->output;
        $this->output = '';
        return $output;
    }

    /**
     * Clears the stored output captured by the last run Git command(s).
     *
     * @return \GitWrapper\GitWorkingCopy
     */
    public function clearOutput()
    {
        $this->output = '';
        return $this;
    }

    /**
     * Manually sets the cloned flag.
     *
     * @param boolean $cloned
     *   Whether the repository is cloned into the directory or not.
     *
     * @return \GitWrapper\GitWorkingCopy
     */
    public function setCloned($cloned)
    {
        $this->cloned = (bool) $cloned;
        return $this;
    }

    /**
     * Checks whether a repository has already been cloned to this directory.
     *
     * If the flag is not set, test if it looks like we're at a git directory.
     *
     * @return boolean
     */
    public function isCloned()
    {
        if (!isset($this->cloned)) {
            $gitDir = $this->directory;
            if (is_dir($gitDir . '/.git')) {
                $gitDir .= '/.git';
            };
            $this->cloned = (is_dir($gitDir . '/objects') && is_dir($gitDir . '/refs') && is_file($gitDir . '/HEAD'));
        }
        return $this->cloned;
    }

    /**
     * Runs a Git command and captures the output.
     *
     * @param array $args
     *   The arguments passed to the command method.
     * @param boolean $setDirectory
     *   Set the working directory, defaults to true.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
     *
     * @see GitWrapper::run()
     */
    public function run($args, $setDirectory = true)
    {
        $command = call_user_func_array(array('GitWrapper\GitCommand', 'getInstance'), $args);
        if ($setDirectory) {
            $command->setDirectory($this->directory);
        }
        $this->output .= $this->wrapper->run($command);
        return $this;
    }

    /**
     * @defgroup command_helpers Git Command Helpers
     *
     * Helper methods that wrap common Git commands.
     *
     * @{
     */

    /**
     * Returns the output of a `git status -s` command.
     *
     * @return string
     *
     * @throws \GitWrapper\GitException
     */
    public function getStatus()
    {
        return $this->wrapper->git('status -s', $this->directory);
    }

    /**
     * Returns true if there are changes to commit.
     *
     * @return bool
     *
     * @throws \GitWrapper\GitException
     */
    public function hasChanges()
    {
        $output = $this->getStatus();
        return !empty($output);
    }

    /**
     * Returns whether HEAD has a remote tracking branch.
     *
     * @return bool
     */
    public function isTracking()
    {
        try {
            $this->run(array('rev-parse @{u}'));
        } catch (GitException $e) {
            return false;
        }
        return true;
    }

    /**
     * Returns whether HEAD is up-to-date with its remote tracking branch.
     *
     * @return bool
     *
     * @throws \GitWrapper\GitException
     *   Thrown when HEAD does not have a remote tracking branch.
     */
    public function isUpToDate()
    {
        if (!$this->isTracking()) {
            throw new GitException('Error: HEAD does not have a remote tracking branch. Cannot check if it is up-to-date.');
        }
        $this->clearOutput();
        $merge_base = (string) $this->run(array('merge-base @ @{u}'));
        $remote_sha = (string) $this->run(array('rev-parse @{u}'));
        return $merge_base === $remote_sha;
    }

    /**
     * Returns whether HEAD is ahead of its remote tracking branch.
     *
     * If this returns true it means that commits are present locally which have
     * not yet been pushed to the remote.
     *
     * @return bool
     *
     * @throws \GitWrapper\GitException
     *   Thrown when HEAD does not have a remote tracking branch.
     */
    public function isAhead()
    {
        if (!$this->isTracking()) {
            throw new GitException('Error: HEAD does not have a remote tracking branch. Cannot check if it is ahead.');
        }
        $this->clearOutput();
        $merge_base = (string) $this->run(array('merge-base @ @{u}'));
        $local_sha = (string) $this->run(array('rev-parse @'));
        $remote_sha = (string) $this->run(array('rev-parse @{u}'));
        return $merge_base === $remote_sha && $local_sha !== $remote_sha;
    }

    /**
     * Returns whether HEAD is behind its remote tracking branch.
     *
     * If this returns true it means that a pull is needed to bring the branch
     * up-to-date with the remote.
     *
     * @return bool
     *
     * @throws \GitWrapper\GitException
     *   Thrown when HEAD does not have a remote tracking branch.
     */
    public function isBehind()
    {
        if (!$this->isTracking()) {
            throw new GitException('Error: HEAD does not have a remote tracking branch. Cannot check if it is behind.');
        }
        $this->clearOutput();
        $merge_base = (string) $this->run(array('merge-base @ @{u}'));
        $local_sha = (string) $this->run(array('rev-parse @'));
        $remote_sha = (string) $this->run(array('rev-parse @{u}'));
        return $merge_base === $local_sha && $local_sha !== $remote_sha;
    }

    /**
     * Returns whether HEAD needs to be merged with its remote tracking branch.
     *
     * If this returns true it means that HEAD has diverged from its remote
     * tracking branch; new commits are present locally as well as on the
     * remote.
     *
     * @return bool
     *   true if HEAD needs to be merged with the remote, false otherwise.
     *
     * @throws \GitWrapper\GitException
     *   Thrown when HEAD does not have a remote tracking branch.
     */
    public function needsMerge()
    {
        if (!$this->isTracking()) {
            throw new GitException('Error: HEAD does not have a remote tracking branch. Cannot check if it is behind.');
        }
        $this->clearOutput();
        $merge_base = (string) $this->run(array('merge-base @ @{u}'));
        $local_sha = (string) $this->run(array('rev-parse @'));
        $remote_sha = (string) $this->run(array('rev-parse @{u}'));
        return $merge_base !== $local_sha && $merge_base !== $remote_sha;
    }

    /**
     * Returns a GitBranches object containing information on the repository's
     * branches.
     *
     * @return GitBranches
     */
    public function getBranches()
    {
        return new GitBranches($this);
    }

    /**
     * Helper method that pushes a tag to a repository.
     *
     * This is synonymous with `git push origin tag v1.2.3`.
     *
     * @param string $tag
     *   The tag being pushed.
     * @param string $repository
     *   The destination of the push operation, which is either a URL or name of
     *   the remote. Defaults to "origin".
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @see GitWorkingCopy::push()
     */
    public function pushTag($tag, $repository = 'origin', array $options = array())
    {
        return $this->push($repository, 'tag', $tag, $options);
    }

    /**
     * Helper method that pushes all tags to a repository.
     *
     * This is synonymous with `git push --tags origin`.
     *
     * @param string $repository
     *   The destination of the push operation, which is either a URL or name of
     *   the remote. Defaults to "origin".
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @see GitWorkingCopy::push()
     */
    public function pushTags($repository = 'origin', array $options = array())
    {
        $options['tags'] = true;
        return $this->push($repository, $options);
    }

    /**
     * Fetches all remotes.
     *
     * This is synonymous with `git fetch --all`.
     *
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @see GitWorkingCopy::fetch()
     */
    public function fetchAll(array $options = array())
    {
        $options['all'] = true;
        return $this->fetch($options);
    }

    /**
     * Create a new branch and check it out.
     *
     * This is synonymous with `git checkout -b`.
     *
     * @param string $branch
     *   The new branch being created.
     *
     * @see GitWorkingCopy::checkout()
     */
    public function checkoutNewBranch($branch, array $options = array())
    {
        $options['b'] = true;
        return $this->checkout($branch, $options);
    }

    /**
     * @} End of "defgroup command_helpers".
     */

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
     * @code
     * $git->add('some/file.txt');
     * @endcode
     *
     * @param string $filepattern
     *   Files to add content from. Fileglobs (e.g.  *.c) can be given to add
     *   all matching files. Also a leading directory name (e.g.  dir to add
     *   dir/file1 and dir/file2) can be given to add all files in the
     *   directory, recursively.
     * @param array $options
     *   An optional array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
     */
    public function add($filepattern, array $options = array())
    {
        $args = array(
            'add',
            $filepattern,
            $options,
        );
        return $this->run($args);
    }

    /**
     * Executes a `git apply` command.
     *
     * Apply a patch to files and/or to the index
     *
     * @code
     * $git->apply('the/file/to/read/the/patch/from');
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return GitWorkingCopy
     *
     * @throws GitException
     */
    public function apply()
    {
        $args = func_get_args();
        array_unshift($args, 'apply');
        return $this->run($args);
    }

    /**
     * Executes a `git bisect` command.
     *
     * Find by binary search the change that introduced a bug.
     *
     * @code
     * $git->bisect('good', '2.6.13-rc2');
     * $git->bisect('view', array('stat' => true));
     * @endcode
     *
     * @param string $sub_command
     *   The subcommand passed to `git bisect`.
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
     */
    public function bisect($sub_command)
    {
        $args = func_get_args();
        $args[0] = 'bisect ' . ProcessUtils::escapeArgument($sub_command);
        return $this->run($args);
    }

    /**
     * Executes a `git branch` command.
     *
     * List, create, or delete branches.
     *
     * @code
     * $git->branch('my2.6.14', 'v2.6.14');
     * $git->branch('origin/html', 'origin/man', array('d' => true, 'r' => 'origin/todo'));
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
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
     * @code
     * $git->checkout('new-branch', array('b' => true));
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
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
     * @code
     * $git->clone('git://github.com/cpliakas/git-wrapper.git');
     * @endcode
     *
     * @param string $repository
     *   The Git URL of the repository being cloned.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @param string $repository
     *   The URL of the repository being cloned.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
     */
    public function cloneRepository($repository, $options = array())
    {
        $args = array(
            'clone',
            $repository,
            $this->directory,
            $options,
        );
        return $this->run($args, false);
    }

    /**
     * Executes a `git commit` command.
     *
     * Record changes to the repository. If only one argument is passed, it is
     * assumed to be the commit message. Therefore `$git->commit('Message');`
     * yields a `git commit -am "Message"` command.
     *
     * @code
     * $git->commit('My commit message');
     * $git->commit('Makefile', array('m' => 'My commit message'));
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
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
     * @code
     * $git->config('user.email', 'opensource@chrispliakas.com');
     * $git->config('user.name', 'Chris Pliakas');
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
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
     * @code
     * $git->diff();
     * $git->diff('topic', 'master');
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
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
     * @code
     * $git->fetch('origin');
     * $git->fetch(array('all' => true));
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
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
     * @code
     * $git->grep('time_t', '--', '*.[ch]');
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
     */
    public function grep()
    {
        $args = func_get_args();
        array_unshift($args, 'grep');
        return $this->run($args);
    }

    /**
     * Executes a `git init` command.
     *
     * Create an empty git repository or reinitialize an existing one.
     *
     * @code
     * $git->init(array('bare' => true));
     * @endcode
     *
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
     */
    public function init(array $options = array())
    {
        $args = array(
            'init',
            $this->directory,
            $options,
        );
        return $this->run($args, false);
    }

    /**
     * Executes a `git log` command.
     *
     * Show commit logs.
     *
     * @code
     * $git->log(array('no-merges' => true));
     * $git->log('v2.6.12..', 'include/scsi', 'drivers/scsi');
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
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
     * @code
     * $git->merge('fixes', 'enhancements');
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
     */
    public function merge()
    {
        $args = func_get_args();
        array_unshift($args, 'merge');
        return $this->run($args);
    }

    /**
     * Executes a `git mv` command.
     *
     * Move or rename a file, a directory, or a symlink.
     *
     * @code
     * $git->mv('orig.txt', 'dest.txt');
     * @endcode
     *
     * @param string $source
     *   The file / directory being moved.
     * @param string $destination
     *   The target file / directory that the source is being move to.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
     */
    public function mv($source, $destination, array $options = array())
    {
        $args = array(
            'mv',
            $source,
            $destination,
            $options,
        );
        return $this->run($args);
    }

    /**
     * Executes a `git pull` command.
     *
     * Fetch from and merge with another repository or a local branch.
     *
     * @code
     * $git->pull('upstream', 'master');
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
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
     * @code
     * $git->push('upstream', 'master');
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
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
     * @code
     * $git->rebase('subsystem@{1}', array('onto' => 'subsystem'));
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
     */
    public function rebase()
    {
        $args = func_get_args();
        array_unshift($args, 'rebase');
        return $this->run($args);
    }

    /**
     * Executes a `git remote` command.
     *
     * Manage the set of repositories ("remotes") whose branches you track.
     *
     * @code
     * $git->remote('add', 'upstream', 'git://github.com/cpliakas/git-wrapper.git');
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
     */
    public function remote()
    {
        $args = func_get_args();
        array_unshift($args, 'remote');
        return $this->run($args);
    }

    /**
     * Executes a `git reset` command.
     *
     * Reset current HEAD to the specified state.
     *
     * @code
     * $git->reset(array('hard' => true));
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
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
     * @code
     * $git->rm('oldfile.txt');
     * @endcode
     *
     * @param string $filepattern
     *   Files to remove from version control. Fileglobs (e.g.  *.c) can be
     *   given to add all matching files. Also a leading directory name (e.g.
     *   dir to add dir/file1 and dir/file2) can be given to add all files in
     *   the directory, recursively.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
     */
    public function rm($filepattern, array $options = array())
    {
        $args = array(
            'rm',
            $filepattern,
            $options,
        );
        return $this->run($args);
    }

    /**
     * Executes a `git show` command.
     *
     * Show various types of objects.
     *
     * @code
     * $git->show('v1.0.0');
     * @endcode
     *
     * @param string $object
     *   The names of objects to show. For a more complete list of ways to spell
     *   object names, see "SPECIFYING REVISIONS" section in gitrevisions(7).
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
     */
    public function show($object, array $options = array())
    {
        $args = array('show', $object, $options);
        return $this->run($args);
    }

    /**
     * Executes a `git status` command.
     *
     * Show the working tree status.
     *
     * @code
     * $git->status(array('s' => true));
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
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

     * @code
     * $git->tag('v1.0.0');
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
     */
    public function tag()
    {
        $args = func_get_args();
        array_unshift($args, 'tag');
        return $this->run($args);
    }

    /**
     * Executes a `git clean` command.
     *
     * Remove untracked files from the working tree
     *
     * @code
     * $git->clean('-d', '-f');
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
     */
    public function clean()
    {
        $args = func_get_args();
        array_unshift($args, 'clean');
        return $this->run($args);
    }

     /**
     * Executes a `git archive` command.
     *
     * Create an archive of files from a named tree
     *
     * @code
     * $git->archive('HEAD', array('o' => '/path/to/archive'));
     * @endcode
     *
     * @param string ...
     *   (optional) Additional command line arguments.
     * @param array $options
     *   (optional) An associative array of command line options.
     *
     * @return \GitWrapper\GitWorkingCopy
     *
     * @throws \GitWrapper\GitException
     */
    public function archive()
    {
        $args = func_get_args();
        array_unshift($args, 'archive');
        return $this->run($args);
    }

    /**
     * @} End of "defgroup command".
     */

    /**
     * Hackish, allows us to use "clone" as a method name.
     *
     * $throws \BadMethodCallException
     * @throws \GitWrapper\GitException
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
