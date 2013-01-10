<?php

use GitWrapper\GitWrapper;
use GitWrapper\Command\GitPush;

require_once __DIR__ . '/../vendor/autoload.php';
$git = new GitWrapper();

$push = new GitPush('./git-wrapper');
$git->run($push);
