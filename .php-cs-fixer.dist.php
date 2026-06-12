<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/core',
        __DIR__ . '/project',
        __DIR__ . '/htdocs',
        __DIR__ . '/tools',
        __DIR__ . '/unit',
    ])
    ->exclude([
        'vendor',
    ]);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(false)
    ->setRules([
        'encoding' => true,
        'full_opening_tag' => true,
    ])
    ->setFinder($finder);
