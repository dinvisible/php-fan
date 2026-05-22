<?php

declare(strict_types=1);

namespace fan\core\service\session;
/**
 * Session engine adodb
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
 * @version of file: 05.02.004 (25.12.2014)
 */
/**
 * ADOdb session engine
 * @version 1.0
 */
class adodb extends inbuilt
{
    public function __construct(mixed $config)
    {
        $config = is_array($config) ? $config : [];
        if ($config['IS_DATABASE']) {
            global $ADODB_SESSION_DRIVER, $ADODB_SESSION_CONNECT, $ADODB_SESSION_USER, $ADODB_SESSION_PWD, $ADODB_SESSION_DB, $ADODB_SESSION_TBL;
            $dbConfig = $this->containerService('config')->get('database');
            $db = $dbConfig['DATABASES'][$config['CONNECTION']];

            $ADODB_SESSION_DRIVER  = (string)$db['DRIVER'];
            $ADODB_SESSION_CONNECT = (string)$db['HOST'];
            $ADODB_SESSION_USER    = (string)$db['USER'];
            $ADODB_SESSION_PWD     = (string)$db['PASSWORD'];
            $ADODB_SESSION_DB      = (string)$db['DATABASE'];
            $ADODB_SESSION_TBL     = (string)$config['TABLE'];

            \fan\project\adapter\adodb::ensureSessionSupport();
        } // check database

        parent::__construct($config);
    }
}
