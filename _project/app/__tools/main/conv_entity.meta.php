<?php

declare(strict_types=1);

/**
 * Convert entity tools meta
 * @version 05.02.005 (12.02.2015)
 */
return [
    'own' => [
        'title' => 'Tool for convert entity from old (PHP-FAN4) to new (PHP-FAN5) format',

        'form' => [
            'action_method'  => 'POST',
            'request_type'   => 'P',
            'form_key_name'  => 'conv_entity',
            'form_id'        => 'conv_entity',
            'fields' => [
                'source_dir' => [
                    'label'          => 'Source directory',
                    'input_type'     => 'text',
                    'is_required'    => true,
                ],
                'source_mask' => [
                    'label'          => 'Regexp for select files',
                    'input_type'     => 'text',
                    'is_required'    => true,
                ],
                'dest_dir' => [
                    'label'          => 'Destination directory',
                    'input_type'     => 'text',
                    'is_required'    => true,
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
