<?php

declare(strict_types=1);

/**
 * Create entity tools meta
 * @version 05.02.005 (12.02.2015)
 */
return [
    'own' => [
        'title' => 'Create entity',

        'externalCss' => [ // css files
            'style' => ['~/entity.css'],
        ],

        'embeddedBlocks' => [
            'entity_filter' => 'form/entity_filter',
        ],

        'form' => [
            'action_method'  => 'POST',
            'request_type'   => 'P',

            'form_key_name'  => 'create_entity',
            'form_id'        => 'create_entity',

            'fields' => [
                'tbl' => [
                    'label'      => 'Table',
                    'input_type' => 'checkbox',
                    'depth'      => 1
                ],
            ],
        ], //form

        'dontCrawl' => true,

        'cache' => [
            'mode' => 1,
        ],
        'roles' => [
            [
                'condition'     => 'tools_access',
                'transfer_sham' => '~/',
            ],
        ],
    ],
];
