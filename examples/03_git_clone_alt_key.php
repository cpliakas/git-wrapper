<?php

use GitWrapper\GitWrapper;

require_once __DIR__ . '/../vendor/autoload.php';
$wrapper = new GitWrapper();

$wrapper->setPrivateKey('/path/to/private/key');

$git = $wrapper->workingCopy('./git-wrapper');
$git->clone('git@github.com:cpliakas/git-wrapper.git');
