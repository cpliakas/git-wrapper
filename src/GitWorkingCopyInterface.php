<?php
declare(strict_types=1);

namespace GitWrapper;


/**
 * Interacts with a working copy.
 * All commands executed via an instance of this class act on the working copy
 * that is set through the constructor.
 */
interface GitWorkingCopyInterface
{
    public function getWrapper(): GitWrapperInterface;

    public function getDirectory(): string;

    public function setCloned(bool $cloned): void;

    /**
     * Checks whether a repository has already been cloned to this directory.
     * If the flag is not set, test if it looks like we're at a git directory.
     */
    public function isCloned(): bool;

    /**
     * Runs a Git command and returns the output.
     *
     * @param mixed[] $argsAndOptions
     */
    public function run(string $command, array $argsAndOptions = [], bool $setDirectory = true): string;

    /**
     * Returns the output of a `git status -s` command.
     */
    public function getStatus(): string;

    /**
     * Returns true if there are changes to commit.
     */
    public function hasChanges(): bool;

    /**
     * Returns whether HEAD has a remote tracking branch.
     */
    public function isTracking(): bool;

    /**
     * Returns whether HEAD is up-to-date with its remote tracking branch.
     */
    public function isUpToDate(): bool;

    /**
     * Returns whether HEAD is ahead of its remote tracking branch.
     * If this returns true it means that commits are present locally which have
     * not yet been pushed to the remote.
     */
    public function isAhead(): bool;

    /**
     * Returns whether HEAD is behind its remote tracking branch.
     * If this returns true it means that a pull is needed to bring the branch
     * up-to-date with the remote.
     */
    public function isBehind(): bool;

    /**
     * Returns whether HEAD needs to be merged with its remote tracking branch.
     * If this returns true it means that HEAD has diverged from its remote
     * tracking branch; new commits are present locally as well as on the
     * remote.
     */
    public function needsMerge(): bool;

    /**
     * Returns a GitBranches object containing information on the repository's
     * branches.
     */
    public function getBranches(): GitBranchesInterface;

    /**
     * This is synonymous with `git push origin tag v1.2.3`.
     *
     * @param string $repository The destination of the push operation, which is either a URL or name of
     *   the remote. Defaults to "origin".
     * @param mixed[] $options
     */
    public function pushTag(string $tag, string $repository = 'origin', array $options = []): string;

    /**
     * This is synonymous with `git push --tags origin`.
     *
     * @param string $repository The destination of the push operation, which is either a URL or name of the remote.
     * @param mixed[] $options
     */
    public function pushTags(string $repository = 'origin', array $options = []): string;

    /**
     * Fetches all remotes.
     * This is synonymous with `git fetch --all`.
     *
     * @param mixed[] $options
     */
    public function fetchAll(array $options = []): string;

    /**
     * Create a new branch and check it out.
     * This is synonymous with `git checkout -b`.
     *
     * @param mixed[] $options
     */
    public function checkoutNewBranch(string $branch, array $options = []): string;

    /**
     * Adds a remote to the repository.
     *
     * @param mixed[] $options An associative array of options, with the following keys:
     *   - -f: Boolean, set to true to run git fetch immediately after the
     *     remote is set up. Defaults to false.
     *   - --tags: Boolean. By default only the tags from the fetched branches
     *     are imported when git fetch is run. Set this to true to import every
     *     tag from the remote repository. Defaults to false.
     *   - --no-tags: Boolean, when set to true, git fetch does not import tags
     *     from the remote repository. Defaults to false.
     *   - -t: Optional array of branch names to track. If left empty, all
     *     branches will be tracked.
     *   - -m: Optional name of the master branch to track. This will set up a
     *     symbolic ref 'refs/remotes/<name>/HEAD which points at the specified
     *     master branch on the remote. When omitted, no symbolic ref will be
     *     created.
     */
    public function addRemote(string $name, string $url, array $options = []): string;

    public function removeRemote(string $name): string;

    public function hasRemote(string $name): bool;

    /**
     * @return string[] An associative array with the following keys:
     *  - fetch: the fetch URL.
     *  - push: the push URL.
     */
    public function getRemote(string $name): array;

    /**
     * @return mixed[] An associative array, keyed by remote name, containing an associative array with keys:
     *  - fetch: the fetch URL.
     *  - push: the push URL.
     */
    public function getRemotes(): array;

    /**
     * Returns the fetch or push URL of a given remote.
     *
     * @param string $operation The operation for which to return the remote. Can be either 'fetch' or 'push'.
     */
    public function getRemoteUrl(string $remote, string $operation = 'fetch'): string;

    /**
     * @code $git->add('some/file.txt');
     * @param mixed[] $options
     */
    public function add(string $filepattern, array $options = []): string;

    /**
     * @code $git->apply('the/file/to/read/the/patch/from');
     * @param mixed ...$argsAndOptions
     */
    public function apply(...$argsAndOptions): string;

    /**
     * Find by binary search the change that introduced a bug.
     * @code $git->bisect('good', '2.6.13-rc2');
     * $git->bisect('view', ['stat' => true]);
     *
     * @param mixed ...$argsAndOptions
     */
    public function bisect(...$argsAndOptions): string;

    /**
     * @code $git->branch('my2.6.14', 'v2.6.14');
     * $git->branch('origin/html', 'origin/man', ['d' => true, 'r' => 'origin/todo']);
     *
     * @param mixed ...$argsAndOptions
     */
    public function branch(...$argsAndOptions): string;

    /**
     * @code $git->checkout('new-branch', ['b' => true]);
     * @param mixed ...$argsAndOptions
     */
    public function checkout(...$argsAndOptions): string;

    /**
     * Executes a `git clone` command.
     * @code $git->cloneRepository('git://github.com/cpliakas/git-wrapper.git');
     *
     * @param mixed[] $options
     */
    public function cloneRepository(string $repository, array $options = []): string;

    /**
     * Record changes to the repository. If only one argument is passed, it is  assumed to be the commit message.
     * Therefore `$git->commit('Message');` yields a `git commit -am "Message"` command.
     * @code $git->commit('My commit message');
     * $git->commit('Makefile', ['m' => 'My commit message']);
     *
     * @param mixed ...$argsAndOptions
     */
    public function commit(...$argsAndOptions): string;

    /**
     * @code $git->config('user.email', 'opensource@chrispliakas.com');
     * $git->config('user.name', 'Chris Pliakas');
     *
     * @param mixed ...$argsAndOptions
     */
    public function config(...$argsAndOptions): string;

    /**
     * @code $git->diff();
     * $git->diff('topic', 'master');
     *
     * @param mixed ...$argsAndOptions
     */
    public function diff(...$argsAndOptions): string;

    /**
     * @code $git->fetch('origin');
     * $git->fetch(['all' => true]);
     *
     * @param mixed ...$argsAndOptions
     */
    public function fetch(...$argsAndOptions): string;

    /**
     * Print lines matching a pattern.
     * @code $git->grep('time_t', '--', '*.[ch]');
     *
     * @param mixed ...$argsAndOptions
     */
    public function grep(...$argsAndOptions): string;

    /**
     * Create an empty git repository or reinitialize an existing one.
     * @code $git->init(['bare' => true]);
     *
     * @param mixed[] $options
     */
    public function init(array $options = []): string;

    /**
     * @code $git->log(['no-merges' => true]);
     * $git->log('v2.6.12..', 'include/scsi', 'drivers/scsi');
     *
     * @param mixed ...$argsAndOptions
     */
    public function log(...$argsAndOptions): string;

    /**
     * @code $git->merge('fixes', 'enhancements');
     * @param mixed ...$argsAndOptions
     */
    public function merge(...$argsAndOptions): string;

    /**
     * @code $git->mv('orig.txt', 'dest.txt');
     * @param mixed[] $options
     */
    public function mv(string $source, string $destination, array $options = []): string;

    /**
     * @code $git->pull('upstream', 'master');
     * @param mixed ...$argsAndOptions
     */
    public function pull(...$argsAndOptions): string;

    /**
     * @code $git->push('upstream', 'master');
     * @param mixed ...$argsAndOptions
     */
    public function push(...$argsAndOptions): string;

    /**
     * @code $git->rebase('subsystem@{1}', ['onto' => 'subsystem']);
     *
     * @param mixed ...$argsAndOptions
     */
    public function rebase(...$argsAndOptions): string;

    /**
     * @code $git->remote('add', 'upstream', 'git://github.com/cpliakas/git-wrapper.git');
     * @param mixed ...$argsAndOptions
     */
    public function remote(...$argsAndOptions): string;

    /**
     * @code $git->reset(['hard' => true]);
     * @param mixed ...$argsAndOptions
     */
    public function reset(...$argsAndOptions): string;

    /**
     * @code $git->rm('oldfile.txt');
     * @param mixed[] $options
     */
    public function rm(string $filepattern, array $options = []): string;

    /**
     * @code $git->show('v1.0.0');
     * @param mixed[] $options
     */
    public function show(string $object, array $options = []): string;

    /**
     * @code $git->status(['s' => true]);
     * @param mixed ...$argsAndOptions
     */
    public function status(...$argsAndOptions): string;

    /**
     * @code $git->tag('v1.0.0');
     * @param mixed ...$argsAndOptions
     */
    public function tag(...$argsAndOptions): string;

    /**
     * @code $git->clean('-d', '-f');
     * @param mixed ...$argsAndOptions
     */
    public function clean(...$argsAndOptions): string;

    /**
     * @code $git->archive('HEAD', ['o' => '/path/to/archive']);
     * @param mixed ...$argsAndOptions
     */
    public function archive(...$argsAndOptions): string;

    /**
     * Returns a GitTags object containing  information on the repository's tags.
     */
    public function tags(): GitTagsInterface;
}