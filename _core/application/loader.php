<?php

declare(strict_types=1);

namespace fan\core\bootstrap;
use fan\core\service\application as service_application;

/**
 * Description of loader
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
 * @version of file: 05.02.007 (31.08.2015)
 * @property-read string $core
 * @property-read string $project
 * @property-read string $app
 * @property-read string $model
 * @property-read string $capp
 * @property-read string $main
 * @property-read string $temp
 */
class loader implements \ArrayAccess
{
    /**
     * Default directory separator
     */
    public const DEFAULT_DIR_SEPARATOR = '/';

    /**
     * Configuration data
     * @var array
     */
    protected ?array $config = null;

    /**
     * Default Config values
     * @var array
     */
    protected array $defaultConfig = [
        'dir_separator' => '/',
        'app_dir'   => '{PROJECT_DIR}/app/',
        'model_dir' => '{PROJECT_DIR}/model/',
        'capp_dir'  => '{APP_DIR}/{APP_NAME}/',
        'main_dir'  => '{CAPP_DIR}/main/',
        'temp_dir'  => '{PROJECT_DIR}/../temp_data/',
        'zend_dir'  => '{PROJECT_DIR}/../libraries/Zend/',
    ];

    /**
     * Namespace keys - correspondence to directories
     * @var array
     */
    protected array $nsKeys = [
        'core'    => null, // Core directory
        'project' => null, // Project directory
        'app'     => null, // All applications directory
        'model'   => null, // Model directory
    ];

    /**
     * Extra direrectory keys
     * @var array
     */
    protected array $extraKeys = [
        'capp' => null, // Current application directory
        'main' => null, // Current directory for Main-blocks
        'temp' => null, // Directory for temporary files
    ];

    /**
     * Path to direrectory of last loaded blocks
     * @var array
     */
    protected array $lastBlock = [];

    /**
     * Flag - show process of autoloading is active
     * @var boolean
     */
    protected bool $loading = false;

    /**
     * Count of arguments for function "class_alias"
     * @var boolean
     */
    protected int $cntAliasArg = 0;

    private ?object $zendAutoloaderLoader = null;

    /**
     * @var callable|null
     */
    private $autoloadRegistrationErrorLogger = null;

    private ?object $fileStorage = null;

    private \Closure $fatalExceptionFactory;

    private \Closure $arrayValueReader;

    private \Closure $fileLoader;

    private \Closure $autoloadRegistrar;

    private \Closure $autoloadUnregistrar;

    private \Closure $classAliaser;

    private \Closure $symbolExists;

    public function __construct(
        $config,
        ?object $zendAutoloaderLoader,
        callable $autoloadRegistrationErrorLogger,
        object $fileStorage,
        ?callable $fatalExceptionFactory,
        callable $arrayValueReader,
        ?callable $fileLoader = null,
        ?callable $autoloadRegistrar = null,
        ?callable $autoloadUnregistrar = null,
        ?callable $classAliaser = null,
        ?callable $symbolExists = null
    )
    {
        $config = is_array($config) ? $config : [];
        $this->config = array_merge($this->defaultConfig, $config);
        $this->zendAutoloaderLoader = $zendAutoloaderLoader;
        $this->autoloadRegistrationErrorLogger = \Closure::fromCallable($autoloadRegistrationErrorLogger);
        $this->fileStorage = $fileStorage;
        $this->fatalExceptionFactory = \Closure::fromCallable(
            $fatalExceptionFactory ?? static function (string $message): \Throwable {
                throw new \RuntimeException('Bootstrap loader fatal exception factory is not configured.');
            }
        );
        $this->arrayValueReader = \Closure::fromCallable($arrayValueReader);
        $this->fileLoader = \Closure::fromCallable(
            $fileLoader ?? fn(string $path, int $way = 3): mixed => $this->fileStorage()->load($path, $way)
        );
        $this->autoloadRegistrar = \Closure::fromCallable(
            $autoloadRegistrar ?? fn(mixed $function, bool $prepend = false): mixed => $this->fileStorage()->registerAutoload($function, $prepend)
        );
        $this->autoloadUnregistrar = \Closure::fromCallable(
            $autoloadUnregistrar ?? fn(mixed $function): mixed => $this->fileStorage()->unregisterAutoload($function)
        );
        $this->classAliaser = \Closure::fromCallable(
            $classAliaser ?? fn(string $original, string $alias, int $cntAliasArg): mixed => $this->fileStorage()->aliasClass($original, $alias, $cntAliasArg)
        );
        $this->symbolExists = \Closure::fromCallable(
            $symbolExists ?? fn(string $name): bool => $this->fileStorage()->symbolExists($name)
        );

        if (!defined('DIR_SEPARATOR')) {
            $separator = isset($config['dir_separator']) ? (string)$config['dir_separator'] : self::DEFAULT_DIR_SEPARATOR;
            define('DIR_SEPARATOR', $separator);
        }

        $this->nsKeys['core']    = $this->getRealPath(CORE_DIR, false);
        $this->nsKeys['project'] = $this->getRealPath(PROJECT_DIR, false);

        $this->cntAliasArg = (int)($this->arrayValueReader)($config, 'cnt_alias_arg', 3);

        $this->_setAppDir()           // Set Applications Directory by path from bootstrap-config
             ->_setModelDir()         // Set Model Directory by path from bootstrap-config
             ->_setTemporaryDir()     // Set Temporary Directory by path from bootstrap-config
             ->_setBasicLoader()      // Set Basic Loader for FAN-classes: $this->loadClass()
             ->_setAdditionalLoader();// Set Additional Loader(s) by bootstrap-config
    }

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\

    public function loadFile(string $path, int $handleError = 0, int $way = 0): mixed
    {
        $convPath = $this->checkPath($path);
        if ($convPath) {
            if ($this->fileStorage()->isReadable($convPath)) {
                if (!in_array($way, [0, 1, 2, 3], true)) {
                    $errorMsg = 'Set incorrect way "' . $way . '" for load file';
                } else {
                    return ($this->fileLoader())($convPath, $way);
                }
            } else {
                $errorMsg = 'File "' . $path . '" isn\'t readable';
            }
        } else {
            $errorMsg = 'File "' . $path . '" doesn\'t exists';
        }
        if ((int)$handleError === 1) {
            throw new \RuntimeException($errorMsg);
        } elseif ($handleError > 1) {
            throw $this->createFatalException($errorMsg);
        }
        return null;
    }

    public function registerAutoload(mixed $function, bool $prepend = false): void
    {
        try {
            ($this->autoloadRegistrar())($function, $prepend);
        } catch (\Throwable $e) {
            ($this->autoloadRegistrationErrorLogger())('Can\'t register autoloader: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    public function unregisterAutoload(mixed $function): void
    {
        ($this->autoloadUnregistrar())($function);
    }

    public function loadClass(string $class, bool $makeAlias = true): bool
    {
        //global $points, $start; $points[$class] = microtime(true) - $start;
        $class = trim($class, '\\');
        if ($this->_symbolExists($class)) {
            return true;
        }

        if (substr($class, 0, 4) !== 'fan\\') {
            // Ignore classes outside the framework namespace to avoid conflicting with other loaders.
            return false;
        }

        if (substr($class, 0, 8) === 'fan\app\\') {
            return $this->loadBlockByClass($class);
        }

        list($key, $path, $parts) = $this->getPathByNS($class, false);
        if (empty($path)) {
            return false;
        }
        $path .= '.php';

        $primePath = $this->nsKeys[$key] . $path;
        if ($this->fileStorage()->isReadable($primePath)) {
            $this->_requireFile($primePath);
            if ($this->_symbolExists($class)) {
                return true;
            }
            throw new \UnexpectedValueException('Class "' . $class . '" isn\'t found in the file "' . $primePath . '"');
        }

        $secondPath = $this->nsKeys['core'] . $path;
        if ($makeAlias && $key === 'project' && $this->fileStorage()->isReadable($secondPath)) {
            $original = 'fan\core\\' . implode('\\', $parts);
            $this->_requireFile($secondPath);
            if (!$this->_symbolExists($original)) {
                throw new \UnexpectedValueException('Class "' . $original . '" isn\'t found in the file "' . $secondPath . '"');
            }
            ($this->classAliaser())($original, $class, $this->cntAliasArg);
            return true;
        }

        return false;
    }

    public function loadBlockByMR(string $appName, array $mainRequest): ?string
    {
        if (empty($mainRequest)) {
            return null;
        }
        $path = str_replace(
                '{CAPP_DIR}',
                $this->nsKeys['app'] . DIR_SEPARATOR . $appName,
                $this->config['main_dir']
        );
        $path .= implode(DIR_SEPARATOR, $mainRequest) . '.php';
        return $this->loadBlockByPath($path);
    }

    public function loadBlockByClass(string $class): ?string
    {
        $class = trim($class, '\\');
        if (substr($class, 0, 8) !== 'fan\app\\') {
            throw new \InvalidArgumentException('Block class "' . $class . '" has incorrect prefix.');
        }

        $key = explode('\\', substr($class, 8));
        if (count($key) !== 3) {
            throw new \InvalidArgumentException('Block class "' . $class . '" has incorrect name.');
        }

        $file = $key[2] . '.php';
        if (isset($this->lastBlock[$key[0]][$key[1]])) {
            $path = $this->lastBlock[$key[0]][$key[1]] . DIR_SEPARATOR . $file;
            if ($this->fileStorage()->isFile($path)) {
                return $this->loadBlockByPath($path);
            }
        }

        $dir  = $this->nsKeys['app'] . DIR_SEPARATOR . $key[0] . DIR_SEPARATOR . $key[1];
        $path = $this->_findBlock($dir, $file);
        if (!empty($path)) {
            return $this->loadBlockByPath($path);
        }
        return null;
    }

    public function loadBlockByPath($srcPath): string
    {
        $srcPath = (string)$srcPath;
        $path = $this->checkPath($srcPath);
        if (empty($path) || !$this->fileStorage()->isReadable($path)) {
            throw new \RuntimeException('Incorrect block path "' . $srcPath . '".');
        }

        $app = (string)$this->nsKeys['app'];
        if (substr($path, 0, strlen($app)) !== $app) {
            throw new \RuntimeException('Class file "' . $path . '" is out of app directory.');
        }
        $key = explode(DIR_SEPARATOR, substr($path, strlen($app) + 1));
        if (count($key) < 3) {
            throw new \RuntimeException('Block path "' . $path . '" isn\'t full.');
        }
        $this->lastBlock[$key[0]][$key[1]] = substr($path, 0, -strlen(end($key)) - 1);

        $this->_requireFile($path);
        $class = '\fan\app\\' . $key[0] . '\\' . $key[1] . '\\' . substr(end($key), 0, -4);
        if (!$this->_symbolExists($class)) {
            throw new \UnexpectedValueException('Class "' . $class . '" isn\'t found in the file "' . $path . '"');
        }

        return $class;
    }

    public function getPathByNS(string $ns, bool $fullPath = true): string|array|null
    {
        $ns = trim($ns, '\\');
        if (substr($ns, 0, 4) === 'fan\\') {
            $ns = substr($ns, 4);
        }

        $parts = explode('\\', $ns);
        $key   = array_shift($parts);
        if (!in_array($key, array_keys($this->nsKeys))) {
            return $fullPath ? null : [$key, null, $parts];
        }
        $path = DIR_SEPARATOR . implode(DIR_SEPARATOR, $parts);
        return $fullPath ? $this->nsKeys[$key] . $path : [$key, $path, $parts];
    }

    /**
     * Transforms path between supported representations.
     */
    public function parsePath(string $path): string
    {
        foreach ($this->_getMixedKeys() as $k => $v) {
            $count = 0;
            $k = strtoupper($k);
            $path = str_replace(['{' . $k . '}', '{' . $k . '_DIR}'], [(string)$v, (string)$v], $path, $count);
            if ($count > 0) {
                break;
            }
        }
        return $path;
    }

    public function checkPath(string $path): ?string
    {
        $path = $this->parsePath($path);
        return $this->getRealPath($path, true);
    }

    public function getRealPath(string $path, bool $isFile = true): ?string
    {
        $fileStorage = $this->fileStorage();
        $realPath = ($isFile ? $fileStorage->isFile($path) : $fileStorage->isDirectory($path)) ? $fileStorage->realPath($path) : false;
        return is_string($realPath) ? str_replace(['/', '\\'], [DIR_SEPARATOR, DIR_SEPARATOR], $realPath) : null;
    }

    public function defineNewApp(service_application $app): void
    {
        $appName = $app->getAppName();
        if (empty($appName)) {
            $this->extraKeys['capp'] = null;
            $this->extraKeys['main'] = null;
            return;
        }
        $mask = [
            '{CORE_DIR}'    => $this->nsKeys['core'],
            '{PROJECT_DIR}' => $this->nsKeys['project'],
            '{APP_DIR}'     => $this->nsKeys['app'],
            '{APP_NAME}'    => $appName,
        ];
        $this->extraKeys['capp'] = $this->getRealPath(
                str_replace(array_keys($mask), array_values($mask), $this->config['capp_dir']),
                false
        );

        $mask['{CAPP_DIR}']   = $this->extraKeys['capp'];
        $this->extraKeys['main'] =  $this->getRealPath(
                str_replace(array_keys($mask), array_values($mask), $this->config['main_dir']),
                false
        );
    }

    public function isLoading(): bool
    {
        return $this->loading;
    }

    public function registerZend2(mixed $zendPath = null, bool $prepend = false): void
    {
        if (is_null($zendPath)) {
            $zendPath = $this->parsePath($this->config['zend_dir']);
        }
        $zendPath = trim(str_replace('\\', '/', (string)$zendPath), '/');
        set_include_path(substr($zendPath, -5) === '/Zend' ? substr($zendPath, 0, -5) : $zendPath);
        $this->zendAutoloaderLoader()->load($zendPath);

        $this->registerAutoload(['Zend_Loader_Autoloader', 'autoload'], $prepend);
    }

    // ======== Private/Protected methods ======== \\

    private function zendAutoloaderLoader(): object
    {
        if ($this->zendAutoloaderLoader === null || !method_exists($this->zendAutoloaderLoader, 'load')) {
            throw new \RuntimeException('Zend autoloader loader is not configured.');
        }

        return $this->zendAutoloaderLoader;
    }

    private function autoloadRegistrationErrorLogger(): callable
    {
        if (is_callable($this->autoloadRegistrationErrorLogger)) {
            return $this->autoloadRegistrationErrorLogger;
        }

        throw new \RuntimeException('Autoload registration error logger is not configured.');
    }

    private function fileStorage(): object
    {
        if ($this->fileStorage === null) {
            throw new \RuntimeException('Bootstrap loader file storage is not configured.');
        }

        return $this->fileStorage;
    }

    private function fileLoader(): callable
    {
        if (!isset($this->fileLoader)) {
            $this->fileLoader = \Closure::fromCallable(
                fn(string $path, int $way = 3): mixed => $this->fileStorage()->load($path, $way)
            );
        }

        return $this->fileLoader;
    }

    private function autoloadRegistrar(): callable
    {
        if (!isset($this->autoloadRegistrar)) {
            $this->autoloadRegistrar = \Closure::fromCallable(
                fn(mixed $function, bool $prepend = false): mixed => $this->fileStorage()->registerAutoload($function, $prepend)
            );
        }

        return $this->autoloadRegistrar;
    }

    private function autoloadUnregistrar(): callable
    {
        if (!isset($this->autoloadUnregistrar)) {
            $this->autoloadUnregistrar = \Closure::fromCallable(
                fn(mixed $function): mixed => $this->fileStorage()->unregisterAutoload($function)
            );
        }

        return $this->autoloadUnregistrar;
    }

    private function classAliaser(): callable
    {
        if (!isset($this->classAliaser)) {
            $this->classAliaser = \Closure::fromCallable(
                fn(string $original, string $alias, int $cntAliasArg): mixed => $this->fileStorage()->aliasClass($original, $alias, $cntAliasArg)
            );
        }

        return $this->classAliaser;
    }

    private function symbolExists(): callable
    {
        if (!isset($this->symbolExists)) {
            $this->symbolExists = \Closure::fromCallable(
                fn(string $name): bool => $this->fileStorage()->symbolExists($name)
            );
        }

        return $this->symbolExists;
    }

    private function createFatalException(string $message): \Throwable
    {
        if (!isset($this->fatalExceptionFactory)) {
            $this->fatalExceptionFactory = \Closure::fromCallable(static function (string $message): \Throwable {
                throw new \RuntimeException('Bootstrap loader fatal exception factory is not configured.');
            });
        }

        $exception = ($this->fatalExceptionFactory)($message);
        if (!$exception instanceof \Throwable) {
            throw new \UnexpectedValueException('Bootstrap loader fatal exception factory must return a throwable object.');
        }

        return $exception;
    }

    protected function _setAppDir(): static
    {
        $path = $this->parsePath($this->config['app_dir']);
        $this->nsKeys['app'] = $this->getRealPath($path, false);
        return $this;
    }

    protected function _setModelDir(): static
    {
        $path = $this->parsePath($this->config['model_dir']);
        $this->nsKeys['model'] = $this->getRealPath($path, false);
        return $this;
    }

    protected function _setTemporaryDir(): static
    {
        $path = $this->parsePath($this->config['temp_dir']);
        $this->extraKeys['temp'] = $this->getRealPath($path, false);
        return $this;
    }

    protected function _setBasicLoader(): static
    {
        $this->registerAutoload([$this, 'loadClass']);
        return $this;
    }

    protected function _setAdditionalLoader(): static
    {
        foreach ($this->config as $k => $v) {
            $method = (string)$v;
            if (substr((string)$k, 0, 11) !== 'add_loader.') {
                continue;
            } elseif (method_exists($this, $method)) {
                $this->$method();
            } else {
                throw new \BadMethodCallException('Incorrect method name "' . $method . '" for activate autoloader.');
            }
        }
        return $this;
    }

    protected function _getMixedKeys(): array
    {
        return array_merge($this->nsKeys, $this->extraKeys);
    }

    /**
     * @param string $file File path or file descriptor handled by the operation.
     */
    protected function _findBlock(string $dir, string $file): ?string
    {
        if ($this->fileStorage()->isFile($dir . DIR_SEPARATOR . $file)) {
            return $dir . DIR_SEPARATOR . $file;
        }

        $entries = $this->fileStorage()->scanDirectory($dir);
        if ($entries === false) {
            return null;
        }
        foreach ($entries as $v) {
            $newDir = $dir . DIR_SEPARATOR . $v;
            if ($v !== '.' && $v !== '..' && $this->fileStorage()->isDirectory($newDir) && $this->fileStorage()->isReadable($newDir)) {
                $newFile = $this->_findBlock($newDir, $file);
                if (!empty($newFile)) {
                    return $newFile;
                }
            }
        }

        return null;
    }

    protected function _requireFile(string $path): static
    {
        $this->loading = true;
        ($this->fileLoader())($path, 3);
        $this->loading = false;
        return $this;
    }

    protected function _symbolExists(string $name): bool
    {
        return (bool)($this->symbolExists())($name);
    }

    // ======== The magic methods ======== \\

    /**
     * Handles dynamic property writes for this current component.
     *
     * @param mixed $value Value that should be applied or transformed.
     */
    public function __set($key, $value): void
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Handles dynamic property reads for this current component.
     */
    public function __get($key): mixed
    {
        return $this->offsetGet($key);
    }

    // ======== Required Interface methods ======== \\

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function offsetSet($key, mixed $value): void
    {
        throw new \LogicException('Error. It is forbidden to set directly the value of property "' . $key . '".');
    }

    public function offsetExists($key): bool
    {
        $keys = $this->_getMixedKeys();
        return isset($keys[$key]);
    }

    public function offsetUnset($key): void
    {
        throw new \LogicException('Error. It is forbidden to unset the value of property "' . $key . '".');
    }

    public function offsetGet($key): mixed
    {
        $keys = $this->_getMixedKeys();
        return isset($keys[$key]) ? $keys[$key] : null;
    }

}
