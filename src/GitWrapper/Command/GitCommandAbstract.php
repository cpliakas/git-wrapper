<?php

/**
 * A PHP wrapper around the Git command line utility.
 *
 * @license GNU General Public License, version 3
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @see https://github.com/cpliakas/git-wrapper
 * @copyright Copyright (c) 2013 Acquia, Inc.
 */

namespace GitWrapper\Command;

/**
 * Base class extended by all Git command classes.
 */
abstract class GitCommandAbstract
{
    /**
     * Path to the directory containing the working copy. If this variable is
     * set, then the process will change into this directory while the Git
     * command is being run.
     *
     * @var string|null
     */
    protected $_directory;

    /**
     * An associative array of command line options and flags.
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Command line arguments passed to the Git command.
     *
     * @var array
     */
    protected $_args = array();

    /**
     * Gets the path to the directory containing the working copy.
     *
     * @return string|null
     *   The path, null if no path is set.
     */
    public function getDirectory()
    {
        return $this->_directory;
    }

    /**
     * Builds the command line options for use in the Git command.
     *
     * @return string
     */
    public function buildOptions()
    {
        $options = array();
        foreach ($this->_options as $option => $value) {
            $prefix = (strlen($option) != 1) ? '--' : '-';
            $rendered = $prefix . $option;
            if ($value !== true) {
                $rendered .= ('--' == $prefix) ? '=' : ' ';
                $rendered .= escapeshellarg($value);
            }
            $options[] = $rendered;
        }
        return join(' ', $options);
    }

    /**
     * Sets a command line option.
     *
     * Option names are passed as-is to the command line, whereas the values are
     * sanitized via the escapeshellarg() function.
     *
     * @param string $option
     *   The option name, e.g. "branch", "q".
     * @param string|true $value
     *   The option's value, pass true if the options is a flag.
     *
     * @reutrn GitCommandAbstract
     */
    public function setOption($option, $value)
    {
        $this->_options[$option] = $value;
        return $this;
    }

    /**
     * Sets multiple command line options.
     *
     * @param array $options
     *   An associative array of command line options.
     *
     * @reutrn GitCommandAbstract
     */
    public function setOptions(array $options)
    {
        foreach ($options as $option => $value) {
            $this->setOption($option, $value);
        }
        return $this;
    }

    /**
     * Sets a command line flag.
     *
     * @param string $flag
     *   The flag name, e.g. "q", "a".
     *
     * @reutrn GitCommandAbstract
     *
     * @see GitCommandAbstract::setOption()
     */
    public function setFlag($option)
    {
        return $this->setOption($option, true);
    }

    /**
     * Gets a command line option.
     *
     * @param string $option
     *   The option name, e.g. "branch", "q".
     * @param mixed $default
     *   Value that is returned if the option is not set, defaults to null.
     *
     * @return mixed
     */
    public function getOption($option, $default = null)
    {
        return (isset($this->_options[$option])) ? $this->_options[$option] : $default;
    }

    /**
     * Unsets a command line option.
     *
     * @param string $option
     *   The option name, e.g. "branch", "q".
     *
     * @return GitCommandAbstract
     */
    public function unsetOption($option)
    {
        unset($this->_options[$option]);
        return $this;
    }

    /**
     * Adds a command line argument passed to the Git command.
     *
     * @param string $arg
     *   The argument, e.g. the repo URL, directory, etc.
     *
     * @reutrn GitCommandAbstract
     */
    public function addArgument($arg)
    {
        $this->_args[] = $arg;
        return $this;
    }

    /**
     * Properly escapes file patterns that are passed as arguments.
     *
     * This method only escape paths with files that have extensions. If the
     * path does not have an extension, there is no need to excape the periods.
     *
     * This is most useful for Git "add" and "rm" commands.
     *
     * @param string $filepattern
     *   The file pattern being escaped.
     *
     * @return string
     */
    public function escapeFilepattern($filepattern)
    {
        $path_info = pathinfo($filepattern);
        if (isset($path_info['extension'])) {
            $path_info['basename'] = str_replace('.', '\\.', $path_info['basename']);
        }
        return $path_info['dirname'] . DIRECTORY_SEPARATOR . $path_info['basename'];
    }

    /**
     * Returns the Git command passed as the first argument on the command line,
     * e.g. "clone", "push".
     *
     * @return string
     */
    abstract public function getCommand();

    /**
     * Renders the arguments and options for the Git command.
     *
     * @return string
     *
     * @see GitCommandAbstract::getCommand()
     * @see GitCommandAbstract::buildOptions()
     */
    public function getCommandLine()
    {
        $command = array(
            $this->getCommand(),
            $this->buildOptions(),
            join(' ', array_map('escapeshellarg', $this->_args)),
        );
        return join(' ', array_filter($command));
    }
}
