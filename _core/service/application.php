<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
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
class application extends \fan\core\base\service\single
{
    private ?string $name = null;

    /**
     * Used Application Names
     * @var array
     */
    protected ?array $usedNames = null;

    protected function __construct(bool $allowIni = true)
    {
        parent::__construct($allowIni);
        $sysApp = ['__log_viewer', '__tools'];
        $usedNames = $this->getConfig('used_names', $sysApp);
        if (empty($usedNames)) {
            throw new fatalException($this, 'Used application names isn\'t set.');
        }
        $this->usedNames = adduceToArray($usedNames);
        foreach ($sysApp as $v) {
            if (!in_array($v, $this->usedNames)) {
                $this->usedNames[] = $v;
            }
        }
    }

    public function setAppName(string $name): static
    {
        if (empty($name)) {
            throw new fatalException($this, 'Application name can\'t be empty.');
        } elseif (in_array($name, $this->usedNames)) {
            if ((string)$this->name !== (string)$name) {
                $this->name = $name;
                $this->_broadcastMessage('setAppName', $name);
                \bootstrap::getLoader()->defineNewApp($this);
            }
        } else {
            throw new fatalException($this, 'Unknown application name "' . $name . '".');
        }
        return $this;
    }

    public function getAppName(): string
    {
        if (empty($this->name)) {
            throw new fatalException($this, 'Get Application Name while it isn\'t defined.');
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
}
