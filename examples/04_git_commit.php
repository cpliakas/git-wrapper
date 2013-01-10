<?php

use GitWrapper\GitWrapper;

require_once __DIR__ . '/../vendor/autoload.php';
$git = new GitWrapper();

$git
    ->workingCopy('./git-wrapper')
    ->commit('Fixed #123: Added feature X.');

