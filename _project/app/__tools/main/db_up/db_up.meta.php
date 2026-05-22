<?php

declare(strict_types=1);

/**
 * DB up tools meta
 * @version 05.01.002 (01.05.2013)
 */
return [
    'own' => [
        'title' => 'Update database',

        'externalCss' => [
            'style' => ['~/db_up_index.css'],
        ],

        'externalJS' => [
            'head' => ['/js/js-wrapper.js', '/js/form_validation.js', '~/db_up.js'],
        ],

        'form' => [
            'action_method'  => 'GET',
            'request_type'   => 'G',

            'form_id'        => 'select_scenario',
            'form_key_name'  => 'select_scenario',

            'fields' => [
                'scenario' => [
                    'label'       => 'Scenario',
                    'is_required' => true,
                    'input_type'  => 'radio_group_ml',
                ],
            ],
        ], //form

        'roles' => [
            [
                'condition'    => 'tools_access',
                'transfer_out' => '~/',
            ],
        ],
    ],
];
