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
 * Class that models arbirarty Git commands.
 */
class Git extends GitCommandAbstract
{
    /**
     * The raw command containing the Git options and arguments excluding the
     * Git binary.
     *
     * @var string
     */
    protected $_commandLine;

    /**
     * Constructs a Git object.
     *
     * @param string $command_line
     *   The raw command containing the Git options and arguments excluding the
     *   Git binary. Defaults to an empty string.
     */
    public function __construct($command_line = '')
    {
        $this->_commandLine = $command_line;
    }

    /**
     * Implements GitCommandAbstract::getCommand().
     *
     * In this instance, the whole command line is returned and not jsut the
     * first argument.
     */
    public function getCommand()
    {
        return $this->_commandLine;
    }
}
