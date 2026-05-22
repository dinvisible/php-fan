<?php

declare(strict_types=1);

namespace FanTest\_core\block;

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
            return \fan\project\service\error::instance();
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
            } else {
                self::$services[$name] = new FakeGenericService($name);
            }
        }
        return self::$services[$name];
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

class FakeRequest
{
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

    public static function getFormat(): string
    {
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

class FakeTab extends \fan\core\service\tab
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

    public function checkBlockStatus(\fan\core\block\base $block): array
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

class FakeMetaMaker extends \fan\core\base\meta\maker
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
        $this->rootRow = $this->meta = class_exists('\fan\project\base\meta\row', false)
            ? new \fan\project\base\meta\row($this, $data)
            : new \fan\core\base\meta\row($data);
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

    public function assembleBlock(): \fan\core\base\meta\row
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
            if ($current instanceof \fan\core\base\meta\row && isset($current[$key])) {
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
            if (!isset($current[$key]) || !($current[$key] instanceof \fan\core\base\meta\row)) {
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
