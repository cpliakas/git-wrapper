<?php declare(strict_types=1);

namespace GitWrapper;

use ArrayIterator;
use IteratorAggregate;

/**
 * Class that parses and returnes an array of Tags.
 */
class GitTags implements IteratorAggregate
{
    /**
     * @var GitWorkingCopy
     */
    protected $git;

    public function __construct(GitWorkingCopy $git)
    {
        $this->git = clone $git;
    }

    /**
     * Fetches the Tags via the `git branch` command.
     *
     * @param bool $onlyRemote Whether to fetch only remote Tags, defaults to false which returns all Tags.
     *
     * @return mixed[]
     */
    public function fetchTags(): array
    {
        $this->git->clearOutput();
        $output = (string) $this->git->tag(['l' => true]);
        $tags = preg_split("/\r\n|\n|\r/", rtrim($output));
        return array_map([$this, 'trimTags'], $tags);
    }

    /**
     * Strips unwanted characters from the branch
     */
    public function trimTags(string $branch): string
    {
        return ltrim($branch, ' *');
    }

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
