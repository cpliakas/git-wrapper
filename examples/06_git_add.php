<?php

use GitWrapper\GitWrapper;

require_once __DIR__ . '/../vendor/autoload.php';
$wrapper = new GitWrapper();
$git = $wrapper->workingCopy('./git-wrapper');

touch('./git-wrapper/file1.txt');
touch('./git-wrapper/file2.txt');

$git
    ->add('file1.txt')
    ->add('file2.txt')
    ->commit('Committed two test files.')
    ->push();
