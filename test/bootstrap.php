<?php

// Ensure the dependencies have been downloaded.
$autoload_file = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload_file)) {
    throw new RuntimeException('Install dependencies to run test suite.');
}
require_once $autoload_file;
