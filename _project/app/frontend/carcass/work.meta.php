<?php

declare(strict_types=1);

/**
 * Main carcass meta
 * @version 05.02.001 (10.03.2014)
 */
return [
    'own' => [
        'embeddedBlocks' => [ // Key - template var; Value - path to block
            'header'     => 'design/header',
            'main'       => '{MAIN}',
            'footer'     => 'design/footer',
        ],
        'externalCss' => [ // css files
            //'new' => ['/css/layout.css'],
        ],
    ],
];
