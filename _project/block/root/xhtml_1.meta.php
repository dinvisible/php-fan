<?php

declare(strict_types=1);

/**
 * Basiс xhtml_1 Meta data
 * @version of file: 05.02.005 (12.02.2015)
 */
return [
    /**
     * Meta data for embedded blocks
     */
    'common' => [
    ],
    /**
     * Meta data for curent carcass block
     */
    'own' => [
        'template' => 'xhtml_10_transitional.tpl',
        //'template' => 'xhtml_10_strict.tpl',

        'externalCss' => [
            'style' => ['/css/main.css'],
            'ie'    => ['/css/invalid_ie.css'],
            'ie6'   => ['/css/invalid_ie6.css'],
        ],
    ],
];
