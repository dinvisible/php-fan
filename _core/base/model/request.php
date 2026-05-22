<?php
declare(strict_types=1);

namespace fan\core\base\model;
use fan\project\exception\model\entity\fatal as fatalException;
/**
 * Loader of Source SQL-requests for \fan\core\service\entity\designer\request
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
 * @version of file: 05.02.001 (10.03.2014)
 */
class request
{
    /**
     * Used SQL-request
     * @var array
     */
    protected array $sql = [];

    /**
     * Entity - table data
     * @var \fan\core\base\model\entity
     */
    protected ?object $entity = null;

    public function __construct(\fan\core\base\model\entity $entity)
    {
        $this->entity = $entity;
    }

    // ======== The magic methods ======== \\

    public function __set(string $key, mixed $value): void
    {
        $this->set((string)$key, (string)$value);
    }

    public function __get(string $key): string
    {
        return $this->get((string)$key);
    }
    /**
     * @throws fatalException
     */
    public function __call(string $method, array $args): mixed
    {
        $method = (string)$method;
        if (substr($method, 0, 4) === 'set_') {
            $this->set(substr($method, 4), (string)($args[0] ?? ''));
        } elseif (substr($method, 0, 4) === 'get_') {
            return $this->get(substr($method, 4));
        } else {
            throw new fatalException($this->getEntity(), 'Incorrect call of instance SQL-request loader!');
        }
    }

    // ======== Required Interface methods ======== \\

    // ======== Main Interface methods ======== \\
    public function get(string $key): string
    {
        if (!array_key_exists($key, $this->sql)) {
            $this->sql[$key] = $this->_loadSQL($key);
        }
        if (!$this->sql[$key]) {
            throw new \OutOfBoundsException('Call for unset SQL-key.');
        }
        return $this->sql[$key];
    }

    public function set(string $key, string $value): static
    {
        $this->sql[$key] = $value;
        return $this;
    }

    public function setRequests(array $sql): static
    {
        $this->sql = array_merge($this->sql, $sql);
        return $this;
    }

    public function toArray(): array
    {
        return $this->sql;
    }

    public function getEntity(): \fan\core\base\model\entity
    {
        return $this->entity;
    }

    // ======== Private/Protected methods ======== \\
    protected function _loadSQL(string $key): ?string
    {
        $fileName = $this->_checkSQLfile($key);
        return is_null($fileName) ? null : (string)file_get_contents($fileName);
    }

    protected function _checkSQLfile(string $key): ?string
    {
        $entity = $this->getEntity();
        $dirName = $entity->getService()->getSqlDir();
        foreach (service('reflector')->getParentPaths($entity) as $v) {
            $fileName  = pathinfo($v, PATHINFO_DIRNAME) . '/';
            $fileName .= $dirName . '/' . $key . '.sql';
            if (file_exists($fileName)) {
                return $fileName;
            }
        }
        return null;
    }

}
