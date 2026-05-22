<?php

declare(strict_types=1);

/**
 * Home page meta-data
 * @version 05.02.001 (10.03.2014)
 */
return [
    'own' => [

        'embeddedBlocks' => [ // Key - template var; Value - path to block
            'test' => '{MAIN}/test',
        ],


        /** /
        'carcass'     => '{CARCASS}/home_carcass',
        'externalCss' => [ // css files
            'new' => ['/css/home.css'],
        ],/**/

        /**
         * All parameters below it is possible to set as "own"-part, amd in "common"-part
         * /
        'tplVars' => [ // variable, which sets in template automaticaly
            'test' => '{FRONTED}/main/test',
            'tplVar2' => 'Value of variable 2',
        ],/**/
    ],
    /** /
    'common' => [
        'test0' => 1,
    ],/**/
];
