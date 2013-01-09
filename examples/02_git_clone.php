<?php

use GitWrapper\GitWrapper;
use GitWrapper\Command\GitClone;

require_once __DIR__ . '/../vendor/autoload.php';
$git = new GitWrapper('/usr/local/git/bin/git');

$clone = new GitClone();
$git->run($clone);
