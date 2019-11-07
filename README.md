# Overview

[![Build Status](https://travis-ci.org/cpliakas/git-wrapper.svg?branch=master)](https://travis-ci.org/cpliakas/git-wrapper)
[![Code Coverage](https://scrutinizer-ci.com/g/cpliakas/git-wrapper/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/cpliakas/git-wrapper/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/cpliakas/git-wrapper/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/cpliakas/git-wrapper/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/cpliakas/git-wrapper.svg)](https://packagist.org/packages/cpliakas/git-wrapper)
[![Latest Stable Version](https://img.shields.io/packagist/v/cpliakas/git-wrapper.svg)](https://packagist.org/packages/cpliakas/git-wrapper)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/cpliakas/git-wrapper/master/LICENSE)

This library is a PHP wrapper around the Git command line tool.

Its purpose is to provide a readable API that abstracts some of the challenges
of executing Git commands from within a PHP process. Specifically, this library
builds upon the Symfony framework's Process component to execute the Git command
in a way that works across platforms and uses the best-in-breed techniques
available to PHP. This library also provides an SSH wrapper script and API
method for developers to easily specify a private key other than one of the
defaults by using the technique in [this thread on StackOverflow](http://stackoverflow.com/a/3500308/870667).
Finally, various commands are expected to be executed in the directory
containing the working copy. Although this a fairly simple challenge to
overcome, the library handles this transparently so the developer doesn't have
to think about it.

## Usage

```php
use GitWrapper\GitWrapper;

// Initialize the library. If the path to the Git binary is not passed as
// the first argument when instantiating GitWrapper, it is auto-discovered.
require_once 'vendor/autoload.php';
$wrapper = new GitWrapper();

// Optionally specify a private key other than one of the defaults.
$wrapper->setPrivateKey('/path/to/private/key');

// Clone a repo into `/path/to/working/copy`, get a working copy object.
$git = $wrapper->clone('git://github.com/cpliakas/git-wrapper.git', '/path/to/working/copy');

// Create a file in the working copy.
touch('/path/to/working/copy/text.txt');

// Add it, commit it, and push the change.
$git
    ->add('test.txt')
    ->commit('Added the test.txt file as per the examples.')
    ->push();

// Render the output.
print $git->getOutput();

// Stream output of subsequent Git commands in real time to STDOUT and STDERR.
$wrapper->streamOutput();

// Execute an arbitrary git command.
// The following is synonymous with `git config -l`
$wrapper->git('config -l');
```

All command methods adhere to the following paradigm:

```php
$git->command($arg1, $arg2, ..., $options);
```

Replace `command` with the Git command being executed, e.g. `checkout`, `push`,
etc. The `$arg*` parameters are a variable number of arguments as they would be
passed to the Git command line tool. `$options` is an optional array of command
line options in the following format:

```php
$options = array(
    'verbose' => true,   // Passes the "--verbose" flag.
    't' => 'my-branch',  // Passes the "-t my-branch" option.
);
```

#### Logging

Use the logger listener with [PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md)
compatible loggers such as [Monolog](https://github.com/Seldaek/monolog) to log
commands that are executed.

```php

use GitWrapper\Event\GitLoggerListener;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Log to a file named "git.log"
$log = new Logger('git');
$log->pushHandler(new StreamHandler('git.log', Logger::DEBUG));

// Instantiate the listener, add the logger to it, and register it.
$listener = new GitLoggerListener($log);
$wrapper->addLoggerListener($listener);

$git = $wrapper->clone('git://github.com/cpliakas/git-wrapper.git', '/path/to/working/copy');

// The "git.log" file now has info about the command that was executed above.

```

## Installation

Git Wrapper can be installed with [Composer](http://getcomposer.org) by adding
the library as a dependency to your composer.json file.

```json
{
    "require": {
        "cpliakas/git-wrapper": "~1.0"
    }
}
```

Please refer to [Composer's documentation](https://github.com/composer/composer/blob/master/doc/00-intro.md#introduction)
for installation and usage instructions.

## Gotchas

There are a few "gotchas" that are out of scope for this library to solve but
might prevent a successful implementation of running Git via PHP. The following
is an incomplete list of challenges that are often encountered when executing
Git from PHP.

### Missing HOME Environment Variable

Sometimes the `HOME` environment variable is not set in the Git process that is
spawned by PHP. This will cause many Git operations to fail. It is advisable to
set the `HOME` environment variable to a path outside of the document root that
the web server has write access to. Note that this environment variable is only
set for the process running Git and NOT the PHP process that is spawns it.

```php
$wrapper->setEnvVar('HOME', '/path/to/a/private/writable/dir');
```

It is important that the storage is persistent as the ~/.gitconfig file will be
written to this location. See the following "gotcha" for why this is important.

### Missing Identity And Configurations

Many repositories require that a name and email address are specified. This data
is set by running `git config [name] [value]` on the command line, and the
configurations are usually stored in the `~/.gitconfig file`. When executing Git
via PHP, however, the process might have a different home directory than the
user who normally runs git via the command line. Therefore no identity is sent
to the repository, and it will likely throw an error.

```php
// Set configuration options globally.
$wrapper->git('config --global user.name "User name"');
$wrapper->git('config --global user.email user@example.com');

// Set configuration options per repository.
$git
    ->config('user.name', 'User name')
    ->config('user.email', 'user@example.com');
```

### Commits To Repositories With No Changes

Running `git commit` on a repository with no changes returns no output but exits
with a status of 1. Therefore the library will throw a `GitException` since it
correctly detected an error. It is advisable to check whether a working copy has
any changes prior to running the commit operation in order to prevent unwanted
exceptions.

```php
if ($git->hasChanges()) {
    $git->commit('Committed the changes.');
}
```

### Permissions Of The GIT_SSH Wrapper Script

On checkout, the bin/git-ssh-wrapper.sh script should be executable. If it is
not, git commands with fail if a non-default private key is specified.

    $> chmod 0755 ./bin/git-ssh-wrapper.sh

## For Developers

Refer to [PHP Project Starter's documentation](https://github.com/cpliakas/php-project-starter#using-apache-ant)
for the Apache Ant targets supported by this project.
