<?php

declare(strict_types=1);

namespace FanTest\_core\block;
use fan\core\base\meta\maker;
use fan\core\base\meta\maker_state;
use fan\core\base\meta\row;
use fan\core\block\base;
use fan\core\di\container_interface;
use fan\core\service\tab;
use fan\project\base\meta\row as meta_row;
use fan\project\service\error;


require_once __DIR__ . '/../../../../_core/base/meta/maker.php';

class FakeServiceRegistry
{
    private static array $services = [];

    public static function reset(): void
    {
        self::$services = [];
        FakeRoleRegistry::reset();
        FakeViewClass::$format = 'html';
        FakeViewClass::$lastRouter = null;
    }

    public static function installContainer(container_interface $container): void
    {
    }

    public static function set($name, $service): void
    {
        self::$services[$name] = $service;
    }

    public static function get($name, $args = []): mixed
    {
        if (isset(self::$services[$name])) {
            return self::$services[$name];
        }

        if ((string)$name === 'error' && class_exists('\fan\project\service\error', false)) {
            return error::instance();
        }

        if (!isset(self::$services[$name])) {
            if ((string)$name === 'tab') {
                self::$services[$name] = new FakeTab();
            } elseif ((string)$name === 'request') {
                self::$services[$name] = new FakeRequest();
            } elseif ((string)$name === 'session') {
                self::$services[$name] = new FakeSession($args);
            } elseif ((string)$name === 'reflector') {
                self::$services[$name] = new FakeReflector();
            } elseif ((string)$name === 'locale') {
                self::$services[$name] = new FakeLocale();
            } elseif ((string)$name === 'role') {
                self::$services[$name] = new FakeRoleService();
            } elseif ((string)$name === 'database_connections') {
                self::$services[$name] = new FakeDatabaseConnections();
            } elseif ((string)$name === 'bootstrap_runtime') {
                self::$services[$name] = new FakeBootstrapRuntime();
            } elseif ((string)$name === 'request_input') {
                self::$services[$name] = new FakeRequestInput();
            } elseif ((string)$name === 'block_file_storage') {
                self::$services[$name] = new FakeBlockFileStorage();
            } elseif ((string)$name === 'project_tool_file_storage') {
                self::$services[$name] = new FakeProjectToolFileStorage();
            } elseif ((string)$name === 'view_router_factory') {
                self::$services[$name] = static function (string $viewClass, base $block, ?object $loaderState = null): object {
                    FakeViewClass::$lastRouter = new FakeViewRouter($block);

                    return FakeViewClass::$lastRouter;
                };
            } else {
                self::$services[$name] = new FakeGenericService($name);
            }
        }
        return self::$services[$name];
    }
}

class FakeServiceContainer implements container_interface
{
    public function has(string $id): bool
    {
        return true;
    }

    public function get(string $id, mixed ...$arguments): mixed
    {
        return FakeServiceRegistry::get($id, $arguments);
    }
}

class FakeRoleRegistry
{
    private static array $roles = [];
    public static array $calls = [];

    public static function reset(): void
    {
        self::$roles = [];
        self::$calls = [];
    }

    public static function set($condition, $result): void
    {
        self::$roles[$condition] = $result;
    }

    public static function check($condition): mixed
    {
        self::$calls[] = $condition;
        return array_key_exists($condition, self::$roles) ? self::$roles[$condition] : true;
    }
}

class FakeGenericService
{
    public ?string $name = null;

    public function __construct($name)
    {
        $this->name = $name;
    }
}

class FakeRoleService
{
    public function check(mixed $condition): mixed
    {
        return FakeRoleRegistry::check($condition);
    }
}

class FakeDatabaseConnections
{
    public array $calls = [];

    public function fixAll(string $oper, bool $setError = true): void
    {
        $this->calls[] = [$oper, $setError];
    }

    public function commitAll(): void
    {
        $this->fixAll('commit', false);
    }

    public function rollbackAll(bool $setError = true): void
    {
        $this->fixAll('rollback', $setError);
    }

    public function close(): void
    {
        $this->calls[] = ['close'];
    }
}

class FakeBootstrapRuntime
{
    public function getLoader(): FakeBootstrapLoader
    {
        return new FakeBootstrapLoader();
    }

    public function getRunner(): FakeBootstrapRunner
    {
        return new FakeBootstrapRunner();
    }

    public function parsePath(string $path): string
    {
        return $path;
    }

    public function logError(string $message): void
    {
        if (class_exists('\bootstrap', false) && property_exists('\bootstrap', 'log')) {
            \bootstrap::$log[] = $message;
        }
    }
}

class FakeBootstrapLoader
{
    public string $project = '/project';

    public string $main = '/project/app/main';

    public function loadBlockByPath(string $path): ?string
    {
        return FakeServiceRegistry::get('tab')->loadBlock($path);
    }

    public function loadBlockByMR(?string $appName, array $mainRequest): ?string
    {
        return FakeServiceRegistry::get('tab')->loadBlock(implode('/', $mainRequest));
    }
}

class FakeBootstrapRunner
{
    public array $showErrorCalls = [];

    public function showError(mixed $errMsg, string $errFile = 'error_500', bool $isEcho = true): mixed
    {
        $this->showErrorCalls[] = [$errMsg, $errFile, $isEcho];

        return $errMsg;
    }
}

class FakeRequest
{
}

class FakeRequestInput
{
    public function globalArray(string $name): array
    {
        $value = $GLOBALS[$name] ?? [];

        return is_array($value) ? $value : [];
    }

    public function globalValue(string $name, mixed $default = null): mixed
    {
        return $GLOBALS[$name] ?? $default;
    }

    public function request(): array
    {
        return $this->globalArray('_REQUEST');
    }

    public function requestValue(string $key, mixed $default = null): mixed
    {
        $request = $this->request();

        return $request[$key] ?? $default;
    }

    public function serverValue(string $key, mixed $default = null): mixed
    {
        return $_SERVER[$key] ?? $default;
    }

    public function server(): array
    {
        return $_SERVER;
    }

    public function &sessionRoot(): array
    {
        if (!isset($GLOBALS['_SESSION']) || !is_array($GLOBALS['_SESSION'])) {
            $GLOBALS['_SESSION'] = [];
        }

        return $GLOBALS['_SESSION'];
    }

    public function &sessionValue(string $group, string $name): mixed
    {
        $session =& $this->sessionRoot();
        if (!isset($session[$group]) || !is_array($session[$group])) {
            $session[$group] = [$name => null];
        } elseif (!array_key_exists($name, $session[$group])) {
            $session[$group][$name] = null;
        }

        return $session[$group][$name];
    }
}

class FakeBlockFileStorage
{
    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    public function exists(string $path): bool
    {
        return file_exists($path);
    }
}

class FakeProjectToolFileStorage
{
    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    public function makeDirectory(string $path, int $mode = 0777, bool $recursive = false): bool
    {
        return mkdir($path, $mode, $recursive);
    }

    public function read(string $path): string|false
    {
        return file_get_contents($path);
    }

    public function write(string $path, string $content): int|false
    {
        return file_put_contents($path, $content);
    }

    public function copy(string $source, string $destination): bool
    {
        return copy($source, $destination);
    }

    public function scanDirectory(string $path): array|false
    {
        return scandir($path);
    }
}

class FakeSession
{
    public ?array $args = null;
    public array $data = [];
    public array $calls = [];

    public function __construct($args = [])
    {
        $this->args = $args;
    }

    /**
     * @param mixed $default Fallback value returned when no explicit value is available.
     */
    public function get(mixed $key = null, mixed $default = null, $remove = false): mixed
    {
        $this->calls[] = ['get', func_get_args()];
        if (array_key_exists($key, $this->data)) {
            $value = $this->data[$key];
            if ($remove) {
                unset($this->data[$key]);
            }
            return $value;
        }
        return $default;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function set(mixed $key, mixed $value): mixed
    {
        $this->calls[] = ['set', func_get_args()];
        $this->data[$key] = $value;
        return $value;
    }

    public function remove(mixed $key): mixed
    {
        $this->calls[] = ['remove', func_get_args()];
        unset($this->data[$key]);
        return null;
    }
}

class FakeSubscriber
{
    public array $calls = [];

    public function subscribeForEvent($block, $eventName, $listenerMethod = 'eventHandler'): static
    {
        $this->calls[] = [__FUNCTION__, $block, $eventName, $listenerMethod];
        return $this;
    }

    public function subscribeByName($block, $broadcasterName, $eventName, $listenerMethod = 'eventHandler'): static
    {
        $this->calls[] = [__FUNCTION__, $block, $broadcasterName, $eventName, $listenerMethod];
        return $this;
    }

    public function subscribeByClass($block, $className, $eventName, $listenerMethod = 'eventHandler'): static
    {
        $this->calls[] = [__FUNCTION__, $block, $className, $eventName, $listenerMethod];
        return $this;
    }

    public function unSubscribeForEvent($block, $eventName, $listenerMethod = 'eventHandler'): static
    {
        $this->calls[] = [__FUNCTION__, $block, $eventName, $listenerMethod];
        return $this;
    }

    public function unSubscribeByName($block, $broadcasterName, $eventName, $listenerMethod = 'eventHandler'): static
    {
        $this->calls[] = [__FUNCTION__, $block, $broadcasterName, $eventName, $listenerMethod];
        return $this;
    }

    public function unSubscribeByClass($block, $className, $eventName, $listenerMethod = 'eventHandler'): static
    {
        $this->calls[] = [__FUNCTION__, $block, $className, $eventName, $listenerMethod];
        return $this;
    }

    public function broadcastEvent($block, $eventName, $data = []): static
    {
        $this->calls[] = [__FUNCTION__, $block, $eventName, $data];
        return $this;
    }
}

class FakeViewDefiner
{
    public string $parserName = 'html';

    public function getViewParserName(): string
    {
        return $this->parserName;
    }
}

class FakeViewClass
{
    public static string $format = 'html';
    public static ?object $lastRouter = null;
    public static mixed $lastFormatExceptionFactory = null;

    public static function getFormat(?callable $exceptionFactory = null): string
    {
        self::$lastFormatExceptionFactory = $exceptionFactory;

        return self::$format;
    }

    public static function getRouter($block): FakeViewRouter
    {
        self::$lastRouter = new FakeViewRouter($block);
        return self::$lastRouter;
    }
}

class FakeViewRouter
{
    public ?object $block = null;
    public array $data = [];
    public int|float $getAllCalls = 0;

    public function __construct($block = null)
    {
        $this->block = $block;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function set($key, mixed $value): static
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function getAll(): array
    {
        $this->getAllCalls++;
        return $this->data;
    }
}

class FakeTab extends tab
{
    public ?object $currentBlock = null;
    public array $currentBlockCalls = [];
    public array $tabBlocks = [];
    public array $blockStatus = [false, false];
    public array $blocksMetaByMain = [];
    public ?object $mainBlock = null;
    public array $loadBlockMap = [];
    public ?object $subscriber = null;
    public ?object $viewDefiner = null;

    public function __construct()
    {
        $this->subscriber = new FakeSubscriber();
        $this->viewDefiner = new FakeViewDefiner();
    }

    public function setCurrentBlock($block): static
    {
        $this->currentBlock = $block;
        $this->currentBlockCalls[] = $block;
        return $this;
    }

    public function setTabBlock($block, $blockName): static
    {
        $this->tabBlocks[$blockName] = $block;
        return $this;
    }

    public function checkBlockStatus(base $block): array
    {
        return $this->blockStatus;
    }

    public function getBlocksMetaByMain($blockName): array
    {
        return array_key_exists($blockName, $this->blocksMetaByMain) ? $this->blocksMetaByMain[$blockName] : [];
    }

    public function getViewClass(): ?string
    {
        return '\FanTest\_core\block\FakeViewClass';
    }

    public function getViewDefiner(): object
    {
        return $this->viewDefiner;
    }

    public function getMainBlock(): ?object
    {
        return $this->mainBlock;
    }

    public function setMainBlock($block): void
    {
        $this->mainBlock = $block;
    }

    public function getTabBlock($blockName, $allowException = true): ?object
    {
        return array_key_exists($blockName, $this->tabBlocks) ? $this->tabBlocks[$blockName] : null;
    }

    public function loadBlock($blockPath): ?string
    {
        return array_key_exists($blockPath, $this->loadBlockMap) ? $this->loadBlockMap[$blockPath] : null;
    }

    public function getSubscriber(): object
    {
        return $this->subscriber;
    }

    public function getBlockDependencies(): array
    {
        return [
            'tab' => $this,
            'requestFactory' => static fn(): object => FakeServiceRegistry::get('request'),
            'roleFactory' => static fn(): object => FakeServiceRegistry::get('role'),
            'sessionFactory' => static fn(string $nameSpace, string $group = 'block'): object => FakeServiceRegistry::get('session', [$nameSpace, $group]),
            'reflectorFactory' => static fn(): object => FakeServiceRegistry::get('reflector'),
            'runtime' => FakeServiceRegistry::get('bootstrap_runtime'),
            'localeFactory' => static fn(): object => FakeServiceRegistry::get('locale'),
            'entityFactory' => static fn(mixed ...$arguments): object => FakeServiceRegistry::get('entity', $arguments),
            'matcherFactory' => static fn(): object => FakeServiceRegistry::get('matcher'),
            'formFactory' => static fn(mixed ...$arguments): object => FakeServiceRegistry::get('form', $arguments),
            'requestInputFactory' => static fn(): object => FakeServiceRegistry::get('request_input'),
            'jsonFactory' => static fn(mixed ...$arguments): object => FakeServiceRegistry::get('json', $arguments),
            'pagerFactory' => static fn(mixed ...$arguments): object => FakeServiceRegistry::get('pager', $arguments),
            'templateFactory' => static fn(mixed ...$arguments): object => FakeServiceRegistry::get('template', $arguments),
            'applicationFactory' => static fn(): object => FakeServiceRegistry::get('application'),
            'obfuscatorFactory' => static fn(mixed ...$arguments): object => FakeServiceRegistry::get('obfuscator', $arguments),
            'imageModifyFactory' => static fn(mixed ...$arguments): object => FakeServiceRegistry::get('image_modify', $arguments),
            'blockFileStorage' => FakeServiceRegistry::get('block_file_storage'),
            'projectToolFileStorage' => FakeServiceRegistry::get('project_tool_file_storage'),
            'configFactory' => static fn(mixed ...$arguments): object => FakeServiceRegistry::get('config', $arguments),
            'databaseFactory' => static fn(mixed ...$arguments): object => FakeServiceRegistry::get('database', $arguments),
            'userFactory' => static fn(mixed ...$arguments): object => FakeServiceRegistry::get('user', $arguments),
            'logFactory' => static fn(mixed ...$arguments): object => FakeServiceRegistry::get('log', $arguments),
            'transferFactory' => static fn(mixed ...$arguments): object => FakeServiceRegistry::get('transfer', $arguments),
            'errorFactory' => static fn(): object => FakeServiceRegistry::get('error'),
            'dateFactory' => static fn(mixed ...$arguments): object => FakeServiceRegistry::get('date', $arguments),
            'viewRouterFactory' => static fn(
                string $viewClass,
                base $block,
                ?object $loaderState = null,
                ?callable $blockExceptionFactory = null
            ): object => FakeViewClass::$lastRouter = new FakeViewRouter($block),
            'metaMakerFactory' => static fn(
                base $block,
                object $reflector,
                maker_state $state,
                callable $phpArrayFileLoader,
                callable $rowFactory,
                object $fileStorage,
                ?callable $blockExceptionFactory = null
            ): object => new FakeMetaMaker($block),
            'blockFactory' => static fn(
                string $blockClass,
                string $blockName,
                object $tabService,
                ?base $containerBlock,
                array $meta,
                bool $allowMeta,
                mixed $inBranch,
                array $dependencies
            ): object => new $blockClass($blockName, $tabService, $containerBlock, $meta, $allowMeta, $inBranch, $dependencies),
            'blockExceptionFactory' => static fn(
                string $exceptionClass,
                base $block,
                string $message,
                int $code,
                ?\Exception $previous = null
            ): \Throwable => new $exceptionClass($block, $message, $code, $previous),
            'metaRowFactory' => static fn(
                maker $maker,
                array $data,
                ?row $parent = null,
                int|string|null $keyName = null,
                ?callable $rowFactory = null
            ): object => new meta_row($maker, $data, $parent, $keyName, $rowFactory),
        ];
    }
}

class FakeReflector
{
    public array $parentPaths = [];

    public function __construct()
    {
        $this->parentPaths = [
            'FanTest\_core\block\TestableBaseBlock' => __DIR__ . '/TestableBaseBlock.php',
        ];
    }

    public function getParentPaths($block): array
    {
        return $this->parentPaths;
    }
}

class FakeLocale
{
    public string $language = 'pl';

    public function getLanguage(): string
    {
        return $this->language;
    }
}

class FakeMetaMaker extends maker
{
    public ?object $block = null;
    public ?object $meta = null;
    public ?array $containerMeta = null;
    public int|float $setMainBlockMetaCalls = 0;
    public int|float $assembleBlockCalls = 0;
    public array $source = [
        'folder'    => [],
        'block'     => [],
        'parent'    => [],
        'container' => [],
    ];
    public array $mixSrcMeta = ['common' => []];

    public function __construct($block, $data = [])
    {
        $this->block = $block;
        $rowFactory = static fn(
            maker $maker,
            array $rowData,
            ?row $parent = null,
            int|string|null $keyName = null,
            ?callable $rowFactory = null
        ): object => self::createMetaRow($maker, $rowData, $parent, $keyName, $rowFactory);
        $property = new \ReflectionProperty(maker::class, 'rowFactory');
        $property->setValue($this, $rowFactory);
        $this->rootRow = $this->meta = $rowFactory($this, $data);
    }

    private static function createMetaRow(
        maker $maker,
        array $data,
        ?row $parent = null,
        int|string|null $keyName = null,
        ?callable $rowFactory = null
    ): object {
        if (class_exists('\fan\project\base\meta\row', false)) {
            return new meta_row($maker, $data, $parent, $keyName, $rowFactory);
        }

        $constructor = new \ReflectionMethod(row::class, '__construct');
        $firstParameter = $constructor->getParameters()[0] ?? null;
        $firstType = $firstParameter?->getType();
        if ($firstType instanceof \ReflectionNamedType && $firstType->getName() === maker::class) {
            return new row($maker, $data, $parent, $keyName, $rowFactory);
        }

        return new row($data);
    }

    public function setContainerMeta($containerMeta): static
    {
        $this->containerMeta = $containerMeta;
        return $this;
    }

    public function setMainBlockMeta(): static
    {
        $this->setMainBlockMetaCalls++;
        return $this;
    }

    public function assembleBlock(): row
    {
        $this->assembleBlockCalls++;
        return $this->meta;
    }

    /**
     * @param mixed $default Fallback value returned when no explicit value is available.
     */
    public function getMeta(mixed $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->meta;
        }
        $current = $this->meta;
        $path = is_array($key) ? $key : [$key];
        foreach ($path as $key) {
            if ($current instanceof row && isset($current[$key])) {
                $current = $current[$key];
            } elseif (is_array($current) && array_key_exists($key, $current)) {
                $current = $current[$key];
            } else {
                return $default;
            }
        }
        return $current;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function setMeta(mixed $key, mixed $value): static
    {
        $path = is_array($key) ? $key : [$key];
        if (count($path) === 1) {
            $this->meta[$path[0]] = $value;
            return $this;
        }

        $current = $this->meta;
        while (count($path) > 1) {
            $key = array_shift($path);
            if (!isset($current[$key]) || !($current[$key] instanceof row)) {
                $current[$key] = [];
            }
            $current = $current[$key];
        }
        $current[array_shift($path)] = $value;
        return $this;
    }

    public function getSource(mixed $key = null): mixed
    {
        if ($key !== null) {
            if (!is_array($key)) {
                return $this->source[$key] ?? null;
            }
            $current = $this->source;
            foreach ($key as $part) {
                if (!is_array($current) || !array_key_exists($part, $current)) {
                    return null;
                }
                $current = $current[$part];
            }
            return $current;
        }
        return $this->source;
    }

    public function getMixSrcMeta(): array
    {
        return $this->mixSrcMeta;
    }
}
