
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

The following snippets explain how to use this library. For working examples,
please view the code in the examples/ directory.

Initializing The Library
------------------------

Require the autoloader that is created by Composer and instantiate the wrapper
around the git binary.

    use GitWrapper\GitWrapper;

    require_once 'vendor/autoload.php';
    $wrapper = new GitWrapper();

If no argument is passed when instantiating the GitWrapper class, an attempt
will be made to find the executable automatically. To explicitly specify the
path to the Git binary, pass it as the first argument.

