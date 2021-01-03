<?php

// use rector to upgrade to version 3.0:
// composer require rector/rector --dev
// vendor/bin/rector process src tests --config vendor/cpliakas/git-wrapper/upgrade/rector/git-wrapper-30.php

// see https://github.com/cpliakas/git-wrapper/pull/182/files
// see https://github.com/cpliakas/git-wrapper/pull/186/files

declare(strict_types=1);

use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'GitWrapper\Event\GitOutputListenerInterface' => 'GitWrapper\EventSubscriber\AbstractOutputEventSubscriber',
                'GitWrapper\Event\Event' => 'GitWrapper\Event\AbstractGitEvent',
                'GitWrapper\Event\GitLoggerEventSubscriber' => 'GitWrapper\EventSubscriber\GitLoggerEventSubscriber',
                'GitWrapper\GitException' => 'GitWrapper\Exception\GitException',
                'GitWrapper\Event\GitOutputStreamListener' => 'GitWrapper\EventSubscriber\StreamOutputEventSubscriber',
                'GitWrapper\GitProcess' => 'GitWrapper\Process\GitProcess',
            ],
        ]]);

    $services->set(RenameStaticMethodRector::class)
        ->call('configure', [[
            RenameStaticMethodRector::OLD_TO_NEW_METHODS_BY_CLASSES => [
                new RenameStaticMethod(
                    'GitWrapper\GitWrapper',
                    'parseRepositoryName',
                    'GitWrapper\Strings\GitStrings',
                    'parseRepositoryName'
                ),
            ],
        ]]);
};
