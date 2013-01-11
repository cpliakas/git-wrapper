<?php

use GitWrapper\GitWrapper;

require_once __DIR__ . '/../vendor/autoload.php';
$wrapper = new GitWrapper();

$git = $wrapper->workingCopy('./git-wrapper');
$git->commit('Fixed #123: Added feature X.');
