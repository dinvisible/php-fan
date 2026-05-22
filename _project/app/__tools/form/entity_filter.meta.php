<?php

declare(strict_types=1);

/**
 * Create entity tools meta
 * @version 05.02.005 (12.02.2015)
 */
return [
    'own' => [
        'externalCss' => [
            'style' => ['frm' => '~/form.css'],
        ],

        'form' => [
            'action_method'  => 'GET',
            'request_type'   => 'G',

            'form_key_name'  => 'filter',
            'form_id'        => 'filter',

            'fields' => [
                'connection' => [
                    'label'      => 'Database',
                    'input_type' => 'select',
                    'dataSource' => [
                        'method' => 'getDbList',
                    ],
                ],
                'ns_pref' => [
                    'label'      => 'Model subdirectory',
                    'input_type' => 'select',
                    'dataSource' => [
                        'method' => 'getDirList',
                    ],
                ],
                'table_regexp' => [
                    'label'         => 'Table name regexp',
                    'input_type'    => 'text',
                    'default_value' => '/.+/',
                    'note'          => 'Enter the regular expression to filter by names of the database tables',
                ],
            ],
        ], //form

        'dontCrawl' => true,

        'cache' => [
            'mode' => 1,
        ],
        'roles' => [
            [
                'condition'    => 'tools_access',
                'transfer_out' => '~/',
            ],
        ],
    ],
];
