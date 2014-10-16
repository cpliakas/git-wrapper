<?php

/**
 * A PHP wrapper around the Git command line utility.
 */

namespace GitWrapper;

/**
 * Class that parses and returnes an array of branches.
 */
class GitBranches implements \IteratorAggregate
{
    /**
     * The working copy that branches are being collected from.
     *
     * @var \GitWrapper\GitWorkingCopy
     */
    protected $git;

    /**
     * Constructs a GitBranches object.
     *
     * @param \GitWrapper\GitWorkingCopy $git
     *   The working copy that branches are being collected from.
     *
     * @throws \GitWrapper\GitException
     */
    public function __construct(GitWorkingCopy $git)
    {
        $this->git = clone $git;
        $output = (string) $git->branch(array('a' => true));
    }

    /**
     * Fetches the branches via the `git branch` command.
     *
     * @param boolean $onlyRemote
     *   Whether to fetch only remote branches, defaults to false which returns
     *   all branches.
     *
     * @return array
     */
    public function fetchBranches($onlyRemote = false)
    {
        $this->git->clearOutput();
        $options = ($onlyRemote) ? array('r' => true) : array('a' => true);
        $output = (string) $this->git->branch($options);
        $branches = preg_split("/\r\n|\n|\r/", rtrim($output));
        return array_map(array($this, 'trimBranch'), $branches);
    }

    /**
     * Strips unwanted characters from the branch.
     *
     * @param string $branch
     *   The raw branch returned in the output of the Git command.
     *
     * @return string
     *   The processed branch name.
     */
    public function trimBranch($branch)
    {
        return ltrim($branch, ' *');
    }

    /**
     * Implements \IteratorAggregate::getIterator().
     */
    public function getIterator()
    {
        $branches = $this->all();
        return new \ArrayIterator($branches);
    }

    /**
     * Returns all branches.
     *
     * @return array
     */
    public function all()
    {
        return $this->fetchBranches();
    }

    /**
     * Returns only remote branches.
     *
     * @return array
     */
    public function remote()
    {
        return $this->fetchBranches(true);
    }

    /**
     * Returns currently active branch (HEAD) of the working copy.
     *
     * @return array
     */
    public function head()
    {
        return (string) $this->git->run(array('rev-parse --abbrev-ref HEAD'));
    }
}
