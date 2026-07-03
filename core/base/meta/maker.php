<?php

declare(strict_types=1);

namespace fan\core\base\meta;
use fan\core\base\meta\row;
use fan\core\block\base;

/**
 * Meta-Data Maker
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
class maker implements \IteratorAggregate
{
    protected maker_state $state;

    /**
     * @var \fan\core\block\base Linked block
     */
    protected ?object $block = null;

    protected ?string $blockName = null;

    /**
     * Array of source Meta-data
     * Keys "folder", "parent", "block" in the Main block
     *   also contain Meta-data where key equal to name of another Blocks
     * Key "main" contain Meta-data from the Main block
     *   and can't be used in the the Main block
     * @var array
     */
    protected array $source = [
        'folder'    => ['common' => null, 'own' => null],
        'parent'    => ['common' => null, 'own' => null],
        'block'     => ['common' => null, 'own' => null],
        'container' => ['common' => null, 'own' => null],
        'main'      => ['blockName' => null],
    ];

    /**
     * Order of Assemble Meta-data for
     *   "Tab", "Current block", "Other blocks" and "Embeded blocks"
     *   "Other blocks" receive Meta-data from the Main only
     * @var array
     */
    protected array $order = [
        'tab' => [
            ['folder', 'common'   ],
            ['parent', 'common'   ],
            ['block',  'common'   ],
            ['parent', 'own'      ],
            ['folder', 'own'      ],
            ['block',  'own'      ],
            ['folder', 'blockName'],
        ],
        'current' => [
            ['folder',    'common'   ],
            ['parent',    'common'   ],
            ['block',     'common'   ],
            ['container', 'common'   ],
            ['parent',    'own'      ],
            ['folder',    'own'      ],
            ['block',     'own'      ],
            ['folder',    'blockName'],
            ['container', 'own'      ],
            ['main',      'blockName'],
        ],
        'other' => [
            'folder',
            'parent',
            'block',
        ],
        'embeded' => [
            'parent',
            'block',
        ],
    ];

    /**
     * @var \fan\core\base\meta\row
     */
    protected ?object $rootRow = null;

    /**
     * @var callable|null
     */
    private $phpArrayFileLoader = null;

    /**
     * @var callable|null
     */
    private $rowFactory = null;

    /**
     * @var callable|null
     */
    private $delayedFactory = null;

    /**
     * @var callable|null
     */
    private $blockExceptionFactory = null;

    private ?object $fileStorage = null;

    private ?\Closure $recursiveMerger = null;

    private ?\Closure $arrayAdducer = null;

    private ?\Closure $classNameResolver = null;

    public function __construct(
        base $block,
        object $reflector,
        ?maker_state $state = null,
        ?callable $phpArrayFileLoader = null,
        ?callable $rowFactory = null,
        ?callable $delayedFactory = null,
        ?callable $blockExceptionFactory = null,
        ?object $fileStorage = null,
        ?callable $recursiveMerger = null,
        ?callable $arrayAdducer = null,
        ?callable $classNameResolver = null
    )
    {
        $this->block     = $block;
        $this->blockName = $block->getBlockName();
        $this->state     = $state ?? throw new \RuntimeException('Meta maker state is not configured for meta maker.');
        $this->phpArrayFileLoader = $phpArrayFileLoader;
        $this->rowFactory = $rowFactory;
        $this->delayedFactory = $delayedFactory;
        $this->blockExceptionFactory = $blockExceptionFactory;
        $this->fileStorage = $fileStorage;
        $this->recursiveMerger = \Closure::fromCallable(
            $recursiveMerger ?? static function (mixed ...$values): mixed {
                throw new \RuntimeException('Recursive merger is not configured for meta maker.');
            }
        );
        $this->arrayAdducer = \Closure::fromCallable(
            $arrayAdducer ?? static function (mixed $value): array {
                throw new \RuntimeException('Array adducer is not configured for meta maker.');
            }
        );
        $this->classNameResolver = \Closure::fromCallable(
            $classNameResolver ?? static function (object $object): string {
                throw new \RuntimeException('Class name resolver is not configured for meta maker.');
            }
        );

        $paths = $reflector->getParentPaths($this->block);
        $this->_defineBlockMeta($paths);
        $this->_defineFolderMeta($paths);
    }

    /**
     * Handles dynamic property writes for this current component.
     *
     * @param mixed $value Value that should be applied or transformed.
     */
    public function __set(string $key, mixed $value): void
    {
        $this->block->$key = $value;
    }

    /**
     * Handles dynamic property reads for this current component.
     */
    public function __get(string $key): mixed
    {
        return $this->block->$key;
    }

    public function __call(string $method, array $arguments = []): mixed
    {
        return $this->block->{$method}(...$arguments);
    }

    final public function getIterator(): \Traversable {
        return $this->getMeta();
    }

    public function getBlock(): object
    {
        return $this->block;
    }

    public function setContainerMeta(array $containerMeta): static
    {
        $this->_setSource('container', $containerMeta);
        return $this;
    }

    public function setMainBlockMeta(): static
    {
        $tab = $this->block->getTab();
        $mainMeta = [
            $this->blockName => $tab->getBlocksMetaByMain($this->blockName)
        ];
        $this->_setSource('main', $mainMeta);
        return $this;
    }

    public function assembleTab(): array
    {
        $data = [];
        foreach ($this->getOrder('tab') as $v) {
            $key   = (string)$v[1] === 'blockName' ? $this->blockName : $v[1];
            if (isset($this->source[$v[0]][$key])) {
                $data = $this->_mergeMeta($data, $this->source[$v[0]][$key], $v[0]);
            }
        }
        return $data;
    }

    public function assembleBlock(): row
    {
        $data = [];
        foreach ($this->getOrder('current') as $v) {
            $key = (string)$v[1] === 'blockName' ? $this->blockName : $v[1];
            if (isset($this->source[$v[0]][$key])) {
                $data = $this->_mergeMeta($data, $this->source[$v[0]][$key], $v[0]);
            }
        }
        $this->rootRow = $this->createRow($data);
        return $this->rootRow;
    }

    public function assembleOther(): array
    {
        $data = [];
        foreach ($this->getOrder('other') as $key) {
            $source = $this->source[$key];
            unset($source['common']);
            unset($source['own']);
            unset($source[$this->blockName]);
            $data = $this->_mergeMeta($data, $source, null);
        }
        return $data;
    }

    public function assembleEmbeded(string $blockName): array
    {
        $data = [];
        foreach ($this->getOrder('embeded') as $key) {
            if (isset($this->source[$key][$blockName])) {
                $data = $this->_mergeMeta($data, $this->source[$key][$blockName], null);
            }
        }
        return $data;
    }

    public function getMixSrcMeta(): array
    {
        $folder    = ['common' => $this->getSource(['folder', 'common'])];
        $parent    = $this->getSource('parent');
        unset($parent['own']);
        $block     = $this->getSource('block');
        unset($block['own']);
        $container = ['common' => $this->getSource(['container', 'common'])];
        return ($this->recursiveMerger())($folder, $parent, $block, $container);
    }

    public function getOrder(string $key): array
    {
        if (isset($this->order[$key])) {
            return $this->order[$key];
        }
        throw new \OutOfBoundsException('Get Undefined Order of Meta-data "' . $key . '" in block "' . $this->blockName . '", class "' . $this->className($this->block) . '".');
    }

    /**
     * @param mixed $default Fallback value returned when no explicit value is available.
     */
    public function getMeta(string|array|null $key = null, mixed $default = null): mixed
    {
        return is_null($key) ? $this->rootRow : $this->rootRow->get($key, $default );
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function setMeta(string|array $key, mixed $value): static
    {
        $this->rootRow->set($key, $value);
        return $this;
    }

    public function getSource(mixed $key = null): mixed
    {
        return is_null($key) ? $this->source : array_get_element($this->source, $key, false);
    }

    // ============ Protected methods ============ \\
    protected function _defineBlockMeta(array $paths): void
    {
        $meta = [];
        foreach ($paths as $k => $v) {
            $meta[$k] = $this->_loadBlockSource($k, $v);
        }

        // Set Block's Meta
        $this->_setSource('block', array_shift($meta));

        // Set Parent Meta
        $parentMeta = ['common' => [], 'own' => []];
        foreach (array_reverse($meta) as $v) {
            $parentMeta = $this->_mergeMeta($parentMeta, $v, 'parent');
        }
        $this->_setSource('parent', $parentMeta);
    }

    protected function _defineFolderMeta(array $paths): void
    {
        $pathParts  = pathinfo(array_shift($paths));
        $folderPath = $pathParts['dirname'] . '/_folder.meta.php';
        if ($this->fileStorage()->exists($folderPath)) {
            $this->_setSource('folder', $this->readMetaSource(
                $this->loadPhpArrayFile($folderPath, []),
                $folderPath
            ));
        }
    }

    protected function _loadBlockSource(string $class, string $path): array
    {
        if (!$this->state->hasBlockSource($class)) {
            $metaPath = substr($path, 0, -3) . 'meta.php';
            $this->state->setBlockSource(
                $class,
                $this->fileStorage()->exists($metaPath) ?
                    $this->readMetaSource($this->loadPhpArrayFile($metaPath), $metaPath) :
                    []
            );
        }
        return $this->state->getBlockSource($class);
    }

    protected function readMetaSource(mixed $data, string $metaPath): array
    {
        if (is_array($data)) {
            return $data;
        }

        throw new \UnexpectedValueException(
            sprintf('Meta file "%s" must return an array, %s returned.', $metaPath, get_debug_type($data))
        );
    }

    /**
     * @throws \fan\project\exception\block\fatal
     */
    protected function _setSource(string $type, array $data): static
    {
        if (!key_exists($type, $this->source)) {
            throw $this->createBlockFatalException('Unknown type "' . $type . '" of source Meta-data');
        }
        $this->source[$type] = $data;
        return $this;
    }

    protected function _mergeMeta(mixed $srcData, mixed $addData, ?string $type): mixed
    {
        if (!empty($addData) && is_array($addData)) {
            foreach ($addData as $k => $v) {
                $srcData[$k] = isset($srcData[$k]) && (is_array($srcData[$k]) || is_array($v)) ?
                    $this->_mergeMeta($srcData[$k], $v, $type) :
                    $v; // ToDo: Take into account merging attributes for $type
            }
        }
        return $srcData;
    }

    protected function _makeActiveMeta(string $method, mixed $arguments = [], string|object|null $obj = null, bool $delayed = true): mixed
    {
        if (is_null($obj)) {
            $obj = $this->getBlock();
        }
        if (is_null($arguments)) {
            $arguments = [];
        } elseif (!is_array($arguments)) {
            $arguments = ($this->arrayAdducer())($arguments);
        }
        $callable = [$obj, $method];
        $ret = $delayed ? $this->createDelayedMeta($obj, $method, $arguments) : $callable(...$arguments);
        return $ret;
    }

    private function loadPhpArrayFile(string $path, mixed $default = null): mixed
    {
        if (!is_callable($this->phpArrayFileLoader)) {
            throw new \RuntimeException('PHP array file loader is not configured for meta maker.');
        }

        return ($this->phpArrayFileLoader)($path, $default);
    }

    private function fileStorage(): object
    {
        return $this->fileStorage ?? throw new \RuntimeException('Meta file storage is not configured for meta maker.');
    }

    private function recursiveMerger(): callable
    {
        if (!isset($this->recursiveMerger)) {
            $this->recursiveMerger = \Closure::fromCallable(
                static function (mixed ...$values): mixed {
                    throw new \RuntimeException('Recursive merger is not configured for meta maker.');
                }
            );
        }

        return $this->recursiveMerger;
    }

    private function arrayAdducer(): callable
    {
        if (!isset($this->arrayAdducer)) {
            $this->arrayAdducer = \Closure::fromCallable(
                static function (mixed $value): array {
                    throw new \RuntimeException('Array adducer is not configured for meta maker.');
                }
            );
        }

        return $this->arrayAdducer;
    }

    private function className(object $object): string
    {
        if (!isset($this->classNameResolver)) {
            $this->classNameResolver = \Closure::fromCallable(
                static function (object $object): string {
                    throw new \RuntimeException('Class name resolver is not configured for meta maker.');
                }
            );
        }

        return (string)($this->classNameResolver)($object);
    }

    public function getRowFactory(): callable
    {
        if (!is_callable($this->rowFactory)) {
            throw new \RuntimeException('Meta row factory is not configured for meta maker.');
        }

        return $this->rowFactory;
    }

    private function createRow(array $data, ?row $parent = null, int|string|null $keyName = null): row
    {
        $row = ($this->getRowFactory())($this, $data, $parent, $keyName);
        if (!$row instanceof row) {
            $actual = is_object($row) ? get_class($row) : gettype($row);
            throw new \UnexpectedValueException('Meta row factory returned "' . $actual . '".');
        }

        return $row;
    }

    private function createDelayedMeta(object|string $object, string $method, mixed $arguments): delayed
    {
        if (!is_callable($this->delayedFactory)) {
            throw new \RuntimeException('Delayed meta factory is not configured for meta maker.');
        }

        $delayedMeta = ($this->delayedFactory)($object, $method, $arguments);
        if (!$delayedMeta instanceof delayed) {
            $actual = is_object($delayedMeta) ? get_class($delayedMeta) : gettype($delayedMeta);
            throw new \UnexpectedValueException('Delayed meta factory returned "' . $actual . '".');
        }

        return $delayedMeta;
    }

    private function createBlockFatalException(string $message, int $code = E_USER_ERROR, ?\Exception $previous = null): \Throwable
    {
        if (!is_callable($this->blockExceptionFactory)) {
            throw new \RuntimeException('Block exception factory is not configured for meta maker.');
        }

        $exception = ($this->blockExceptionFactory)(
            '\fan\project\exception\block\fatal',
            $this->block,
            $message,
            $code,
            $previous
        );
        if (!$exception instanceof \Throwable) {
            throw new \UnexpectedValueException('Block exception factory must return a throwable object.');
        }

        return $exception;
    }

}
