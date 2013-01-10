<?php

use GitWrapper\GitWrapper;

require_once __DIR__ . '/../vendor/autoload.php';
$git = new GitWrapper();
$git->setPrivateKey('/path/to/private/key');

$working_copy = $git->workingCopy('./git-wrapper');
$working_copy->clone('git@github.com:cpliakas/git-wrapper.git');
