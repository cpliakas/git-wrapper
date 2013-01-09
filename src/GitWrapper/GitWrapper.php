<?php

/**
 * A PHP Git wrapper.
 *
 * @license GNU General Public License, version 3
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Copyright (c) 2013 Acquia, Inc.
 */

namespace GitWrapper;

use Symfony\Component\Process\Process;
use GitWrapper\Command\Git;
use GitWrapper\Command\GitCommandAbstract;
use GitWrapper\Exception\GitException;

/**
 * Base class for executing Git commands.
 */
class GitWrapper
{
    /**
     * @var string
     */
    protected $_gitBinary;

    /**
     * Constructs a Git object.
     *
     * @param string $git_binary
     */
    public function __construct($git_binary = null)
    {
        if (null !== $git_binary) {
            $this->setGitBinary($git_binary);
        }
    }

    /**
     * @param string $git_binary
     * @return GitWrapper
     */
    public function setGitBinary($git_binary)
    {
        $this->_gitBinary = $git_binary;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGitBinary()
    {
        return $this->_gitBinary;
    }

    /**
     * Runs the git binary using a single flag.
     *
     * @param string $flag
     *
     * @return string
     */
    public function runGit($flag)
    {
        $git = new Git();
        $git->setFlag('version');
        return $this->run($git);
    }

    /**
     * Returns the version if the installed Git client.
     *
     * @return string
     *
     * @throws GitException
     */
    public function version()
    {
        return $this->runGit('version');
    }

    /**
     * Returns the exec path.
     *
     * @return string
     *
     * @throws GitException
     */
    public function execPath()
    {
        return $this->runGit('exec-path');
    }

    /**
     * Returns the html path.
     *
     * @return string
     *
     * @throws GitException
     */
    public function htmlPath()
    {
        return $this->runGit('html-path');
    }

    /**
     * Returns the man path.
     *
     * @return string
     *
     * @throws GitException
     */
    public function manPath()
    {
        return $this->runGit('man-path');
    }

    /**
     * Returns the info path.
     *
     * @return string
     *
     * @throws GitException
     */
    public function infoPath()
    {
        return $this->runGit('nfo-path');
    }

    /**
     * Runs a Git command.
     *
     * In order to modify the Git process prior
     *
     * @param GitCommandAbstract $command
     *
     * @return string
     */
    public function run(GitCommandAbstract $command)
    {
        $git_binary = $this->_gitBinary;
        if (null === $this->_gitBinary) {
            $finder = new ExecutableFinder();
            if ($git_binary = $finder->find('git')) {
                $this->_gitBinary= $git_binary;
            } else {
                throw new \RuntimeException('Unable to find the Git executable.');
            }
        }

        try {
            $commandline = rtrim(escapeshellcmd($git_binary) . ' ' . $command->getCommandLine());
            $process = new Process($commandline);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new \RuntimeException($process->getErrorOutput());
            }
        } catch (\RuntimeException $e) {
            throw new GitException($e->getMessage());
        }

        return $process->getOutput();
    }
}
