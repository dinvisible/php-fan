<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\core\base\service\single;


/**
 * Application service
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
 * @version of file: 05.02.011 (03.10.2015)
 */
class application extends single
{
    private ?string $name = null;

    /**
     * Used Application Names
     * @var array
     */
    protected ?array $usedNames = null;

    private ?object $runtime = null;
    private \Closure $arrayAdducer;

    public function __construct(
        bool $allowIni = true,
        ?object $runtime = null,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null,
        ?callable $arrayAdducer = null
    )
    {
        $this->runtime = $runtime;
        $this->arrayAdducer = \Closure::fromCallable(
            $arrayAdducer
                ?? static function (mixed $value): array {
                    throw new \RuntimeException('Array adducer is not configured for application service.');
                }
        );
        parent::__construct($allowIni, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory);
        $sysApp = [];
        $usedNames = $this->getConfig('used_names', $sysApp);
        if (empty($usedNames)) {
            throw $this->createServiceFatalException('Used application names isn\'t set.');
        }
        $this->usedNames = ($this->arrayAdducer())($usedNames);
        foreach ($sysApp as $v) {
            if (!in_array($v, $this->usedNames)) {
                $this->usedNames[] = $v;
            }
        }
    }

    public function setAppName(string $name): static
    {
        if (empty($name)) {
            throw $this->createServiceFatalException('Application name can\'t be empty.');
        } elseif (in_array($name, $this->usedNames)) {
            if ((string)$this->name !== (string)$name) {
                $this->name = $name;
                $this->_broadcastMessage('setAppName', $name);
                $this->runtime()->getLoader()->defineNewApp($this);
            }
        } else {
            throw $this->createServiceFatalException('Unknown application name "' . $name . '".');
        }
        return $this;
    }

    public function getAppName(): string
    {
        if (empty($this->name)) {
            throw $this->createServiceFatalException('Get Application Name while it isn\'t defined.');
        }
        return $this->name;
    }

    public function getDefaultAppName(): mixed
    {
        $usedApp = $this->getConfig('used_app', []);
        return $this->getConfig('default_app', reset($usedApp));
    }

    public function getProjectName(): mixed
    {
        return $this->getConfig('PROJECT_NAME', 'Name of project is not set');
    }

    public function getCoreVersion(): string
    {
        return 'PHP-FAN 05.02.011 (2015-10-03)';
    }

    private function runtime(): object
    {
        if ($this->runtime !== null) {
            return $this->runtime;
        }

        throw new \RuntimeException('Bootstrap runtime service is not configured for application service.');
    }

    private function arrayAdducer(): callable
    {
        if (!isset($this->arrayAdducer)) {
            $this->arrayAdducer = \Closure::fromCallable(
                static function (mixed $value): array {
                    throw new \RuntimeException('Array adducer is not configured for application service.');
                }
            );
        }

        return $this->arrayAdducer;
    }
}
