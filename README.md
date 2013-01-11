Overview
========

This library is a PHP wrapper around the Git command line tool.

Its purpose is to provide a readable API that abstracts some of the challenges
of executing Git commands from within a PHP process. Specifically, this library
builds upon the Symfony framework's Process component to execute the Git command
in a way that works across platforms and uses the best-in-breed techniques
available to PHP. This library also provides an SSH wrapper script and API
method for developers to easily specify a private key other than one of the
defaults. Finally, various command are expected to be executed in the directory
containing the working copy. Although this a fairly simple challenge to
overcome, the library handles this transparently to the developer doesn't have
to think about it.

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

    $git = $wrapper->workingCopy('./path/to/working/copy');

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

Gotchas
=======

There are a few "gotchas" that are out of scope for this library to solve but
might prevent a successful implementation of running Git via PHP. The following
is an incomplete list of challenges that are often encountered when executing
Git from PHP.

Missing HOME Environment Variable
---------------------------------

Sometimes the `HOME` environment variable is not set in the Git process that is
spawned by PHP. This will cause many git operations to fail. It is advisable to
set the `HOME` environment variable to a path outside of the document root that
the web server has write access to. Note that this environment variable is only
set for the process running Git and NOT the PHP process that is spawns it.

    $wrapper->setEnvVar('HOME', '/path/to/a/private/writable/dir');

It is important that the storage is persistent as the ~/.gitconfig file will be
written to this location. See the following "gotcha" for why this is important.

Missing Identity And Configurations
-----------------------------------

Many repositories require that a name and email address are specified. This data
is set by running `git config [name] [value]` on the command line, and the
configurations are usually stored in the `~/.gitconfig file`. When executing Git
via PHP, however, the process might have a different home directory than the
user who normally runs git via the command line. Therefore no identity is sent
to the repository, and it will likely throw an error.

    // Set configuration options globally.
    $wrapper->git('config --global user.name "User name"');
    $wrapper->git('config --global user.email user@example.com');

    // Set configuration options per repository.
    $git
        ->config('user.name', 'User name')
        ->config('user.email', 'user@example.com');

Commits To Repositories With No Changes
---------------------------------------

Running `git commit` on a repository with no changes returns no output but exits
with a status of 1. Therefore the library will throw a `GitException` since it
correctly detected an error. It is advisable to check whether a working copy has
any changes prior to running the commit operation in order to prevent unwanted
exceptions.

    if ($git->hasChanges()) {
        $git->commit('Committed the changes.');
    }
