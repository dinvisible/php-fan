<?php

declare(strict_types=1);

namespace fan\core\bootstrap;
/**
 * Description of initializer
 *
 * This file is part PHP-FAN (php-framework from Alexandr Nosov)
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

class initializer
{
    /**
     * Ini-config data
     * @var array
     */
    protected array $config = [];

    public function __construct($config)
    {
        $config = is_array($config) ? $config : [];
        $this->setConfig($config);

        $this->initBeforeLoader();
    }

    public function setConfig(array $config): static
    {
        foreach ($config as $k => $v) {
            if (preg_match('/^(?:(main)|(check|app|service)_(.*?))_(\d+)$/', (string)$k, $a)) {
                if (empty($a[1])) {
                    $this->config[$a[2]][$a[3]][$a[4]] = explode(':', (string)$v, 2);
                } else {
                    $this->config['main'][$a[4]]       = explode(':', (string)$v, 2);
                }
            }
        }
        return $this;
    }

    public function initBeforeLoader(): static
    {
        $this->checkRequiredParam();
        $this->checkAdvisedParam();
        $this->setMainParam();
        return $this;
    }

    public function initAfterLoader(): static
    {
        set_error_handler('handleError');
        $matcher = \fan\project\service\matcher::instance();
        if (\bootstrap::isCli()) {
            $pathParts = pathinfo($GLOBALS['argv'][0]);
            $matcher->setCli($pathParts['basename'], $pathParts['dirname']);
        } else {
            $host = array_val($_SERVER, 'HTTP_HOST');
            $matcher->setUri((string)array_val($_SERVER, 'REQUEST_URI', ''), is_null($host) ? null : (string)$host);
        }
        return $this;
    }


    public function checkRequiredParam(): void
    {
        $this->_checkPhpConf('req', true);
    }

    public function checkAdvisedParam(): void
    {
        $this->_checkPhpConf('adv', false);
    }

    public function setMainParam(): void
    {
        foreach ($this->config['main'] as $v) {
            ini_set(trim((string)$v[0]), trim((string)$v[1]));
        }
    }

    public function setAppParam(string $name): ?array
    {
        return $this->_setPhpConf('app', $name);
    }

    public function setServiceParam(string $name): ?array
    {
        return $this->_setPhpConf('service', $name);
    }

    protected function _checkPhpConf(string $type, bool $setErr = false): void
    {
        if (isset($this->config['check'][$type])) {
            foreach ($this->config['check'][$type] as $v) {
                $val = ini_get(trim((string)$v[0]));
                if ((string)$val !== trim((string)$v[1])) {
                    $errMsg = 'Incorrect value of param "' . $v[0] . ' = <b>' . $val . '</b>". Need value = <b>' . $v[1] . '</b><br />';
                    if ($setErr) {
                        throw new \RuntimeException($errMsg);
                    } else {
                        \bootstrap::logError($errMsg);
                    }
                }
            }
        }

    }

    protected function _setPhpConf(string $type, string $name): ?array
    {
        if (isset($this->config[$type][$name])) {
            foreach ($this->config[$type][$name] as $v) {
                ini_set(trim((string)$v[0]), trim((string)$v[1]));
            }
            return $this->config[$type][$name];
        }
        return null;
    }

}
