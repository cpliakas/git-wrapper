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
     * The working copy that Tags are being collected from.
     *
     * @var \GitWrapper\GitWorkingCopy
     */
    protected $git;

    /**
     * @param \GitWrapper\GitWorkingCopy $git The working copy that Tags are being collected from.
     */
    public function __construct(GitWorkingCopy $git)
    {
        $this->git = clone $git;
    }

    /**
     * Fetches the Tags via the `git branch` command.
     *
     * @param bool $onlyRemote Whether to fetch only remote Tags, defaults to false which returns all Tags.
     *
     * @return array
     */
    public function fetchTags(): array
    {
        $this->git->clearOutput();
        $output = (string) $this->git->tag(['l' => true]);
        $tags = preg_split("/\r\n|\n|\r/", rtrim($output));
        return array_map([$this, 'trimTags'], $tags);
    }

    /**
     * Strips unwanted characters from the branch.
     *
     * @param string $branch The raw branch returned in the output of the Git command.
     *
     * @return string
     *   The processed branch name.
     */
    public function trimTags(string $branch): string
    {
        return ltrim($branch, ' *');
    }

    /**
     * Implements \IteratorAggregate::getIterator().
     */
    public function getIterator()
    {
        $tags = $this->all();
        return new ArrayIterator($tags);
    }

    /**
     * Returns all Tags.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->fetchTags();
    }
}
