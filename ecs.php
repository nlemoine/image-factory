<?php

use PhpCsFixer\Fixer\FunctionNotation\NativeFunctionInvocationFixer;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;
use Symplify\EasyCodingStandard\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);

    $containerConfigurator->import(SetList::PSR_12);
    $containerConfigurator->import(SetList::NAMESPACES);
    $containerConfigurator->import(SetList::CLEAN_CODE);
    $containerConfigurator->import(SetList::SYMFONY);

    $services = $containerConfigurator->services();
    $services->set(ArraySyntaxFixer::class)
        ->call('configure', [[
            'syntax' => 'short',
        ]]);
    $services->set(NativeFunctionInvocationFixer::class)
        ->call('configure', [[
            'include' => [
                '@all',
            ],
            'scope' => 'namespaced'
        ]]);
};
