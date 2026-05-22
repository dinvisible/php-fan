<?php

declare(strict_types=1);

/**
 * Meta data of request password block
 *
 * This file is part PHP-FAN (php-framework of Alexandr Nosov)
 * Copyright (C) 2005-2007 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *     http://www.opensource.org/licenses/lgpl-license.php
 *
 * Do not remove this comment if you want to use script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.02.005 (12.02.2015)
 */
return [
    'own' => [

        'carcass' => 'carcass/simple',

        'title'   => 'Enter password',

        'externalCss' => [
            'style' => ['~/password.css'],
        ],

        'passwd_as_hash' => 0,

        'form' => [
            'action_method'  => 'POST',
            'request_type'   => 'P',
            'redirect_uri'   => '~/index.html',
            'form_key_name'  => 'tools_password',
            'form_id'        => 'tools_password',
            'required_msg'   => 'FIELD_"{FIELD_LABEL}"_IS_REQUIRED',
            'fields' => [
                'login' => [
                    'label'          => 'Login',
                    'input_type'     => 'text',
                    'is_required'    => true,
                ],

                'password' => [
                    'label'          => 'Password',
                    'input_type'     => 'password',
                    'is_required'    => true,
                    'validate_rules' => [
                        [
                            'rule_name' => 'checkPassword',
                            'rule_data' => [
                                'login' => 'login',
                            ],
                            'not_empty' => true,
                            'error_msg' => 'Incorrect login or password',
                            'not_js'    => true,
                        ],
                    ],
                ],
            ],
        ], //form
    ], //'own'
];
