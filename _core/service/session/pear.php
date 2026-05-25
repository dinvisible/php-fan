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
    private ?object $httpSession = null;

    public function __construct(
        mixed $config,
        ?object $databaseConfig = null,
        ?object $request = null,
        ?object $sessionSupportLoader = null,
        ?object $httpSession = null
    )
    {
        $this->httpSession = $httpSession;
        $config = is_array($config) ? $config : [];
        $this->sessionSupportLoader($sessionSupportLoader)->load();

        if (!empty($config['IS_DATABASE'])) {
            if ($databaseConfig === null) {
                throw new \RuntimeException('Database config is not configured for PEAR session engine.');
            }
            $dbConfig = $databaseConfig;
            $db = $dbConfig['DATABASES'][$config['CONNECTION']];
            $this->httpSession()->setContainer('DB', [
                'dsn'   => (string)$db['DRIVER'] . '://' . (string)$db['USER'] . ':' . (string)$db['PASSWORD'] . '@' . (string)$db['HOST'] . '/' . (string)$db['DATABASE'],
                'table' => (string)$config['TABLE']]);
        } // check database

        if ($request === null) {
            throw new \RuntimeException('Request service is not configured for PEAR session engine.');
        }

        $sessionName = (string)($config['SESSION_NAME'] ?? 'SID');
        $this->httpSession()->useCookies(true);
        $this->httpSession()->start($sessionName, $request->get($sessionName));
    }

    private function sessionSupportLoader(?object $sessionSupportLoader): object
    {
        if ($sessionSupportLoader === null || !method_exists($sessionSupportLoader, 'load')) {
            throw new \RuntimeException('PEAR HTTP session loader is not configured.');
        }

        return $sessionSupportLoader;
    }

    public function getSessionId(): mixed
    {
        return $this->httpSession()->id();
    }

    public function get(string $key, ?string $defaultValue = NULL): mixed
    {
        return $this->httpSession()->get($key, $defaultValue);
    }

    public function set(string $key, string $value): void
    {
        $this->httpSession()->set($key, $value);
    }

    public function remove(string $key): void
    {
        $this->httpSession()->set($key, NULL);
    }

    public function remove_all(): void
    {
        $this->httpSession()->clear();
    }

    public function destroy(): void
    {
        $this->httpSession()->destroy();
    }

    private function httpSession(): object
    {
        if ($this->httpSession === null) {
            throw new \RuntimeException('HTTP session adapter is not configured for PEAR session engine.');
        }

        return $this->httpSession;
    }
}
