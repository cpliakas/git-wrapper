<?php declare(strict_types=1);

namespace GitWrapper;

use ArrayIterator;
use IteratorAggregate;

/**
 * Class that parses and returns an array of branches.
 */
final class GitBranches implements GitBranchesInterface
{
    /**
     * @var GitWorkingCopyInterface
     */
    private $gitWorkingCopy;

    public function __construct(GitWorkingCopyInterface $gitWorkingCopy)
    {
        $this->gitWorkingCopy = clone $gitWorkingCopy;
        $gitWorkingCopy->branch(['a' => true]);
    }

    /**
     * @inheritdoc
     */
    public function fetchBranches(bool $onlyRemote = false): array
    {
        $options = $onlyRemote ? ['r' => true] : ['a' => true];
        $output = $this->gitWorkingCopy->branch($options);
        $branches = (array)preg_split("/\r\n|\n|\r/", rtrim($output));
        return array_map([$this, 'trimBranch'], $branches);
    }

    /**
     * @inheritdoc
     */
    public function trimBranch(string $branch): string
    {
        return ltrim($branch, ' *');
    }

    /**
     * @inheritdoc
     */
    public function getIterator(): ArrayIterator
    {
        $branches = $this->all();
        return new ArrayIterator($branches);
    }

    /**
     * @inheritdoc
     */
    public function all(): array
    {
        return $this->fetchBranches();
    }

    /**
     * @inheritdoc
     */
    public function remote(): array
    {
        return $this->fetchBranches(true);
    }

    /**
     * @inheritdoc
     */
    public function head(): string
    {
        return trim($this->gitWorkingCopy->run('rev-parse', ['--abbrev-ref HEAD']));
    }
}
