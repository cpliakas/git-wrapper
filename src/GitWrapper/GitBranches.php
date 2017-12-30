<?php declare(strict_types=1);

namespace GitWrapper;

use ArrayIterator;
use IteratorAggregate;

/**
 * Class that parses and returnes an array of branches.
 */
class GitBranches implements IteratorAggregate
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
     * @param \GitWrapper\GitWorkingCopy $git The working copy that branches are being collected from.
     */
    public function __construct(GitWorkingCopy $git)
    {
        $this->git = clone $git;
        $output = (string) $git->branch(['a' => true]);
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
    public function fetchBranches(bool $onlyRemote = false): array
    {
        $this->git->clearOutput();
        $options = ($onlyRemote) ? ['r' => true] : ['a' => true];
        $output = (string) $this->git->branch($options);
        $branches = preg_split("/\r\n|\n|\r/", rtrim($output));
        return array_map([$this, 'trimBranch'], $branches);
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
    public function trimBranch(string $branch): string
    {
        return ltrim($branch, ' *');
    }

    /**
     * Implements \IteratorAggregate::getIterator().
     */
    public function getIterator()
    {
        $branches = $this->all();
        return new ArrayIterator($branches);
    }

    /**
     * Returns all branches.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->fetchBranches();
    }

    /**
     * Returns only remote branches.
     *
     * @return array
     */
    public function remote(): array
    {
        return $this->fetchBranches(true);
    }

    /**
     * Returns currently active branch (HEAD) of the working copy.
     *
     */
    public function head(): string
    {
        return trim((string) $this->git->run(['rev-parse --abbrev-ref HEAD']));
    }
}
