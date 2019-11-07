<?php

/**
 * A PHP Git wrapper.
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License that is bundled
 * with this package in the "LICENSE" file. It is also available for download at
 * http://www.gnu.org/licenses/gpl-3.0.txt.
 *
 * @license    http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright  Copyright (c) 2013 Acquia, Inc.
 */

namespace GitWrapper\Command;

/**
 * Base class extended by all Git command classes.
 */
abstract class GitCommandAbstract
{
    /**
     * @var array
     */
    protected $_options = array();

    /**
     * @var array
     */
    protected $_args = array();

    /**
     * A cached copy of the current working directory, for internal use only.
     *
     * @var string
     */
    protected $_currentDir;

    /**
     * The directory containing the working copy. If this variable is set, then
     * the process will change into this directory while the Git command is
     * being run.
     *
     * @var string
     */
    protected $_directory;

    /**
     * Builds the options text.
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
     * Sets an option.
     *
     * Option names are passed as-is to the command line, whereas the values are
     * sanitized via the escapeshellarg() function.
     *
     * @param string $option The option name, e.g. "branch", "q".
     * @param string|true $value The option's value.
     * @reutrn GitCommandAbstract
     */
    public function setOption($option, $value)
    {
        $this->_options[$option] = $value;
        return $this;
    }

    /**
     * Sets multiple options.
     *
     * @param array $options An associative array of options.
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
     * Sets a flag.
     *
     * @param string $flag The flag / option name, e.g. "q".
     * @reutrn GitCommandAbstract
     *
     * @see GitCommandAbstract::setOption()
     */
    public function setFlag($option)
    {
        return $this->setOption($option, true);
    }

    /**
     * @param string $option The option name, e.g. "branch", "q".
     * @return string|null $option
     */
    public function getOption($option)
    {
        return (isset($this->_options[$option])) ? $this->_options[$option] : null;
    }

    /**
     * @param string $option The option name, e.g. "branch", "q".
     * @return string|null $option
     */
    public function removeOption($option)
    {
        unset($this->_options[$option]);
        return $this;
    }

    /**
     * Adds an argument to the command.
     *
     * @param string $arg The argument, e.g. the repo URL, directory, etc.
     * @reutrn GitCommandAbstract
     */
    public function addArgument($arg)
    {
        $this->_args[] = $arg;
        return $this;
    }

    /**
     * Returns the Git command, e.g. "clone" or "push".
     *
     * @return string
     */
    abstract public function getCommand();

    /**
     * Hook invoked prior to the Process object being instantiated.
     *
     * If the working copy is set, change into that directory for the time that
     * the Git command is run.
     *
     * @throws \RuntimeException
     */
    public function preCommandRun()
    {
        if ($this->_directory !== null) {
            $this->_currentDir = getcwd();
            if (!@chdir($this->_directory)) {
                throw new \RuntimeException('Error changing directories into the working copy.');
            }
        }
    }

    /**
     * Returns the event name used by the dispatcher.
     *
     * @return string
     */
    public function getEventName()
    {
        $event_name = 'git.';
        $command = $this->getCommand();
        $event_name .= ($command) ? $command : 'command';
        return $event_name;
    }

    /**
     * Renders the command that will be executed.
     *
     * @return string
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

    /**
     * Hook invoked prior to the Process object being instantiated.
     *
     * Changes back to the current working directory.
     */
    public function postCommandRun()
    {
        if ($this->_directory !== null) {
            @chdir($this->_currentDir);
        }
    }
}