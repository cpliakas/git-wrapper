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

/**
 * Class that parses and returnes an array of branches.
 */
class GitBranches implements \IteratorAggregate
{
    /**
     * The working copy that branches are being collected from.
     *
     * @var GitWorkingCopy
     */
    protected $_git;

    /**
     * Constructs a GitBranches object.
     *
     * @param GitWorkingCopy $git
     *   The working copy that branches are being collected from.
     *
     * @throws GitException
     */
    public function __construct(GitWorkingCopy $git)
    {
        $this->_git = clone $git;
        $output = (string) $git->branch(array('a' => true));
    }

    /**
     * Fetches the branches via the `git branch` command.
     *
     * @param boolean $only_remote
     *   Whether to fetch only remote branches, defaults to false which returns
     *   all branches.
     *
     * @return array
     */
    public function fetchBranches($only_remote = false)
    {
        $this->_git->clearOutput();
        $options = ($only_remote) ? array('r' => true) : array('a' => true);
        $output = (string) $this->_git->branch($options);
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
}
