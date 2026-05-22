<?php
declare(strict_types=1);

namespace fan\core\service;
/**
 * File-system service
 *
 * This file is part PHP-FAN (php-framework of Alexandr Nosov)
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
class file_system extends \fan\core\base\service\multi
{
    private static ?array $instances = null;

    protected string $fullPath = '';

    protected ?bool $isFile = null;

    /**
     * @var file/dir handler
     */
    protected mixed $handle = null;

    protected mixed $param = '';

    protected function __construct(string $fullPath)
    {
        parent::__construct(false);
        $this->fullPath = (string)$fullPath;
        if (file_exists($this->fullPath)) {
            $this->isFile = is_file($this->fullPath);
        } else {
            throw new \RuntimeException('File "' . $this->fullPath . '" isn\'t found.');
        }
    }

    public static function instance(?string $srcPath = null): ?self
    {
        if (!$srcPath) {
            return null;
        }
        $fullPath = \bootstrap::parsePath($srcPath);
        if (!isset(self::$instances[$fullPath])) {
            self::$instances[$fullPath] = new self($fullPath);
        }
        return self::$instances[$fullPath];
    }

    public function isFile(): ?bool
    {
        return $this->isFile;
    }

    public function isRreadable(): bool
    {
        return $this->isFile && is_readable($this->getFullPath());
    }

    public function getFullPath(): string
    {
        return $this->fullPath;
    }

    public function setReadByPart(int|float $rowsQtt = 100, string $rowSeparator = "\n", string $colSeparator = "\t", mixed $openFile = true): static
    {
        if ($this->isRreadable()) {
            $this->param['rowsQtt']      = $rowsQtt;
            $this->param['rowSeparator'] = $rowSeparator;
            $this->param['colSeparator'] = $colSeparator;
            $this->param['dataPart']     = [];
            if ($openFile) {
                $this->openFile();
            }
        }
        return $this;
    }

    public function openFile(): static
    {
        $this->closeFile();
        $this->handle = fopen($this->fullPath, 'r');
        return $this;
    }

    public function closeFile(): static
    {
        if ($this->handle) {
            fclose($this->handle);
            $this->handle = null;
        }
        return $this;
    }

    public function getPartAsString(): ?array
    {
        $data = &$this->param['dataPart'];
        $qtt  = $this->param['rowsQtt'];

        $partSize = (int)$this->getConfig('APPROX_ROW_LENGTH', 64) * (int)$qtt;
        if ($partSize > $this->getConfig('PART_SIZE', 8192)) {
            $partSize = (int)$this->getConfig('PART_SIZE', 8192);
        }
        $ret = [];

        while (count($ret) < $qtt) {
            if ($this->handle && count($data) < $qtt) {
                $tmp = fread($this->handle, $partSize);
                $srcEnc  = $this->getConfig('SOURCE_ENCODING');
                $baseEnc = (string)$this->getConfig('BASE_ENCODING', 'UTF-8');
                if ($srcEnc && (string)$srcEnc !== $baseEnc) {
                    $tmp = iconv((string)$srcEnc, $baseEnc, (string)$tmp);
                }
                if (feof($this->handle)) {
                    fclose($this->handle);
                    $this->handle = null;
                }
                $tmp = explode((string)$this->param['rowSeparator'], (string)$tmp);
                if ($data) {
                    $data[count($data) - 1] .= array_shift($tmp);
                    $data = array_merge($data, $tmp);
                } else {
                    $data = $tmp;
                }
            }

            while (count($data) > ($this->handle ? 1 : 0) && count($ret) < $qtt) {
                $ret[] = array_shift($data);
            }

            if (!$this->handle && !count($data)) {
                break;
            }
        }
        return $ret ? $ret : null;
    }

    public function getPartAsArray(): ?array
    {
        $data = $this->getPartAsString();
        if (is_null($data)) {
            return null;
        }
        $ret = [];
        foreach ($data as $v) {
            $ret[] = explode((string)$this->param['colSeparator'], (string)$v);
        }
        return $ret;
    }

}
