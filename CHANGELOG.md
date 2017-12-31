# Changelog

## [v2.0.0]


### Added

- [#137] Added PHP 7.1 typehints, Thanks to @TomasVotruba
- [#137] Added [EasyCodingStandard](https://github.com/Symplify/EasyCodingStandard) to CI, Thanks to @TomasVotruba
- [#137] Added [PHPStan](https://github.com/phpstan/phpstan) to CI, Thanks to @TomasVotruba
- [#138] Added Null Coalesce Operator use, Thanks to @carusogabriel
- [#136] Added `executeRaw()` method for executing arbitrary git commands, Thanks to @cpliakas
- [#124] Added support for listing tags, Thanks to @martwana

### Changed

- [#136] Symfony 4.0 and PHP 7.1 support + min. requirement added, Thanks to @cpliakas
- [#130] Bump to PHP 7.0 min. requirement, cleanup of meta utils, Thanks to @TomasVotruba
- [#137] Make use of PHP 5.6 feature [variadics](http://php.net/manual/en/functions.arguments.php#functions.variable-arg-list) and to make `Command` API better. Also drop `createInstance()` helper method thanks to that, Thanks to @TomasVotruba

    **Before**
    
    ```php
    array_unshift($argsAndOptions, 'name');
    GitCommand::createInstance($argsAndOptions);
    ```
    
    **After**
    
    ```php
    new GitCommand('name', $argsAndOptions);
    ```
 
### Removed

- [#137] Removed `getOutput()` and `__toString()` magic methods, now output is returned right away, Thanks to @TomasVotruba
  
    **Before**

    ```php
    $output = $git->add('*')->getOutput();
    $output = (string) $git->add('*');
    ```
        
    **After**
    
    ```php
    $output = $git->add('*');
    ```

- [#137] Removed fluent interfaces to support [1 way to do things](https://ocramius.github.io/blog/fluent-interfaces-are-evil/), also more `git` alike, Thanks to @TomasVotruba

    **Before**
    
    ```php
    $git->add('*')
        ->commit('Initial commit.');
    ```
    
    **After**
    
    ```php
    $git->add('*');
    $git->commit('Initial commit.');
    ```
    
- [#137] Removed `clone()` helper method, to drop magic it was build on. Use `cloneRepository()` instead, Thanks to @TomasVotruba
                 
    **Before**
    
    ```php
    $gitWrapper->clone(...);
    ```
    
    **After**
    
    ```php
    $gitWrapper->cloneRepository(...);
    ```

## [1.7.0]

- misc
