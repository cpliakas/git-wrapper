<?php

use GitWrapper\GitWrapper;
use GitWrapper\Command\GitClone;

require_once __DIR__ . '/../vendor/autoload.php';

$git = new GitWrapper();
$clone = new GitClone('git://github.com/cpliakas/git-wrapper.git');
$git->run($clone);
