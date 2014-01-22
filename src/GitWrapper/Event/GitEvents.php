<?php

namespace GitWrapper\Event;

/**
 * Static list of events thrown by this library.
 */
final class GitEvents
{
    /**
     * Event thrown prior to executing a git command.
     *
     * @var string
     */
    const GIT_PREPARE = 'git.command.prepare';

    /**
     * Event thrown when real-time output is returned from the Git command.
     *
     * @var string
     */
    const GIT_OUTPUT = 'git.command.output';

    /**
     * Event thrown after executing a succesful git command.
     *
     * @var string
     */
    const GIT_SUCCESS = 'git.command.success';

    /**
     * Event thrown after executing a unsuccesful git command.
     *
     * @var string
     */
    const GIT_ERROR = 'git.command.error';

    /**
     * Event thrown if the command is flagged to skip execution.
     *
     * @var string
     */
    const GIT_BYPASS = 'git.command.bypass';

    /**
     * Deprecated in favor of GitEvents::GIT_PREPARE.
     *
     * @var string
     *
     * @deprecated since version 1.0.0beta5
     */
    const GIT_COMMAND = 'git.command.prepare';
}
