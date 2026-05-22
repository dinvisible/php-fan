<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\core\exception\service\fatal as fatalException;
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
class obfuscator extends \fan\core\base\service\multi
{
    private static array $instances = [];
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

    /**
     * @throws \fan\core\exception\service\fatal
     */
    protected function __construct(string $type)
    {
        $type = strtolower((string)$type);
        if (in_array($type, $this->fileType, true)) {
            $this->type = $type;
        } else {
            throw new fatalException(0, 'Incorrect file type for obfuscator "' . $type . '"', 3008);
        }

        parent::__construct(true);

        $this->_defineDir();
    }

    // ======== Static methods ======== \\

    public static function instance(string $type): static
    {
        $type = strtolower($type);
        if (!isset(self::$instances[$type])) {
            new self($type);
        }
        return self::$instances[$type];
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
        return is_file($contentFile) ? file_get_contents($contentFile) : 'Error 404! File not found.';
    }

    public function getHeaders(string $name, ?int $length = null): array
    {
        $contentFile = $this->contentDir . '/' . $name;
        if (is_file($contentFile)) {
            return [
                'contentType' => $this->type === 'css' ? 'text/css' : 'application/javascript',
                'filename'    => $this->type . '_' . $name,
                'length'      => empty($length) ? filesize($contentFile) : $length,
                'modified'    => filemtime($contentFile),
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

    protected function _saveInstance(): static
    {
        self::$instances[$this->type] = $this;
        return $this;
    }

    /**
     * @throws fatalException
     */
    protected function _defineDir(): static
    {
        if (!$this->isEnabled()) {
            return $this;
        }
        foreach ($this->dirKeys as  $k => $v) {
            $tmp = (string)$this->getConfig('PATH_' . $k, '{TEMP}/obfuscator/' . $this->type . '/' . strtolower($k));
            $this->$v = \bootstrap::parsePath($tmp);
            if (!is_dir($this->$v)) {
                if (!mkdir ($this->$v, 0750, true)) {
                    throw new fatalException('Can\'t create directory "' . $this->$v . '" for obfuscator.');
                }
            }
        }
        return $this;
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
        if (is_file($contentFile)) {
            if (!$checkObsolete) {
                return $this;
            }
            if (is_file($metaFile)) {
                $obsolete = false;
                $data = \fan\project\adapter\php_array_file::load($metaFile, []);
                foreach ($list as $v) {
                    $srcPath = BASE_DIR . '/' . $v;
                    if (!is_file($srcPath)) {
                        continue;
                    }
                    if (!isset($data[$v]['time']) || !isset($data[$v]['size'])) {
                        $obsolete = true;
                        break;
                    }
                    if ((int)$data[$v]['time'] !== (int)filemtime($srcPath) || (int)$data[$v]['size'] !== (int)filesize($srcPath)) {
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
            if (!is_readable($srcPath)) {
                throw new \RuntimeException('File "' . $v . '" isn\'t readable. Can\'t obfuscate it.');
            }

            $tmp      = file_get_contents($srcPath);
            $content .= $this->obfuscate((string)$tmp);
            if ($checkObsolete) {
                $data[$v] = [
                    'time' => filemtime($srcPath),
                    'size' => filesize($srcPath),
                ];
            }
        }

        if (file_put_contents($contentFile, $content) === false) {
            throw new \RuntimeException('Obfuscator error. Can\'t save file "' . $contentFile . '".');
        }
        if ($checkObsolete) {
            if (file_put_contents($metaFile, '<?php
return ' . var_export($data, true) .';
?>') === false) {
                throw new \RuntimeException('Obfuscator error. Can\'t save file "' . $metaFile . '".');
            }
        }

        return $this;
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
