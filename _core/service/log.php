<?php
declare(strict_types=1);

namespace fan\core\service;
/**
 * Description of log
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
class log extends \fan\core\base\service\single
{
    /**
     * Array of log parcers
     * @var array
     */
    protected ?array $parcers = null;

    /**
     * File Directory of each type
     * @var string
     */
    protected ?array $dir = null;

    /**
     * File name of each type
     * @var string
     */
    protected ?array $file = null;

    /**
     * Log file is new
     * @var bolean
     */
    protected bool $isNewFile = false;

    /**
     * @param string $file File path or file descriptor handled by the operation.
     */
    public function getLogParser(string $variety, string $file): \fan\core\service\log\parser_base
    {
        if (!isset($this->parcers[$variety][$file])) {
            $engine = $this->_getEngine('parser_'  . $variety);
            $this->parcers[$variety][$file] = $engine;
            if (empty($engine)) {
                throw new \fan\project\exception\service\fatal($this, $variety ? 'Incorrect Variety of log-file: "' . $variety . '"' : 'Unset Variety of log-file.');
            }
            $engine->setFilePath($variety, $file);
        } else {
            $this->parcers[$variety][$file]->checkIndex();
        }
        return $this->parcers[$variety][$file];
    }

    /**
     * @param ?string $file File path or file descriptor handled by the operation.
     */
    public function logData(string $type, mixed $data, string $title, string $note = '', int|float|null $dataDepth = null, bool $isTrace = true, ?string $file = null): void
    {
        if (is_null($dataDepth) || $dataDepth < 1) {
            $dataDepth = $this->getConfig('DATA_DEPTH', 4);
        }
        $row = $this->_setAttribute($title);
        $row['data'] = $this->_setNewData($data, $dataDepth);
        $this->_setNote($row, $note);
        if ($isTrace) {
            $row['trace'] = $this->_getTrace();
        }
        $this->_saveLog('data', $type, $row, $file);
    }

    /**
     * @param ?string $file File path or file descriptor handled by the operation.
     */
    public function logError(string $type, string $message, string $title, string $note = '', bool $isTrace = true, ?string $file = null): void
    {
        $row = $this->_setAttribute($title);
        $row['main_msg'] = $message;
        $this->_setNote($row, $note);
        if ($isTrace) {
            $row['trace'] = $this->_getTrace();
        }
        $this->_saveLog('error', $type, $row, $file);
    }

    /**
     * @param ?string $file File path or file descriptor handled by the operation.
     */
    public function logMessage(string $type, string $message, string $title, string $note = '', ?string $file = null): void
    {
        $row = $this->_setAttribute($title);
        $row['main_msg'] = $message;
        $this->_setNote($row, $note);
        $this->_saveLog('message', $type, $row, $file);
    }

    protected function _setAttribute(string $title): array
    {
        $row = [];
        $row['method'] = !isset($_SERVER['REQUEST_METHOD']) ? 'CLI' : $_SERVER['REQUEST_METHOD'];
        if (!empty($this->config['SET_PROTOCOL'])) {
            $row['protocol'] = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        }
        if ($row['method'] !== 'CLI' && (!empty($this->config['SET_DOMAIN']) || !empty($this->config['SET_PROTOCOL']))) {
            $row['domain'] = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null;
        }
        $row['request'] = $row['method'] !== 'CLI' ? ($_SERVER['REQUEST_URI'] ?? '') : (isset($_SERVER['argv']) ? implode(' ', $_SERVER['argv']) : 'No path');

        $row['header']  = $title;
        return $row;
    }

    protected function _setNewData(mixed $data, int|float $dataDepth): array
    {
        $isRow = is_object($data) && is_subclass_of($data, '\fan\core\base\model\row');
        $dtEl = [
            'type' => $isRow ? get_class($data) . ':' : (is_object($data) ? 'object:' . get_class($data) : gettype($data)),
        ];

        if (is_null($data)) {
            $dtEl['singular'] = 'NULL';
        } elseif (is_scalar($data)) {
            $dtEl['singular'] = is_bool($data) ? ($data ? 'true' : 'false') : $this->_checkIncorrectSymbol((string)$data, 'scalar_val', 2048);
        } elseif (is_array($data) || is_object($data)) {
            if ($dataDepth < 1) {
                $dtEl['singular'] = is_array($data) ? 'array[' . count($data) . ']' : 'object:' . get_class($data);
            } else {
                $dtEl['multiple'] = [];
                if ($isRow) {
                    foreach ($data->getDebugInfo() as $k => $v) {
                        $dtEl['multiple'][$k] = $this->_setNewData($v, $dataDepth);
                    }
                } else {
                    foreach ($data as $k => $v) {
                        $dtEl['multiple'][$this->_checkIncorrectSymbol((string)$k, 'mp_key', 64)] = $this->_setNewData($v, $dataDepth - 1);
                    }
                }
                if (empty($dtEl['multiple'])) {
                    $dtEl['singular'] = is_array($data) ? 'Empty array' : 'Object:' . get_class($data) . ' without public property.';
                    unset($dtEl['multiple']);
                }
            }
        } else {
            $dtEl['singular'] = $this->_checkIncorrectSymbol(var_export($data, true), 'any_var', 4096);
        }

        return $dtEl;
    }

    protected function _setNote(array &$row, string $note): void
    {
        if ($note) {
            $row['note'] = $this->_checkIncorrectSymbol($note, 'note', 4096);
        }
    }

    protected function _getTrace(): array
    {
        $ret = [];
        $tmp = debug_backtrace();
        $backTrace = [];
        for ($i = count($tmp) - 1; $i >= 0; $i--) {
            $backTrace[$i]['file'] = isset($tmp[$i]['file']) ? $tmp[$i]['file'] : '';
            $backTrace[$i]['line'] = isset($tmp[$i]['line']) ? $tmp[$i]['line'] : '';
            $backTrace[$i]['function'] = (isset($tmp[$i]['class']) ? $tmp[$i]['class'] . $tmp[$i]['type'] : '') . $tmp[$i]['function'];
            $backTrace[$i]['args'] = isset($tmp[$i]['args']) ? $tmp[$i]['args'] : [];
            if (isset($tmp[$i]['class']) && preg_match('/^(?:core|project)\\\\service\\\\(?:log|error)$/', $tmp[$i]['class'])) {
                break;
            }
        }
        ksort($backTrace);
        foreach ($backTrace as $v) {
            $call = [];
            if ($v['file']) {
                $call['file'] = $v['file'];
            }
            if ($v['file']) {
                $call['line'] = $v['line'];
            }
            $call['func'] = $v['function'];
            if ($v['args']) {
                $call['arg'] = [];
                foreach ($v['args'] as $arg) {
                    if (is_null($arg)) {
                        $s = 'NULL';
                    } elseif (is_scalar($arg)) {
                        $s = is_bool($arg) ? ($arg ? 'true' : 'false') : $this->_checkIncorrectSymbol((string)$arg, 'argument', 128);
                    } elseif (is_array($arg)) {
                        $s = 'Array[' . count($arg) . ']';
                    } elseif (is_object($arg)) {
                        $s = 'Object:' . get_class($arg);
                    } else {
                        $s = $this->_checkIncorrectSymbol(var_export($arg, true), 'argument', 128);
                    }
                    $call['arg'][] = [gettype($arg), str_replace ('&', '&amp;',$s)];
                }
            }
            $ret[] = $call;
        }
        return $ret;
    }

    /**
     * @param string $file File path or file descriptor handled by the operation.
     */
    protected function _saveLog(string $variety, string $type, array $row, ?string $file): void
    {
        $row  = date('H:i:s') . "\t" . $type . "\t";
        if ($this->getConfig(['USE_PID', $variety], false)) {
            $row .= \bootstrap::getPid() . "\t";
        }
        $row .= addcslashes(\fan\core\adapter\safe_serializer::encodeJson($row), "\\\t\r\n\0") . "\n";
        error_log($row, 3, $this->_getFullPath($variety, $file));
    }

    /**
     * @param mixed $file File path or file descriptor handled by the operation.
     */
    protected function _getFullPath(string $variety, mixed $file): string
    {
        if (!isset($this->dir[$variety])) {
            $this->dir[$variety] = \bootstrap::parsePath((string)$this->config['LOG_DIR'][$variety]);
            if (!is_writable($this->dir[$variety])) {
                throw new \fan\project\exception\fatal('Directory "' . $file . '" isn\'t writable.');
            }
        }

        if ($file) {
            $file = (string)$file;
            $dir = dirname($file);
            if ($dir) {
                if (!is_dir($dir)) {
                    $dir = $this->dir[$variety] . '/' . $dir;
                    if (!is_dir($dir)) {
                        throw new \fan\project\exception\fatal('Incorrect log-file path "' . $file . '".');
                    }
                }
                if (!is_writable($dir)) {
                    throw new \fan\project\exception\fatal('Directory "' . $file . '" isn\'t writable.');
                }
                return $dir . '/' . $file;
            }
            return $this->dir[$variety] . '/' . $file;
        }

        for ($i = 0; $i < 1000; $i++) {
            $file = date('Y-m-d') . '_' . str_pad((string)$i, 3, '0', STR_PAD_LEFT) . '.log';
            $fullPath = $this->dir[$variety] . '/' . $file;
            if (!file_exists($fullPath) || is_writable($fullPath) && filesize($fullPath) < (int)$this->getConfig('MAX_FILE_SIZE', 1000000)) {
                break;
            }
        }
        return $fullPath;
    }

    protected function _checkIncorrectSymbol(string $str, ?string $limitKey = null, ?int $limitDefault = null): string
    {
        $limit = (int)$this->getConfig(['LEN_LIMIT', $limitKey], is_null($limitDefault) ? 16384 : $limitDefault);
        $str = (string)$str;
        $k = min(strlen($str), $limit);
        $ret = $k < strlen($str) ? '[REDUCED]' : '';
        for ($i=0; $i < $k; $i++) {
            $c = substr($str, $i, 1);
            $n = ord($c);
            if ($n === 0) {
                return '[BINARY CODE]';
            } elseif ($n & 0x80) {
                if ($n & 0x40) {
                    $n1 = $n & 0x7F;
                    $c1 = $c;
                    $b  = true;
                    for ($j = 1; ($j <= 5) && ($n1 & 0x40); $j++, $n1 = $n1 << 1) {
                        $c = substr($str, $i + $j, 1);
                        if ((ord($c) & 0xC0) === 0x80) {
                            $c1 .= $c;
                        } else {
                            $b = false;
                            break;
                        }
                    }
                    if ($b) {
                        $ret .= $c1;
                        $i += ($j - 1);
                    } else {
                        $ret .= '♣';
                    }
                } else {
                    $ret .= '♠';
                }
            } elseif ($n < 0x20 && $n !== 0x0D && $n !== 0x0A && $n !== 0x09) {
                $ret .= '♥';
            } elseif ($n === 127) {
                $ret .= '♦';
            } else {
                $ret .= $c;
            }
        }

        return $ret;
    }

}
