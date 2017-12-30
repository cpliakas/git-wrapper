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
     * @var GitWorkingCopy
     */
    protected $git;

    public function __construct(GitWorkingCopy $git)
    {
        $this->git = clone $git;
        $output = (string) $git->branch(['a' => true]);
    }

    /**
     * Fetches the branches via the `git branch` command.
     *
     * @param bool $onlyRemote Whether to fetch only remote branches, defaults to false which returns all branches.
     *
     * @return mixed[]
     */
    public function fetchBranches(bool $onlyRemote = false): array
    {
        $this->git->clearOutput();
        $options = ($onlyRemote) ? ['r' => true] : ['a' => true];
        $output = (string) $this->git->branch($options);
        $branches = preg_split("/\r\n|\n|\r/", rtrim($output));
        return array_map([$this, 'trimBranch'], $branches);
    }

    public function trimBranch(string $branch): string
    {
        return ltrim($branch, ' *');
    }

    public function getIterator(): ArrayIterator
    {
        $branches = $this->all();
        return new ArrayIterator($branches);
    }

    /**
     * @return string[]
     */
    public function all(): array
    {
        return $this->fetchBranches();
    }

    /**
     * @return string[]
     */
    public function remote(): array
    {
        return $this->fetchBranches(true);
    }

    /**
     * Returns currently active branch (HEAD) of the working copy.
     */
    public function head(): string
    {
        return trim((string) $this->git->run(['rev-parse --abbrev-ref HEAD']));
    }
}
