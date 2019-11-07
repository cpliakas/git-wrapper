<?php

declare(strict_types=1);

namespace GitWrapper\Event;

/**
 * Event thrown if the command is flagged to skip execution.
 */
final class GitBypassEvent extends AbstractGitEvent
{
}
