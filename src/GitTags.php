<?php declare(strict_types=1);

namespace GitWrapper;

use ArrayIterator;

/**
 * Class that parses and returns an array of Tags.
 */
final class GitTags implements GitTagsInterface
{
    /**
     * @var GitWorkingCopyInterface
     */
    private $gitWorkingCopy;

    public function __construct(GitWorkingCopyInterface $gitWorkingCopy)
    {
        $this->gitWorkingCopy = clone $gitWorkingCopy;
    }

    /**
     * @inheritdoc
     */
    public function fetchTags(): array
    {
        $output = $this->gitWorkingCopy->tag(['l' => true]);
        $tags = (array) preg_split("/\r\n|\n|\r/", rtrim($output));
        return array_map([$this, 'trimTags'], $tags);
    }

    /**
     * @inheritdoc
     */
    public function trimTags(string $branch): string
    {
        return ltrim($branch, ' *');
    }

    /**
     * @inheritdoc
     */
    public function getIterator(): ArrayIterator
    {
        $tags = $this->all();
        return new ArrayIterator($tags);
    }

    /**
     * @inheritdoc
     */
    public function all(): array
    {
        return $this->fetchTags();
    }
}
