<?php
declare(strict_types=1);

namespace fan\core\base\model;
use fan\core\base\model\entity;

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

    private ?object $reflector = null;

    private ?object $fileStorage = null;

    private \Closure $sqlDirectoryResolver;

    public function __construct(entity $entity, ?object $reflector = null, ?object $fileStorage = null, ?callable $sqlDirectoryResolver = null)
    {
        $this->entity = $entity;
        $this->reflector = $reflector;
        $this->fileStorage = $fileStorage;
        $this->sqlDirectoryResolver = \Closure::fromCallable(
            $sqlDirectoryResolver ?? static fn(entity $entity): string => $entity->getSqlDirectory()
        );
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
    public function __call(string $method, array $args): mixed
    {
        $method = (string)$method;
        if (substr($method, 0, 4) === 'set_') {
            $this->set(substr($method, 4), (string)($args[0] ?? ''));
            return $this;
        } elseif (substr($method, 0, 4) === 'get_') {
            return $this->get(substr($method, 4));
        } else {
            throw $this->createRequestFatalException('Incorrect call of instance SQL-request loader!');
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

    public function getEntity(): entity
    {
        return $this->entity;
    }

    // ======== Private/Protected methods ======== \\
    protected function _loadSQL(string $key): ?string
    {
        $fileName = $this->_checkSQLfile($key);
        if ($fileName === null) {
            return null;
        }

        $content = $this->fileStorage()->read($fileName);

        return $content === false ? null : $content;
    }

    protected function _checkSQLfile(string $key): ?string
    {
        $entity = $this->getEntity();
        $dirName = $this->sqlDirectory($entity);
        foreach ($this->reflector()->getParentPaths($entity) as $v) {
            $fileName  = pathinfo($v, PATHINFO_DIRNAME) . '/';
            $fileName .= $dirName . '/' . $key . '.sql';
            if ($this->fileStorage()->exists($fileName)) {
                return $fileName;
            }
        }
        return null;
    }

    private function reflector(): object
    {
        return $this->reflector ?? throw new \RuntimeException('Reflector service is not configured for model request.');
    }

    private function fileStorage(): object
    {
        return $this->fileStorage ?? throw new \RuntimeException('Model request file storage is not configured.');
    }

    private function sqlDirectory(entity $entity): string
    {
        $directory = ($this->sqlDirectoryResolver)($entity);
        if (!is_string($directory)) {
            throw new \UnexpectedValueException('Model request SQL directory resolver must return a string.');
        }

        return $directory;
    }

    private function createRequestFatalException(string $message, int $code = E_USER_ERROR, ?\Throwable $previous = null): \Throwable
    {
        $exception = $this->getEntity()->createRequestFatalException($message, $code, $previous);
        if (!$exception instanceof \Throwable) {
            $actual = is_object($exception) ? get_class($exception) : gettype($exception);
            throw new \UnexpectedValueException('Model request exception factory returned "' . $actual . '".');
        }

        return $exception;
    }

}
