<?php

use GitWrapper\GitWrapper;
use GitWrapper\Command\GitCommit;

require_once __DIR__ . '/../vendor/autoload.php';
$git = new GitWrapper();

$commit = new GitCommit('./git-wrapper', 'Fixed #123: Added feature X.');
$git->run($commit);
