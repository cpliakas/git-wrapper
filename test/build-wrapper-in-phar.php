<?php

$phar = new \Phar('wrapper-in.phar');
$phar->startBuffering();
$phar->addFile('../bin/git-ssh-wrapper.sh', 'git-ssh-wrapper.sh');
$phar->stopBuffering();
