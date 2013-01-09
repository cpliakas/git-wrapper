<?php

/**
 * A PHP Git wrapper.
 *
 * @license GNU General Public License, version 3
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Copyright (c) 2013 Acquia, Inc.
 */

namespace GitWrapper\EventListener;

use GitWrapper\Event\GitEvent;

/**
 * Event listener that wraps Git calls through an SSH wrapper script in order to
 * more easily specify which private keys are used to authenticate to different
 * repositories.
 *
 * @see http://stackoverflow.com/a/3500308/870667
 */
class GitSSHListener
{
    /**
     * @var string
     */
    protected $_privateKey;

    /**
     * @var int
     */
    protected $_port;

    /**
     * The path to the GIT_SSH wrapper script.
     *
     * @var string
     */
    protected $_wrapper;

    /**
     * Constructs a GitSSHListener object.
     *
     * @param string $private_key path to the private key.
     * @param int $port The SSH port
     */
    public function __construct($private_key, $port = '22')
    {
        $this->_privateKey = $private_key;
        $this->_port = $port;

        $this->_wrapper = __DIR__ . '/../../../bin/git-ssh-wrapper.sh';
    }

    /**
     * Sets the path to the GIT_SSH wrapper script.
     *
     * @param string $wrapper
     * @return GitSSHListener
     */
    public function setWrapper($wrapper)
    {
        $this->_wrapper = $wrapper;
        return $this;
    }

    /**
     * Event handler, sets the environment variables used to run SSH through the
     * specified wrapper script.
     *
     * @param GitEvent $event
     */
    public function onGitCommand(GitEvent $event)
    {
        $env = array(
            'GIT_SSH' => $this->_wrapper,
            'GIT_SSH_KEY' => $this->_privateKey,
            'GIT_SSH_PORT' => $this->_port,
        );
        $process = $event->getProcess()->setEnv($env);
    }
}
