<?php

declare(strict_types=1);

namespace fan\core\service\session;
/**
 * PEAR session engine
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
class pear
{
    use \fan\core\di\container_aware_trait;

    public function __construct(mixed $config)
    {
        $config = is_array($config) ? $config : [];
        \fan\project\adapter\pear_http_session::ensureAvailable();

        if ($config['IS_DATABASE']) {
            $dbConfig = $this->containerService('config')->get('database');
            $db = $dbConfig['DATABASES'][$config['CONNECTION']];
            HTTP_Session::setContainer('DB', [
                'dsn'   => (string)$db['DRIVER'] . '://' . (string)$db['USER'] . ':' . (string)$db['PASSWORD'] . '@' . (string)$db['HOST'] . '/' . (string)$db['DATABASE'],
                'table' => (string)$config['TABLE']]);
        } // check database

        HTTP_Session::useCookies(true);
        HTTP_Session::start((string)$config['SESSION_NAME'], $this->containerService('request')->get((string)$config['SESSION_NAME']));
    }

    public function getSessionId(): mixed
    {
        return HTTP_Session::id();
    }

    public function get(string $key, ?string $defaultValue = NULL): mixed
    {
        return HTTP_Session::get($key, $defaultValue);
    }

    public function set(string $key, string $value): void
    {
        HTTP_Session::set($key, $value);
    }

    public function remove(string $key): void
    {
        HTTP_Session::set($key, NULL);
    }

    public function remove_all(): void
    {
        HTTP_Session::clear();
    }

    public function destroy(): void
    {
        HTTP_Session::destroy();
    }
}
