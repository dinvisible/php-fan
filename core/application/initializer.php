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
     * Configuration data
     * @var array
     */
    protected array $config = [];

    private ?object $runtime = null;

    private ?object $matcher = null;

    private ?object $input = null;

    private $errorHandlerRegistrar = null;

    private ?object $phpRuntimeSettings = null;

    public function __construct(
        $config,
        ?object $runtime = null,
        ?object $matcher = null,
        ?object $input = null,
        ?callable $errorHandlerRegistrar = null,
        ?object $phpRuntimeSettings = null
    )
    {
        $this->runtime = $runtime;
        $this->matcher = $matcher;
        $this->input = $input;
        $this->errorHandlerRegistrar = $errorHandlerRegistrar;
        $this->phpRuntimeSettings = $phpRuntimeSettings;

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

    public function initAfterLoader(?object $matcher = null, ?object $input = null, ?object $runtime = null): static
    {
        $this->matcher = $matcher ?? $this->matcher;
        $this->input = $input ?? $this->input;
        $this->runtime = $runtime ?? $this->runtime;

        ($this->errorHandlerRegistrar())([$this->runtime(), 'handleError']);
        if ($this->runtime()->isCli()) {
            $argv = $this->input()->argv();
            $pathParts = pathinfo((string)($argv[0] ?? ''));
            $this->matcher()->setCli((string)($pathParts['basename'] ?? ''), (string)($pathParts['dirname'] ?? ''));
        } else {
            $host = $this->input()->serverValue('HTTP_HOST');
            $this->matcher()->setUri((string)$this->input()->serverValue('REQUEST_URI', ''), is_null($host) ? null : (string)$host);
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
            $this->phpRuntimeSettings()->set(trim((string)$v[0]), trim((string)$v[1]));
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
                $val = $this->phpRuntimeSettings()->get(trim((string)$v[0]));
                if ($val === false && trim((string)$v[1]) === '0') {
                    continue;
                }
                if ((string)$val !== trim((string)$v[1])) {
                    $errMsg = 'Incorrect value of param "' . $v[0] . ' = <b>' . $val . '</b>". Need value = <b>' . $v[1] . '</b><br />';
                    if ($setErr) {
                        throw new \RuntimeException($errMsg);
                    } else {
                        $this->runtime()->logError($errMsg);
                    }
                }
            }
        }

    }

    protected function _setPhpConf(string $type, string $name): ?array
    {
        if (isset($this->config[$type][$name])) {
            foreach ($this->config[$type][$name] as $v) {
                $this->phpRuntimeSettings()->set(trim((string)$v[0]), trim((string)$v[1]));
            }
            return $this->config[$type][$name];
        }
        return null;
    }

    private function runtime(): object
    {
        if ($this->runtime === null) {
            throw new \RuntimeException('Bootstrap runtime dependency is not configured for initializer.');
        }

        return $this->runtime;
    }

    private function matcher(): object
    {
        if ($this->matcher === null) {
            throw new \RuntimeException('Matcher dependency is not configured for initializer.');
        }

        return $this->matcher;
    }

    private function input(): object
    {
        if ($this->input === null) {
            throw new \RuntimeException('Request input dependency is not configured for initializer.');
        }

        return $this->input;
    }

    private function errorHandlerRegistrar(): callable
    {
        if (!is_callable($this->errorHandlerRegistrar)) {
            throw new \RuntimeException('Error handler registrar dependency is not configured for initializer.');
        }

        return $this->errorHandlerRegistrar;
    }

    private function phpRuntimeSettings(): object
    {
        if ($this->phpRuntimeSettings === null) {
            throw new \RuntimeException('PHP runtime settings dependency is not configured for initializer.');
        }

        return $this->phpRuntimeSettings;
    }

}
