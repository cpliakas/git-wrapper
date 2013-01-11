Overview
========

This library is a PHP wrapper around the Git command line tool.

Its purpose is to provide a readable API that abstracts some of the challenges
of executing Git commands from within a PHP process. Specifically, this library
builds upon the Symfony framework's Process component to execute the Git command
in a way that works across platforms and uses the best-in-breed techniques
available to PHP. This library also provides an SSH wrapper script and API
method for developers to easily specify a private key other than one of the
defaults.

Installation
============

To install the required libraries, execute the following commands in the
directory where this library is extracted.

    curl -s https://getcomposer.org/installer | php
    php composer.phar install

If curl is not available, replace the first command with the one below:

    php -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));"

Please refer to the [Composer](http://getcomposer.org/) tool's
[installation documentation](http://getcomposer.org/doc/00-intro.md#installation-nix)
for more information.

Usage
=====

The following code snippets explain how to use this library. For working
examples, please view the code in the bundled `examples/` directory.

Initializing The Library
------------------------

Require the autoloader that is created by Composer during installation and
instantiate the wrapper around the git binary.

    use GitWrapper\GitWrapper;

    require_once 'vendor/autoload.php';
    $wrapper = new GitWrapper();

If no argument is passed when instantiating the `GitWrapper` class, an attempt
will be made to find the executable automatically. Please refer to Symfony's
`\Symfony\Component\Process\ExecutableFinder` class for more details on how the
auto discovery process works. To explicitly specify the path to the Git binary,
pass it as the first argument.

Interacting With The Working Copy
---------------------------------

Specify the working copy's directory by passing it as the first argument to the
`GitWrapper::workingCopy()` method. It is recommended that the return object is
stored in a variable named `$git` for code readability.

    $git = $wrapper->('./path/to/working/copy');

Cloning A Repository
--------------------

The repository will be checked out to the directory passed as the first argument
to the `GitWrapper::workingCopy()` method illustrated above.

    $git->clone('git://github.com/cpliakas/git-wrapper.git');

Adding Files, Committing Changes, and Pushing Commits
-----------------------------------------------------

The Git Wrapper library provides a fluent interface so that commands can be
chained together in logical groupings.

    // Create a file in the working copy.
    touch('./path/to/working/copy/text.txt');

    // Add the file, commit the change, and push the commit.
    $git
        ->add('test.txt')
        ->commit('Added the test.txt file as per the examples.')
        ->push();

As is the Git command line tool, the repository and refspec can be passed as the
first and second arguments respectively to the `GitWorkingCopy::push()` method.

Specifying An Alternate Private Key
-----------------------------------

Depending on the web server's configuration, the developer might not have the
required permissions to modify the SSH configuration file in order to set the
appropriate keys. This library comes with an SSH wrapper script that is used
automatically when a private key is set via the `GitWrapper::setPrivateKey()`
method.

    $wrapper->setPrivateKey('/path/to/private/key');

For a deeper technical description on how the wrapper script works, please refer
the [this response](http://stackoverflow.com/a/3500308/870667) on StackOverflow
explaining the technique.

Executing Arbitrary Commands
----------------------------

Arbitrary Git commands can be executed directly via the wrapper. Commands are
simply what would be passed via the command line minus the Git binary. For
example, executing `git config -l` would be done as in the example below:

    $wrapper->git('config -l');
