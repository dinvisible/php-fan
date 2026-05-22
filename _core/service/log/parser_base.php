<?php
declare(strict_types=1);

namespace fan\core\service\log;
/**
 * Base parser of log file
 * [
 *     0 => offset,
 *     1 => prefix length,
 *     2 => main length,
 *     3 => time,
 *     4 => md5 of main message,
 *     5 => type of message;
 * ]
 *     6 => is PID;
 * )
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
abstract class parser_base
{

    /**
     * Service Log
     * @var \fan\core\service\log
     */
    protected ?object $facade = null;

    /**
     * Key of dir by Bootstrap
     * @var string
     */
    protected ?string $logDirKey = null;

    /**
     * Type of record is available
     * @var boolean
     */
    protected bool $isType = true;
    /**
     * Is serialized
     * @var boolean
     */
    protected bool $isSerialized = true;
    /**
     * Is PID in the log-file
     * @var boolean
     */
    protected bool $isPid = true;

    /**
     * Path to data file
     * @var string
     */
    private string $dataFile = '';

    /**
     * Path to index file
     * @var string
     */
    private string $indxFile = '';

    /**
     * Size of file
     * @var integer
     */
    protected int|float $size = 0;

    /**
     * Index data
     * @var array
     */
    protected ?array $indxData = null;

    /**
     * Keys of Unique Index data
     * @var array
     */
    protected array $uniqueKeys = [];

    /**
     * Keys of Similar data
     * @var array
     */
    protected array $similarKeys = [];

    /**
     * @param mixed $file File path or file descriptor handled by the operation.
     */
    public function setFilePath(string $variety, string $file): void
    {
        $variety = (string)$variety;
        $file = (string)$file;
        $logDir = $this->logDirKey ?
            \bootstrap::getGlobalPath($this->logDirKey) :
            \bootstrap::parsePath((string)$this->facade->getConfig(['LOG_DIR', $variety]));
        $this->dataFile = $logDir . '/' . $file . '.log';
        $this->indxFile = $logDir . '/' . $file . '.i0.php';

        $this->isPid = (bool)$this->facade->getConfig(['USE_PID', $variety], false);

        $this->checkIndex();
    }

    public function setFacade(\fan\core\base\service $facade): void
    {
        $this->facade = $facade;
    }

    public function checkIndex(): bool
    {
        $curSize = is_file($this->dataFile) ? (int)filesize($this->dataFile) : 0;
        if ($curSize === 0) {
            $this->_removeFile();
            $this->indxData = null;
            return false;
        }

        $curTime = (int)filemtime($this->dataFile);
        if (!is_readable($this->indxFile) || filemtime($this->indxFile) < $curTime) {
            $this->_recreateIndex();
        } else {
            $tmp = \fan\project\adapter\php_array_file::load($this->indxFile, []);
            if ((int)$tmp['size'] === $curSize && (int)$tmp['time'] === $curTime) {
                $this->indxData = $tmp['data'];
                $this->size     = $curSize;
            } else {
                $this->_recreateIndex();
            }
        }
        return true;
    }


    public function isData(bool $reindex = false): bool
    {
        if ($reindex) {
            $this->checkIndex();
        }
        return !is_null($this->indxData);
    }

    public function getQtt(bool $isUnique = false): ?int
    {
        if (is_null($this->indxData)) {
            return null;
        }
        if ($isUnique) {
            $this->_setUniqueKeys();
            return count($this->uniqueKeys);
        }
        return count($this->indxData);
    }

    public function checkAfterLast(mixed $lastKey, bool $isUnique = false): ?int
    {
        if (!is_null($this->indxData) && !is_null($lastKey)) {
            if ($isUnique) {
                $this->_setUniqueKeys();
                $k = array_search ($lastKey, $this->uniqueKeys);
                return $k === false ? 0 : (isset($this->uniqueKeys[$k + 1]) ? $k + 1 : null);
            }
            return isset($this->indxData[$lastKey + 1]) ? $lastKey + 1 : null;
        }
        return null;
    }


    public function getDataArr(int $first, int $qtt, bool $isUnique = false): ?array
    {
        if (is_null($this->indxData)) {
            return null;
        }
        if ($isUnique) {
            $this->_setUniqueKeys();
        }

        $records = [];
        $f = fopen($this->dataFile, 'r');
        for ($i = 0; $i < $qtt; $i++) {
            $id = $i + $first;
            if ($isUnique) {
                if (!isset($this->uniqueKeys[$id])) {
                    break;
                }
                $id = $this->uniqueKeys[$id];
            }
            $ind = $this->indxData[$id] ?? null;
            if (!$ind) {
                break;
            }

            fseek($f, (int)($ind[0] + $ind[1]));
            $rd = $this->isSerialized ? $this->decodeLogRowPayload(stripcslashes(fread($f, (int)$ind[2]))) :
                [
                    'method'   => '',
                    'request'  => '',
                    'header'   => '',
                    'main_msg' => stripcslashes(fread($f, (int)$ind[2])),
                ];
            $records[$i] = [
                'id'     => $id,
                'attr'   => [
                    'time'   => $ind[3],
                    'type'   => isset($ind[5]) ? $ind[5] : '',
                ],
                'header' => $rd['header'],
            ];
            if ($this->isPid && isset($ind[6])) {
                $records[$i]['attr']['pid'] = $ind[6];
            }
            foreach ([
                'method',
                'protocol',
                'domain',
                'request',
            ] as $k) {
                if (isset($rd[$k])) {
                    $records[$i]['attr'][$k] = $rd[$k];
                }
            }
            foreach ([
                'data',
                'main_msg',
                'note',
            ] as $k) {
                if (isset($rd[$k])) {
                    $records[$i][$k] = $rd[$k];
                }
            }
            if (isset($rd['trace'])) {
                $records[$i]['trace'] = 1;
            }
        }
        fclose($f);
        return $records;
    }

    public function getTrace(string $key): mixed
    {
        if (is_null($this->indxData) || !$this->isSerialized) {
            return null;
        }
        $trace = null;
        $ind = $this->indxData[$key] ?? null;
        if ($ind) {
            $f = fopen($this->dataFile, 'r');
            fseek($f, (int)($ind[0] + $ind[1]));
            $rd = $this->decodeLogRowPayload(stripcslashes(fread($f, (int)$ind[2])));
            fclose($f);
            if (isset($rd['trace'])) {
                $trace = $rd['trace'];
            }
        }
        return $trace;
    }

    protected function decodeLogRowPayload(string $payload): array
    {
        $decoded = \fan\core\adapter\safe_serializer::decodeExternalPayload(
            $payload,
            [],
            static function (string $message): void {
                error_log('Cannot decode log row payload: ' . $message);
            }
        );

        $default = [
            'method'   => '',
            'request'  => '',
            'header'   => '',
            'main_msg' => '',
        ];

        return is_array($decoded) ? $decoded + $default : $default;
    }

    public function deleteRows(array $keys, bool $isUnique = false): void
    {
        if ($isUnique) {
            $this->_setUniqueKeys(true);
        }

        $offsets = [];
        foreach ($keys as $k1) {
            $hash = $this->_setOffset($offsets, $k1);
            if ($isUnique && $hash) {
                foreach ($this->similarKeys[$hash] as $k2) {
                    $this->_setOffset($offsets, $k2);
                }
            }
        }
        ksort($offsets);

        $start = 0;
        $offsets[$this->size] = null;
        rename($this->dataFile, $this->dataFile . '.tmp');
        $fw = fopen($this->dataFile, 'w');
        $fr = fopen($this->dataFile . '.tmp', 'r');
        foreach ($offsets as $k => $v) {
            $end = $k;
            if ($start < $end) {
                if (!$this->_dataTransfer($fw, $fr, $start, $end)) {
                    // ToDo: take into account error
                    break;
                }
            }
            if ($v) {
                $start = $end + $v[0];
                unset($this->indxData[$v[1]]);
            }
        }
        fclose($fw);
        fclose($fr);
        unlink($this->dataFile . '.tmp');
        $this->_recreateIndex();
    }

    protected function _setOffset(array &$offsets, string $k): ?string
    {
        $ind = $this->indxData[$k] ?? null;
        if ($ind) {
            $offsets[$ind[0]] = [
                $ind[1] + $ind[2] + 1,
                $k,
            ];
            return $ind[4];
        }
        return null;
    }

    protected function _recreateIndex(): void
    {
        $this->uniqueKeys  = [];
        if (!filesize($this->dataFile)) {
            $this->indxData = null;
            $this->_removeFile();
            return;
        }
        $data = [];

        $str = '';
        $l = 0;

        $chunk = (int)$this->facade->getConfig('FILE_CHUNK', 8192);
        $f = fopen($this->dataFile, 'r');
        while (!feof($f)) {
            $str .= fread($f, $chunk);
            $str = explode("\n", $str);
            for ($i = 0; $i < count($str) - 1; $i++) {
                $this->_parceSting($data, $str[$i], $l);
                $l += strlen($str[$i]) + 1;
            }
            $str = $str[$i];
        }
        fclose($f);
        $this->_parceSting($data, $str, $l);

        $this->indxData    = $data;
        $this->_writeIndexFile();
    }

    protected function _parceSting(array &$data, string $str, int $l): void
    {
        $str = (string)$str;
        $l = (int)$l;
        if ($str) {
            $arr = explode("\t", $str);

            $main = end($arr);
            $len = strlen($main);

            $row = [
                0 => $l,
                1 => strlen($str) - $len,
                2 => $len,
                3 => $arr[0],
                4 => md5($main),
            ];
            if ($this->isType) {
                $row[5] = $arr[1];
            }
            if ($this->isPid) {
                $row[6] = $arr[$this->isType ? 2 : 1];
            }
            $data[] = $row;
        }
    }

    protected function _setUniqueKeys(): void
    {
        if (empty($this->uniqueKeys)) {
            $this->uniqueKeys  = [];
            $this->similarKeys = [];
            foreach ($this->indxData as $k => $v) {
                $s = $v[4];
                if (!isset($this->similarKeys[$s])) {
                    $this->uniqueKeys[] = $k;
                    $this->similarKeys[$s] = [$k];
                } else {
                    $this->similarKeys[$s][] = $k;
                }
            }
        }
    }

    protected function _dataTransfer(mixed $fw, mixed $fr, int $start, int $end): bool
    {
        $chunk = (int)$this->facade->getConfig('FILE_CHUNK', 8192);
        $start = (int)$start;
        $end = (int)$end;
        fseek($fr, $start);
        while ($start < $end) {
            $l = min($end - $start, $chunk);
            if (fwrite($fw, fread($fr, $l)) === false) {
                return false;
            }
            $start += $l;
        }
        return true;
    }

    protected function _removeFile(): void
    {
        if (is_file($this->dataFile)) {
            unlink($this->dataFile);
        }
        if (is_file($this->indxFile)) {
            unlink($this->indxFile);
        }
    }

    protected function _writeIndexFile(): void
    {
        $tmp = [
            'size' => filesize($this->dataFile),
            'time' => filemtime($this->dataFile),
            'data' => $this->indxData,
        ];
        $this->size = $tmp['size'];
        file_put_contents($this->indxFile, '<?php
/*
 * Index data
 */
return ' . var_export($tmp, true) . ';
?>');
    }
}
