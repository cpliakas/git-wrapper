<?php

use GitWrapper\GitWrapper;
use GitWrapper\Command\GitClone;

require_once __DIR__ . '/../vendor/autoload.php';
$git = new GitWrapper();

$listener = new GitSSHListener('/path/to/private/key');
$git->getDispatcher()->addListener(GitEvents::GIT_CLONE, array($listener, 'onGitCommand'));

$clone = new GitClone('git@github.com:cpliakas/git-wrapper.git');
$git->run($clone);
