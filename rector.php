<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\DeadCode\Rector\ClassConst\RemoveUnusedClassConstantRector;
use Rector\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector;
use Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);

    $parameters->set(Option::SETS, [
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::NETTE_CODE_QUALITY,
        SetList::PRIVATIZATION,
        SetList::PHPUNIT_80,
        SetList::PHP_70,
        SetList::PHP_71,
        SetList::PHP_72,
        SetList::PHP_73,
    ]);

    $parameters->set(Option::SKIP, [
        PrivatizeFinalClassMethodRector::class => [__DIR__ . '/tests/GitWorkingCopyTest.php'],

        // buggy
        // ChangeReadOnlyVariableWithDefaultValueToConstantRector::class,
        RemoveUnusedClassConstantRector::class,
    ]);
};
