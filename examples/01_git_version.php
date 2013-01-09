<?php

use GitWrapper\GitWrapper;

require_once __DIR__ . '/../vendor/autoload.php';
$git = new GitWrapper('/usr/local/git/bin/git');

print $git->version();
