<?php

// Ensure the dependencies have been downloaded.
$autoload_file = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload_file)) {
    throw new RuntimeException('Install dependencies to run test suite.');
}
require_once $autoload_file;

// PSR-0 autoloader for test classes.
spl_autoload_register(function ($class) {
    $filename = __DIR__ . '/' . str_replace("\\", DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists($filename)) {
        require $filename;
    }
});
