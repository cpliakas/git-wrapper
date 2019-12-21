<?php

declare(strict_types=1);

namespace GitWrapper;

use ArrayIterator;
use IteratorAggregate;
use Nette\Utils\Strings;

/**
 * Class that parses and returnes an array of Tags.
 *
 * @implements IteratorAggregate<int,string>
 */
final class GitTags implements IteratorAggregate
{
    /**
     * @var GitWorkingCopy
     */
    private $gitWorkingCopy;

    public function __construct(GitWorkingCopy $gitWorkingCopy)
    {
        $this->gitWorkingCopy = clone $gitWorkingCopy;
    }

    /**
     * Fetches the Tags via the `git branch` command.
     * @return string[]
     */
    public function fetchTags(): array
    {
        $output = $this->gitWorkingCopy->tag(['l' => true]);
        $tags = (array) Strings::split(rtrim($output), "/\r\n|\n|\r/");
        return array_map([$this, 'trimTags'], $tags);
    }

    /**
     * Strips unwanted characters from the branch
     */
    public function trimTags(string $branch): string
    {
        return ltrim($branch, ' *');
    }

    /**
     * @return ArrayIterator<int,string>
     */
    public function getIterator(): ArrayIterator
    {
        $tags = $this->all();
        return new ArrayIterator($tags);
    }

    /**
     * @return mixed[]
     */
    public function all(): array
    {
        return $this->fetchTags();
    }
}
