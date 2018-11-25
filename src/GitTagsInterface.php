<?php
declare(strict_types=1);

namespace GitWrapper;


use ArrayIterator;

/**
 * Class that parses and returns an array of Tags.
 */
interface GitTagsInterface
{
    /**
     * Fetches the Tags via the `git branch` command.
     *
     * @return mixed[]
     */
    public function fetchTags(): array;

    /**
     * Strips unwanted characters from the branch
     */
    public function trimTags(string $branch): string;

    public function getIterator(): ArrayIterator;

    /**
     * @return mixed[]
     */
    public function all(): array;
}