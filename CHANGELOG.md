# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

PRs and issues are linked, so you can find more about it. Thanks to [ChangelogLinker](https://github.com/Symplify/ChangelogLinker).

<!-- changelog-linker -->

## [v2.2.0] - 2019-04-29

- [#171] Update documentation with timeout example, Thanks to [@kallehauge]
- [#167] Update head method to seperate args, Thanks to [@Hodgy]
- [#165] Update `GitProcess`, Thanks to [@Big-Shark]
- [#164] Update testEvent for symfony process 3.4+, Thanks to [@Big-Shark]
- [#163] Update .travis.yml, Thanks to [@Big-Shark]

## [v2.1.0] - 2018-11-09

### Changed

- [#160] bump Symfony to 4.1, ECS to 5.1, PHPStan
- [#158] Bump dependencies, code styling and static analysis fixes
- [#151] PHPUnit 7, Thanks to [@carusogabriel]

### Fixed

- [#156] Fix a bug on windows, Thanks to [@wi1dcard]

## [v2.0.1] - 2018-01-30

### Added

- [#146] Add a test with lowest dependencies, Thanks to [@Soullivaneuh]

### Changed

- [#144] Symfony 4 compatibility improvement., Thanks to [@allansun]
- [#139] Refactoring tests, Thanks to [@carusogabriel]
- [#140] Use Symplify preset config file, Thanks to [@carusogabriel]
- [#142] Pass the previous exception on git exception, Thanks to [@Soullivaneuh]
- [#150] Do not override Process::run, Thanks to [@Soullivaneuh]

## [v2.0.0] - 2017-12-31

### Added

- [#137] Added PHP 7.1 typehints, Thanks to [@TomasVotruba]
- [#137] Added [EasyCodingStandard](https://github.com/Symplify/EasyCodingStandard) to CI, Thanks to [@TomasVotruba]
- [#137] Added [PHPStan](https://github.com/phpstan/phpstan) to CI, Thanks to [@TomasVotruba]
- [#138] Added Null Coalesce Operator use, Thanks to [@carusogabriel]
- [#136] Added `executeRaw()` method for executing arbitrary git commands, Thanks to [@cpliakas]
- [#124] Added support for listing tags, Thanks to [@martwana]

### Changed

- [#136] Symfony 4.0 and PHP 7.1 support + min. requirement added, Thanks to [@cpliakas]
- [#130] Bump to PHP 7.0 min. requirement, cleanup of meta utils, Thanks to [@TomasVotruba]
- [#137] Make use of PHP 5.6 feature [variadics](http://php.net/manual/en/functions.arguments.php#functions.variable-arg-list) and to make `Command` API better. Also drop `createInstance()` helper method thanks to that, Thanks to [@TomasVotruba]

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

- [#137] Removed `getOutput()` and `__toString()` magic methods, now output is returned right away, Thanks to [@TomasVotruba]
  
    **Before**

    ```php
    $output = $git->add('*')->getOutput();
    $output = (string) $git->add('*');
    ```
        
    **After**
    
    ```php
    $output = $git->add('*');
    ```

- [#137] Removed fluent interfaces to support [1 way to do things](https://ocramius.github.io/blog/fluent-interfaces-are-evil/), also more `git` alike, Thanks to [@TomasVotruba]

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
    
- [#137] Removed `clone()` helper method, to drop magic it was build on. Use `cloneRepository()` instead, Thanks to [@TomasVotruba]
                 
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


[v2.0.0]: https://github.com/cpliakas/git-wrapper/compare/1.7.0...v2.0.0
[#138]: https://github.com/cpliakas/git-wrapper/pull/138
[#137]: https://github.com/cpliakas/git-wrapper/pull/137
[#136]: https://github.com/cpliakas/git-wrapper/pull/136
[#130]: https://github.com/cpliakas/git-wrapper/pull/130
[#124]: https://github.com/cpliakas/git-wrapper/pull/124
[@martwana]: https://github.com/martwana
[@cpliakas]: https://github.com/cpliakas
[@carusogabriel]: https://github.com/carusogabriel
[@TomasVotruba]: https://github.com/TomasVotruba

[#156]: https://github.com/cpliakas/git-wrapper/pull/156
[#155]: https://github.com/cpliakas/git-wrapper/pull/155
[#151]: https://github.com/cpliakas/git-wrapper/pull/151
[#150]: https://github.com/cpliakas/git-wrapper/pull/150
[#146]: https://github.com/cpliakas/git-wrapper/pull/146
[#144]: https://github.com/cpliakas/git-wrapper/pull/144
[#142]: https://github.com/cpliakas/git-wrapper/pull/142
[#140]: https://github.com/cpliakas/git-wrapper/pull/140
[#139]: https://github.com/cpliakas/git-wrapper/pull/139
[@wi1dcard]: https://github.com/wi1dcard
[@allansun]: https://github.com/allansun
[@Soullivaneuh]: https://github.com/Soullivaneuh
[#160]: https://github.com/cpliakas/git-wrapper/pull/160
[#158]: https://github.com/cpliakas/git-wrapper/pull/158
[#171]: https://github.com/cpliakas/git-wrapper/pull/171
[#167]: https://github.com/cpliakas/git-wrapper/pull/167
[#165]: https://github.com/cpliakas/git-wrapper/pull/165
[#164]: https://github.com/cpliakas/git-wrapper/pull/164
[#163]: https://github.com/cpliakas/git-wrapper/pull/163
[@kallehauge]: https://github.com/kallehauge
[@Hodgy]: https://github.com/Hodgy
[@Big-Shark]: https://github.com/Big-Shark