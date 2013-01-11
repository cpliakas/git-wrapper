<?php

use GitWrapper\GitWrapper;
use GitWrapper\Command\GitClone;

require_once __DIR__ . '/../vendor/autoload.php';
$wrapper = new GitWrapper();

$git = $wrapper->workingCopy('git-wrapper');
$git->clone('git://github.com/cpliakas/git-wrapper.git');
