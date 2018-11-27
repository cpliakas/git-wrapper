<?php declare(strict_types=1);

namespace GitWrapper;

/**
 * Interacts with a working copy.
 *
 * All commands executed via an instance of this class act on the working copy
 * that is set through the constructor.
 */
final class GitWorkingCopy implements GitWorkingCopyInterface
{
    /**
     * A boolean flagging whether the repository is cloned.
     *
     * If the variable is null, the a rudimentary check will be performed to see
     * if the directory looks like it is a working copy.
     *
     * @var bool|null
     */
    private $cloned;

    /**
     * Path to the directory containing the working copy.
     *
     * @var string
     */
    private $directory;

    /**
     * The GitWrapper object that likely instantiated this class.
     *
     * @var GitWrapperInterface
     */
    private $gitWrapper;

    public function __construct(GitWrapperInterface $gitWrapper, string $directory)
    {
        $this->gitWrapper = $gitWrapper;
        $this->directory = $directory;
    }

    /**
     * @inheritdoc
     */
    public function getWrapper(): GitWrapperInterface
    {
        return $this->gitWrapper;
    }

    /**
     * @inheritdoc
     */
    public function getDirectory(): string
    {
        return $this->directory;
    }

    /**
     * @inheritdoc
     */
    public function setCloned(bool $cloned): void
    {
        $this->cloned = $cloned;
    }

    /**
     * @inheritdoc
     */
    public function isCloned(): bool
    {
        if ($this->cloned === null) {
            $gitDir = $this->directory;
            if (is_dir($gitDir . '/.git')) {
                $gitDir .= '/.git';
            }

            $this->cloned = is_dir($gitDir . '/objects') && is_dir($gitDir . '/refs') && is_file($gitDir . '/HEAD');
        }

        return $this->cloned;
    }

    /**
     * @inheritdoc
     */
    public function run(string $command, array $argsAndOptions = [], bool $setDirectory = true): string
    {
        $command = new GitCommand($command, ...$argsAndOptions);
        if ($setDirectory) {
            $command->setDirectory($this->directory);
        }

        return $this->gitWrapper->run($command);
    }

    /**
     * @inheritdoc
     */
    public function getStatus(): string
    {
        return $this->run('status', ['-s']);
    }

    /**
     * @inheritdoc
     */
    public function hasChanges(): bool
    {
        $output = $this->getStatus();
        return ! empty($output);
    }

    /**
     * @inheritdoc
     */
    public function isTracking(): bool
    {
        try {
            $this->run('rev-parse', ['@{u}']);
        } catch (GitException $gitException) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function isUpToDate(): bool
    {
        if (! $this->isTracking()) {
            throw new GitException(
                'Error: HEAD does not have a remote tracking branch. Cannot check if it is up-to-date.'
            );
        }

        $mergeBase = $this->run('merge-base', ['@', '@{u}']);
        $remoteSha = $this->run('rev-parse', ['@{u}']);
        return $mergeBase === $remoteSha;
    }

    /**
     * @inheritdoc
     */
    public function isAhead(): bool
    {
        if (! $this->isTracking()) {
            throw new GitException('Error: HEAD does not have a remote tracking branch. Cannot check if it is ahead.');
        }

        $mergeBase = $this->run('merge-base', ['@', '@{u}']);
        $localSha = $this->run('rev-parse', ['@']);
        $remoteSha = $this->run('rev-parse', ['@{u}']);
        return $mergeBase === $remoteSha && $localSha !== $remoteSha;
    }

    /**
     * @inheritdoc
     */
    public function isBehind(): bool
    {
        if (! $this->isTracking()) {
            throw new GitException('Error: HEAD does not have a remote tracking branch. Cannot check if it is behind.');
        }

        $mergeBase = $this->run('merge-base', ['@', '@{u}']);
        $localSha = $this->run('rev-parse', ['@']);
        $remoteSha = $this->run('rev-parse', ['@{u}']);
        return $mergeBase === $localSha && $localSha !== $remoteSha;
    }

    /**
     * @inheritdoc
     */
    public function needsMerge(): bool
    {
        if (! $this->isTracking()) {
            throw new GitException('Error: HEAD does not have a remote tracking branch. Cannot check if it is behind.');
        }

        $mergeBase = $this->run('merge-base', ['@', '@{u}']);
        $localSha = $this->run('rev-parse', ['@']);
        $remoteSha = $this->run('rev-parse', ['@{u}']);
        return $mergeBase !== $localSha && $mergeBase !== $remoteSha;
    }

    /**
     * @inheritdoc
     */
    public function getBranches(): GitBranchesInterface
    {
        return new GitBranches($this);
    }

    /**
     * @inheritdoc
     */
    public function pushTag(string $tag, string $repository = 'origin', array $options = []): string
    {
        return $this->push($repository, 'tag', $tag, $options);
    }

    /**
     * @inheritdoc
     */
    public function pushTags(string $repository = 'origin', array $options = []): string
    {
        $options['tags'] = true;
        return $this->push($repository, $options);
    }

    /**
     * @inheritdoc
     */
    public function fetchAll(array $options = []): string
    {
        $options['all'] = true;
        return $this->fetch($options);
    }

    /**
     * @inheritdoc
     */
    public function checkoutNewBranch(string $branch, array $options = []): string
    {
        $options['b'] = true;
        return $this->checkout($branch, $options);
    }

    /**
     * @inheritdoc
     */
    public function addRemote(string $name, string $url, array $options = []): string
    {
        $this->ensureAddRemoveArgsAreValid($name, $url);

        $args = ['add'];

        // Add boolean options.
        foreach (['-f', '--tags', '--no-tags'] as $option) {
            if (! empty($options[$option])) {
                $args[] = $option;
            }
        }

        // Add tracking branches.
        if (! empty($options['-t'])) {
            foreach ($options['-t'] as $branch) {
                array_push($args, '-t', $branch);
            }
        }

        // Add master branch.
        if (! empty($options['-m'])) {
            array_push($args, '-m', $options['-m']);
        }

        // Add remote name and URL.
        array_push($args, $name, $url);

        return $this->remote(...$args);
    }

    /**
     * @inheritdoc
     */
    public function removeRemote(string $name): string
    {
        return $this->remote('rm', $name);
    }

    /**
     * @inheritdoc
     */
    public function hasRemote(string $name): bool
    {
        return array_key_exists($name, $this->getRemotes());
    }

    /**
     * @inheritdoc
     */
    public function getRemote(string $name): array
    {
        if (! $this->hasRemote($name)) {
            throw new GitException(sprintf('The remote "%s" does not exist.', $name));
        }

        return $this->getRemotes()[$name];
    }

    /**
     * @inheritdoc
     */
    public function getRemotes(): array
    {
        $result = rtrim($this->remote());
        if (empty($result)) {
            return [];
        }

        $remotes = [];
        foreach (explode(PHP_EOL, $result) as $remote) {
            $remotes[$remote]['fetch'] = $this->getRemoteUrl($remote);
            $remotes[$remote]['push'] = $this->getRemoteUrl($remote, 'push');
        }

        return $remotes;
    }

    /**
     * @inheritdoc
     */
    public function getRemoteUrl(string $remote, string $operation = 'fetch'): string
    {
        $argsAndOptions = ['get-url', $remote];

        if ($operation === 'push') {
            $argsAndOptions[] = '--push';
        }

        return rtrim($this->remote(...$argsAndOptions));
    }

    /**
     * @inheritdoc
     */
    public function add(string $filepattern, array $options = []): string
    {
        return $this->run('add', [$filepattern, $options]);
    }

    /**
     * @inheritdoc
     */
    public function apply(...$argsAndOptions): string
    {
        return $this->run('apply', $argsAndOptions);
    }

    /**
     * @inheritdoc
     */
    public function bisect(...$argsAndOptions): string
    {
        return $this->run('bisect', $argsAndOptions);
    }

    /**
     * @inheritdoc
     */
    public function branch(...$argsAndOptions): string
    {
        return $this->run('branch', $argsAndOptions);
    }

    /**
     * @inheritdoc
     */
    public function checkout(...$argsAndOptions): string
    {
        return $this->run('checkout', $argsAndOptions);
    }

    /**
     * @inheritdoc
     */
    public function cloneRepository(string $repository, array $options = []): string
    {
        $argsAndOptions = [$repository, $this->directory, $options];
        return $this->run('clone', $argsAndOptions, false);
    }

    /**
     * @inheritdoc
     */
    public function commit(...$argsAndOptions): string
    {
        if (isset($argsAndOptions[0]) && is_string($argsAndOptions[0]) && ! isset($argsAndOptions[1])) {
            $argsAndOptions[0] = [
                'm' => $argsAndOptions[0],
                'a' => true,
            ];
        }

        return $this->run('commit', $argsAndOptions);
    }

    /**
     * @inheritdoc
     */
    public function config(...$argsAndOptions): string
    {
        return $this->run('config', $argsAndOptions);
    }

    /**
     * @inheritdoc
     */
    public function diff(...$argsAndOptions): string
    {
        return $this->run('diff', $argsAndOptions);
    }

    /**
     * @inheritdoc
     */
    public function fetch(...$argsAndOptions): string
    {
        return $this->run('fetch', $argsAndOptions);
    }

    /**
     * @inheritdoc
     */
    public function grep(...$argsAndOptions): string
    {
        return $this->run('grep', $argsAndOptions);
    }

    /**
     * @inheritdoc
     */
    public function init(array $options = []): string
    {
        $argsAndOptions = [$this->directory, $options];
        return $this->run('init', $argsAndOptions, false);
    }

    /**
     * @inheritdoc
     */
    public function log(...$argsAndOptions): string
    {
        return $this->run('log', $argsAndOptions);
    }

    /**
     * @inheritdoc
     */
    public function merge(...$argsAndOptions): string
    {
        return $this->run('merge', $argsAndOptions);
    }

    /**
     * @inheritdoc
     */
    public function mv(string $source, string $destination, array $options = []): string
    {
        $argsAndOptions = [$source, $destination, $options];
        return $this->run('mv', $argsAndOptions);
    }

    /**
     * @inheritdoc
     */
    public function pull(...$argsAndOptions): string
    {
        return $this->run('pull', $argsAndOptions);
    }

    /**
     * @inheritdoc
     */
    public function push(...$argsAndOptions): string
    {
        return $this->run('push', $argsAndOptions);
    }

    /**
     * @inheritdoc
     */
    public function rebase(...$argsAndOptions): string
    {
        return $this->run('rebase', $argsAndOptions);
    }

    /**
     * @inheritdoc
     */
    public function remote(...$argsAndOptions): string
    {
        return $this->run('remote', $argsAndOptions);
    }

    /**
     * @inheritdoc
     */
    public function reset(...$argsAndOptions): string
    {
        return $this->run('reset', $argsAndOptions);
    }

    /**
     * @inheritdoc
     */
    public function rm(string $filepattern, array $options = []): string
    {
        $args = [$filepattern, $options];
        return $this->run('rm', $args);
    }

    /**
     * @inheritdoc
     */
    public function show(string $object, array $options = []): string
    {
        $args = [$object, $options];
        return $this->run('show', $args);
    }

    /**
     * @inheritdoc
     */
    public function status(...$argsAndOptions): string
    {
        return $this->run('status', $argsAndOptions);
    }

    /**
     * @inheritdoc
     */
    public function tag(...$argsAndOptions): string
    {
        return $this->run('tag', $argsAndOptions);
    }

    /**
     * @inheritdoc
     */
    public function clean(...$argsAndOptions): string
    {
        return $this->run('clean', $argsAndOptions);
    }

    /**
     * @inheritdoc
     */
    public function archive(...$argsAndOptions): string
    {
        return $this->run('archive', $argsAndOptions);
    }

    /**
     * @inheritdoc
     */
    public function tags(): GitTagsInterface
    {
        return new GitTags($this);
    }

    private function ensureAddRemoveArgsAreValid(string $name, string $url): void
    {
        if (empty($name)) {
            throw new GitException('Cannot add remote without a name.');
        }

        if (empty($url)) {
            throw new GitException('Cannot add remote without a URL.');
        }
    }
}
