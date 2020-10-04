<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\AssignmentInConditionSniff;
use PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PATHS, [
        __DIR__ . '/src', __DIR__ . '/tests',
        __DIR__ . '/ecs.php',
        __DIR__ . '/rector-ci.php',
    ]);

    $parameters->set(Option::SETS, [
        SetList::PSR_12,
        SetList::PHP_71,
        SetList::COMMON,
        SetList::CLEAN_CODE,
        SetList::SYMPLIFY,
    ]);

    $parameters->set(Option::SKIP, [
        AssignmentInConditionSniff::class => null,
        'Symplify\CodingStandard\Sniffs\DependencyInjection\NoClassInstantiationSniff' => null,
        UnaryOperatorSpacesFixer::class => null,
        'SlevomatCodingStandard\Sniffs\Variables\UnusedVariableSniff.UnusedVariable' => [
            'tests/GitWorkingCopyTest.php',
        ],
        'Symplify\CodingStandard\Sniffs\CleanCode\ForbiddenStaticFunctionSniff' => ['src/Strings/GitStrings.php'],
        'Symplify\CodingStandard\Sniffs\CleanCode\ForbiddenReferenceSniff' => ['tests/Source/StreamSuppressFilter.php'],
    ]);
};
