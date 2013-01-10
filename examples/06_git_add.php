<?php

use GitWrapper\GitWrapper;
use GitWrapper\Command\GitAdd;

require_once __DIR__ . '/../vendor/autoload.php';
$git = new GitWrapper();
$working_copy = $git->workingCopy('./git-wrapper');

touch('./git-wrapper/file1.txt');
touch('./git-wrapper/file2.txt');

$working_copy
    ->add('file1.txt')
    ->add('file2.txt')
    ->commit('Committed two test files.')
    ->push();
