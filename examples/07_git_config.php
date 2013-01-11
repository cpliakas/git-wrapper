<?php

use GitWrapper\GitWrapper;

require_once __DIR__ . '/../vendor/autoload.php';
$wrapper = new GitWrapper();
$git = $wrapper->workingCopy('./git-wrapper');

// Set configs just for this repository.
$git->config('user.email', 'user@example.com');

// Sets configs globally.
$git->config('user.email', 'user@example.com', array('global' => true));
