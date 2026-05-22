<?php

declare(strict_types=1);

namespace fan\core\base\meta;
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
    /**
     * Cache of blocks of Meta-data
     * @var array
     */
    protected static array $metaCache = [];

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

    public function __construct(\fan\core\block\base $block)
    {
        $this->block     = $block;
        $this->blockName = $block->getBlockName();

        $paths = \fan\project\service\reflector::instance()->getParentPaths($this->block);
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
        return call_user_func_array([$this->block, $method], $arguments);
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

    public function assembleBlock(): \fan\core\base\meta\row
    {
        $data = [];
        foreach ($this->getOrder('current') as $v) {
            $key = (string)$v[1] === 'blockName' ? $this->blockName : $v[1];
            if (isset($this->source[$v[0]][$key])) {
                $data = $this->_mergeMeta($data, $this->source[$v[0]][$key], $v[0]);
            }
        }
        $this->rootRow = new \fan\project\base\meta\row($this, $data);
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
        return array_merge_recursive_alt($folder, $parent, $block, $container);
    }

    public function getOrder(string $key): array
    {
        if (isset($this->order[$key])) {
            return $this->order[$key];
        }
        throw new \OutOfBoundsException('Get Undefined Order of Meta-data "' . $key . '" in block "' . $this->blockName . '", class "' . get_class_alt($this->block) . '".');
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
        if (file_exists($folderPath)) {
            $this->_setSource('folder', $this->readMetaSource(
                \fan\project\adapter\php_array_file::load($folderPath, []),
                $folderPath
            ));
        }
    }

    protected function _loadBlockSource(string $class, string $path): array
    {
        if (!array_key_exists($class, self::$metaCache)) {
            $metaPath = substr($path, 0, -3) . 'meta.php';
            self::$metaCache[$class] = file_exists($metaPath) ?
                $this->readMetaSource(\fan\project\adapter\php_array_file::load($metaPath), $metaPath) :
                [];
        }
        return self::$metaCache[$class];
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
            throw new \fan\project\exception\block\fatal($this->block, 'Unknown type "' . $type . '" of source Meta-data');
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
            $arguments = adduceToArray($arguments);
        }
        $ret = $delayed ? new \fan\project\base\meta\delayed($obj, $method, $arguments) : call_user_func_array([$obj, $method], $arguments);
        return $ret;
    }

}
