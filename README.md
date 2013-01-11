Overview
========

This library is a PHP wrapper around the Git command line tool.

Its purpose is to provide a semantic API that abstracts some of the challenges
of executing Git commands from within a PHP process. Specifically, this library
builds upon the Symfony framework's Process component to execute the Git command
in a way that works across platforms and uses the best-in-breed techniques
available to PHP. This library also provides an SSH wrapper script and API
method for developers to easily specify a private key other than one of the
defaults. Finally, the script transparently changes in and out of the directory
containing the working copy when executing certain Git commands so the developer
doesn't have to think about it.


Installation
============

To install the required libraries, execute the following commands in the
directory where this library is extracted.

    curl -s https://getcomposer.org/installer | php
    php composer.phar install

If curl is not available, replace the first command with the one below:

    php -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));"


Usage
=====

The following code snippets explain how to use this library. For working
examples, please view the code in the bundled examples/ directory.

Initializing The Library
------------------------

Require the autoloader that is created by Composer during installation and
instantiate the wrapper around the git binary.

    use GitWrapper\GitWrapper;

    require_once 'vendor/autoload.php';
    $wrapper = new GitWrapper();

If no argument is passed when instantiating the GitWrapper class, an attempt
will be made to find the executable automatically. Please refer to Symfony's
\Symfony\Component\Process\ExecutableFinder class for more details on how the
auto discovery process works. To explicitly specify the path to the Git binary,
pass it as the first argument.
