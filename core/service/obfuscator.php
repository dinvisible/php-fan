<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\core\base\service\multi;

/**
 * Paiment-maker service
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
 * @author: Alex Nosov (alex@4n.com.ua)
 * @version of file: 05.02.008 (15.09.2015)
 */
class obfuscator extends multi
{
    /**
     * List of Engines by TA Types
     * @var array
     */
    private array $fileType = [
        'css',
        'js',
    ];
    /**
     * Current Type of File (css or js)
     * @var numeric
     */
    protected ?string $type = null;
    /**
     * Path to directory with content files
     * @var string
     */
    protected ?string $contentDir = null;
    /**
     * Path to directory with META-files
     * @var string
     */
    protected ?string $metaDir = null;
    /**
     * Keys for check/make directories
     * @var array
     */
    protected array $dirKeys = [
        'CONTENT' => 'contentDir',
        'META'    => 'metaDir',
    ];

    private ?object $engine = null;

    /**
     * @var callable|null
     */
    private $phpArrayFileLoader = null;

    private ?object $fileStorage = null;

    /**
     * @throws \Throwable
     */
    public function __construct(
        string $type,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null,
        ?callable $phpArrayFileLoader = null,
        ?object $fileStorage = null
    )
    {
        $type = strtolower((string)$type);
        $isSupportedType = in_array($type, $this->fileType, true);
        if ($isSupportedType) {
            $this->type = $type;
        }
        $this->phpArrayFileLoader = $phpArrayFileLoader;
        $this->fileStorage = $fileStorage;

        parent::__construct(true, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory);

        if (!$isSupportedType) {
            throw $this->createServiceFatalException('Incorrect file type for obfuscator "' . $type . '"', 3008);
        }

        $this->_defineDir();
    }

    // ======== Main Interface methods ======== \\

    public function getNewList(array $fileList): array
    {
        if (!$this->isEnabled()) {
            return $fileList;
        }

        $method = '_makeNew' . ucfirst($this->type) . 'List';
        return $this->$method($fileList);
    }

    public function obfuscate(string $text): string
    {
        $engine = $this->getConfig('ENGINE');
        return empty($engine) ? $text : $this->_getEngine($engine)->obfuscate($text);
    }

    public function getFileData(string $name): string|false
    {
        $contentFile = $this->contentDir . '/' . $name;
        return $this->fileStorage()->isFile($contentFile) ? $this->fileStorage()->read($contentFile) : 'Error 404! File not found.';
    }

    public function getHeaders(string $name, ?int $length = null): array
    {
        $contentFile = $this->contentDir . '/' . $name;
        if ($this->fileStorage()->isFile($contentFile)) {
            return [
                'contentType' => $this->type === 'css' ? 'text/css' : 'application/javascript',
                'filename'    => $this->type . '_' . $name,
                'length'      => empty($length) ? $this->fileStorage()->size($contentFile) : $length,
                'modified'    => $this->fileStorage()->modifiedTime($contentFile),
                //'cacheLimit'  => 0,
            ];
        }
        return [
            'response'    => 404,
            'contentType' => 'text/plain',
            'filename'    => 'error_404',
            'length'      => empty($length) ? null : $length,
        ];
    }

    public function getConfig(mixed $key = null, mixed $default = null): mixed
    {
        return parent::getConfig(is_array($key) ? $key : [$this->type, $key], $default);
    }

    public function isEnabled(): bool
    {
        return (bool)$this->getConfig('ENABLED', false);
    }

    public function resetEnabled(): static
    {
        $this->_getConfigurator()->reset('obfuscator', [$this->type, 'ENABLED']);
        return $this;
    }

    // ======== Private/Protected methods ======== \\

    /**
     * @throws \Throwable
     */
    protected function _defineDir(): static
    {
        if (!$this->isEnabled()) {
            return $this;
        }
        foreach ($this->dirKeys as  $k => $v) {
            $tmp = (string)$this->getConfig('PATH_' . $k, '{TEMP}/obfuscator/' . $this->type . '/' . strtolower($k));
            $this->$v = $this->runtime()->parsePath($tmp);
            if (!$this->fileStorage()->isDirectory($this->$v)) {
                if (!$this->fileStorage()->makeDirectory($this->$v, 0750, true)) {
                    throw $this->createServiceFatalException('Can\'t create directory "' . $this->$v . '" for obfuscator.');
                }
            }
        }
        return $this;
    }

    private function runtime(): object
    {
        return $this->serviceBootstrapRuntime();
    }

    protected function _makeNewCssList(array $fileList): array
    {
        $newList = [];
        foreach ($fileList as $type => $tmp) {
            foreach ($tmp as $media => $list) {
                $newList[$type][$media] = $this->_makeNewList($list);
            }
        }
        return $newList;
    }
    protected function _makeNewJsList(array $fileList): array
    {
        $newList = [];
        foreach ($fileList as $type => $list) {
            $newList[$type] = $this->_makeNewList($list);
        }
        return $newList;
    }
    protected function _makeNewList(array $list): array
    {
        if (empty($list)) {
            return [];
        }

        $glue   = (bool)$this->getConfig('GLUE', true);
        $prefix = ''; // Url prefix,
        $names  = [];
        $key    = 0;
        foreach ($list as $k => $v) {
            $v = (string)$v;
            if (preg_match('/^https?\:\/\/\w+\.\w+/', $v)) {
                if (!empty($names[$key])) {
                    $key++;
                }
                $names[$key] = $v;
                $key++;
            } else{
                $names[$key][] =  substr($v, 0, 1) === '/' ? $v : $prefix . $v;
                if (!$glue) {
                    $key++;
                }
            }
        }

        $newList = [];
        $handler = (string)$this->getConfig('HANDLER', '/get_' . $this->type . '/');
        foreach ($names as $v1) {
            if (is_string($v1)) {
                $newList[] = $v1;
            } else {
                $name = md5(implode('-', $v1));
                $newList[] = $handler . $name;
                $this->_makeFile($v1, $name);
            }
        }

        return $newList;
    }

    protected function _makeFile(array $list, string $name): static
    {
        $checkObsolete = $this->getConfig('CHECK_OBSOLETE', true);
        $contentFile   = $this->contentDir . '/' . $name;
        $metaFile      = $this->metaDir . '/' . $name;

        // Check - is content exists and isn't obsolete
        if ($this->fileStorage()->isFile($contentFile)) {
            if (!$checkObsolete) {
                return $this;
            }
            if ($this->fileStorage()->isFile($metaFile)) {
                $obsolete = false;
                $data = $this->loadPhpArrayFile($metaFile, []);
                foreach ($list as $v) {
                    $srcPath = BASE_DIR . '/' . $v;
                    if (!$this->fileStorage()->isFile($srcPath)) {
                        continue;
                    }
                    if (!isset($data[$v]['time']) || !isset($data[$v]['size'])) {
                        $obsolete = true;
                        break;
                    }
                    if ((int)$data[$v]['time'] !== (int)$this->fileStorage()->modifiedTime($srcPath) || (int)$data[$v]['size'] !== (int)$this->fileStorage()->size($srcPath)) {
                        $obsolete = true;
                        break;
                    }
                }
                if (!$obsolete) {
                    return $this;
                }
            }
        }

        // Make content of files
        $content = '';
        $data    = [];
        foreach ($list as $v) {
            $srcPath = BASE_DIR . '/' . $v;
            if (!$this->fileStorage()->isReadable($srcPath)) {
                throw new \RuntimeException('File "' . $v . '" isn\'t readable. Can\'t obfuscate it.');
            }

            $tmp      = $this->fileStorage()->read($srcPath);
            $content .= $this->obfuscate((string)$tmp);
            if ($checkObsolete) {
                $data[$v] = [
                    'time' => $this->fileStorage()->modifiedTime($srcPath),
                    'size' => $this->fileStorage()->size($srcPath),
                ];
            }
        }

        if ($this->fileStorage()->write($contentFile, $content) === false) {
            throw new \RuntimeException('Obfuscator error. Can\'t save file "' . $contentFile . '".');
        }
        if ($checkObsolete) {
            if ($this->fileStorage()->write($metaFile, '<?php
return ' . var_export($data, true) .';
?>') === false) {
                throw new \RuntimeException('Obfuscator error. Can\'t save file "' . $metaFile . '".');
            }
        }

        return $this;
    }

    private function loadPhpArrayFile(string $path, mixed $default = null): mixed
    {
        if (!is_callable($this->phpArrayFileLoader)) {
            throw new \RuntimeException('PHP array file loader is not configured for obfuscator service.');
        }

        return ($this->phpArrayFileLoader)($path, $default);
    }

    private function fileStorage(): object
    {
        if ($this->fileStorage === null) {
            throw new \RuntimeException('File storage dependency is not configured for obfuscator service.');
        }

        return $this->fileStorage;
    }

    /**
     * @throws \fan\core\exception\service\fatal
     */
    protected function _getDelegate(mixed $class): mixed
    {
        if (empty($this->engine)) {
            $this->engine = $this->_getEngine($this->type);
        }
        return $this->engine;
    }
}
