<?php
declare(strict_types=1);

namespace GitWrapper;


use ArrayIterator;

/**
 * Class that parses and returns an array of branches.
 */
interface GitBranchesInterface
{
    /**
     * Fetches the branches via the `git branch` command.
     *
     * @param bool $onlyRemote Whether to fetch only remote branches, defaults to false which returns all branches.
     * @return mixed[]
     */
    public function fetchBranches(bool $onlyRemote = false): array;

    public function trimBranch(string $branch): string;

    public function getIterator(): ArrayIterator;

    /**
     * @return string[]
     */
    public function all(): array;

    /**
     * @return string[]
     */
    public function remote(): array;

    /**
     * Returns currently active branch (HEAD) of the working copy.
     */
    public function head(): string;
}