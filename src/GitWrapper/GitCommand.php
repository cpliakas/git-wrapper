<?php

/**
 * A PHP wrapper around the Git command line utility.
 *
 * @license GNU General Public License, version 3
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @see https://github.com/cpliakas/git-wrapper
 * @copyright Copyright (c) 2013 Acquia, Inc.
 */

namespace GitWrapper;

/**
 * Base class extended by all Git command classes.
 */
class GitCommand
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
     * The command being run, e.g. "clone", "commit", etc.
     *
     * @var string
     */
    protected $_command = '';

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
     * Whether command execution should be bypassed.
     *
     * @var boolean
     */
    protected $_bypass = false;

    /**
     * Constructs a GitCommand object.
     *
     * Use GitCommand::getInstance() as the factory method for this class.
     *
     * @param array $args
     *   The arguments passed to GitCommand::getInstance().
     */
    protected function __construct($args)
    {
        if ($args) {

            // The first argument is the command.
            $this->_command = array_shift($args);

            // If the last element is an array, set it as the options.
            $options = end($args);
            if (is_array($options)) {
                $this->setOptions($options);
                array_pop($args);
            }

            // Pass all other method arguments as the Git command arguments.
            foreach ($args as $arg) {
                $this->addArgument($arg);
            }
        }
    }

    /**
     * Constructs a GitCommand object.
     *
     * Accepts a variable number of arguments to model the arguments passed to
     * the Git command line utility. If the last argument is an array, it is
     * passed as the command options.
     *
     * @param string $command
     *   The Git command being run, e.g. "clone", "commit", etc.
     * @param string ...
     *   Zero or more arguments passed to the Git command.
     * @param array $options
     *   An optional array of arguments to pass to the command.
     *
     * @return GitCommand
     */
    static public function getInstance()
    {
        $args = func_get_args();
        return new static($args);
    }

    /**
     * Returns Git command being run, e.g. "clone", "commit", etc.
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->_command;
    }

    /**
     * Sets the path to the directory containing the working copy.
     *
     * @param string $directory
     *   The path to the directory containing the working copy.
     *
     * @return GitCommand
     */
    public function setDirectory($directory)
    {
        $this->_directory = $directory;
        return $this;
    }

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
     * A boolean flagging whether to skip running the command.
     *
     * @param boolean $bypass
     *   Whether to bypass execution of the command. The parameter defaults to
     *   true for code readability, however the default behavior of this class
     *   is to run the command.
     *
     * @return GitCommand
     */
    public function bypass($bypass = true)
    {
        $this->_bypass = (bool) $bypass;
        return $this;
    }

    /**
     * Returns true if the Git command should be run.
     *
     * The return value is the boolean opposite $this->_bypass. Although this
     * seems complex, it makes the code more readable when checking whether the
     * command should be run or not.
     *
     * @return boolean
     *   If true, the command should be run.
     */
    public function notBypassed()
    {
        return !$this->_bypass;
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
     * @reutrn GitCommand
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
     * @reutrn GitCommand
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
     * @reutrn GitCommand
     *
     * @see GitCommand::setOption()
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
     * @return GitCommand
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
     * @reutrn GitCommand
     */
    public function addArgument($arg)
    {
        $this->_args[] = $arg;
        return $this;
    }

    /**
     * Renders the arguments and options for the Git command.
     *
     * @return string
     *
     * @see GitCommand::getCommand()
     * @see GitCommand::buildOptions()
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
