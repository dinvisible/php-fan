<?php

declare(strict_types=1);

namespace fan\core\service;
/**
 * Description of error
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
class error extends \fan\core\base\service\single
{
    /**
     * Types of system error
     * @var array
     */
    protected array $sysErrorType = [
        E_ERROR           => 'Error',
        E_WARNING         => 'Warning',
        E_PARSE           => 'Parsing Error',
        E_NOTICE          => 'Notice',
        E_CORE_ERROR      => 'Core Error',
        E_CORE_WARNING    => 'Core Warning',
        E_COMPILE_ERROR   => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR      => 'User Error',
        E_USER_WARNING    => 'User Warning',
        E_USER_NOTICE     => 'User Notice',
        2048              => 'Runtime Notice',
    ];

    /**
     * Types of system error
     * @var array
     */
    protected array $sysErrWithoutFile = [
        E_USER_ERROR,
        E_USER_WARNING,
        E_USER_NOTICE,
    ];

    /**
     * Path of system error which ignored
     * @var array
     */
    protected array $ignorePath = [];

    /**
     * Mask of system error which need parse
     * @var number
     */
    protected int|float|null $sysMask = null;

    /**
     * Error service buckets keyed by log type.
     *
     * @var array<string, int>
     */
    protected array $sysErrMask = [];

    /**
     * Flag of system error
     * @var boolean
     */
    protected bool $isSysError = false;
    /**
     * Flag of buffering of system error
     * @var boolean
     */
    protected bool $isBufferingSysError = false;
    /**
     * Backup value of system error mask
     * @var number
     */
    protected int|float|null $bufBakSysMask = null;
    /**
     * Bufering system errors
     * @var array
     */
    protected ?array $sysErrorBuffer = null;

    /**
     * Service log
     * @var \fan\project\service\log
     */
    protected ?object $servLog = null;

    /**
     * Service email
     * @var \fan\project\service\email
     */
    protected ?object $servEmail = null;

    /**
     * Enable Parse DB-error
     * @var boolean
     */
    private bool $parseDBerror = true;

    /**
     * Enable Duplicate errors by email
     * @var boolean
     */
    private bool $duplicateByEmail = false;

    protected function __construct(bool $allowIni = true)
    {
        parent::__construct($allowIni);
        $config = $this->config;

        if (\bootstrap::isCli()) {
            $this->duplicateByEmail = false;
        } elseif (!empty($config['DUPLICATE_BY_EMAIL'])) {
            if (!is_array($config['DUPLICATE_BY_EMAIL']) && !($config['DUPLICATE_BY_EMAIL'] instanceof \fan\core\service\config\row)) {
                $config['DUPLICATE_BY_EMAIL'] = [$config['DUPLICATE_BY_EMAIL']];
            }
            foreach ($config['DUPLICATE_BY_EMAIL'] as $v) {
                if (preg_match((string)$v, (string)($_SERVER['SERVER_NAME'] ?? ''))) {
                    $this->duplicateByEmail = true;
                    break;
                }
            }
        }

        if (defined('E_RECOVERABLE_ERROR')) {
            $this->sysErrorType[E_RECOVERABLE_ERROR] = 'Catchable fatal error';
        }

        $this->sysMask = $this->readErrorMask($config->get('SYS_MASK', E_ALL));
        foreach ($config->get('SYS_ERR', []) as $type => $mask) {
            $this->sysErrMask[(string)$type] = $this->readErrorMask($mask);
        }

        foreach ($config->get('IGNORE_PATH', []) as $v) {
            if (isset($v['path']) && isset($v['mask'])) {
                foreach ($v['path'] as $p) {
                    $this->addIgnorePath($v['mask'], $p);
                }
            }
        }
    }

    public function handleError(int|float $errNo, string $errMsg, mixed $fileName = null, int|float|null $lineNum = null, mixed $errContext = null): ?bool
    {
        if ($errNo === E_DEPRECATED || $errNo === E_USER_DEPRECATED) {
            return true;
        }
        if (!error_reporting() || !($errNo & $this->sysMask)) {
            return null;
        }
        $errContext = is_array($errContext) ? $errContext : [];
        $fileName = is_null($fileName) ? '' : (string)$fileName;

        $logFileName = str_replace('\\', '/', $fileName);
        foreach ($this->ignorePath as $m => $v) {
            if ($errNo & $m) {
                foreach ($v as $p) {
                    if (substr($logFileName, 0, strlen($p)) === $p){
                        return null;
                    }
                }
            }
        }

        $errType = 'debug';
        foreach ($this->sysErrMask as $k => $v) {
            if ($errNo & $v) {
                $errType = $k;
                break;
            }
        }

        //if (function_exists('mb_convert_encoding')) {
        //    $errMsg  = mb_convert_encoding($errMsg, 'UTF-8', 'CP1251');
        //}
        $message = in_array($errNo, $this->sysErrWithoutFile) ? $errMsg : $errMsg . ' in ' . $logFileName . ' on line ' . $lineNum;
        $header  = isset($this->sysErrorType[$errNo]) ? $this->sysErrorType[$errNo] : 'Unknown system error ' . $errNo;

        if ($this->isSysError) {
            \bootstrap::logError($header . "\n" . $message);
            return null;
        }
        $this->isSysError = true;

        if ($this->isBufferingSysError) {
            $this->sysErrorBuffer[] = [
                'sys_err_no'          => $errNo,
                'sys_err_message'     => $errMsg,
                'sys_err_file_name'   => $logFileName,
                'sys_err_line_number' => $lineNum,
                'sys_err_context'     => $errContext,
                'service_err_type'    => $errType,
                'service_message'     => $message,
                'service_header'      => $header
            ];
        } else {
            $note = [];
            $t1 = '<i style="color:#999999; font-size:9px;">';
            $t2 = '</i>';
            foreach ($errContext as $v) {
                if (is_null($v)) {
                    $note[] = $t1 . 'NULL';
                } elseif (is_bool($v)) {
                    $note[] = $t1 . 'boolean' . $t2 . ' ' . ($v ? 'true' : 'false');
                } elseif (is_scalar($v)) {
                    $v = (string)$v;
                    $note[] = $t1 . gettype($v) . $t2 . ' ' . (strlen($v) > 48 ? substr($v, 0, 48) . '...' : $v);
                } elseif (is_array($v)) {
                    $note[] = $t1 . 'array' . $t2  . '[' . count($v) . ']';
                } elseif (is_object($v)) {
                    $note[] = $t1 . 'object' . $t2 . '[' . get_class($v) . ']';
                } else {
                    $note[] = $t1 . 'var' . $t2    . '[' . gettype($v) . ']';
                }
            }
            $this->_logError($errType, $message, $header, implode(', ', $note), false, $errType !== 'warn');
        }
        $this->isSysError = false;
        return null;
    }

    public function setErrorBuffering(mixed $sysMask = null): bool
    {
        if (!$this->isBufferingSysError) {
            $this->sysErrorBuffer = [];
            $this->isBufferingSysError = true;
            $this->bufBakSysMask = $this->sysMask;
            $this->sysMask = $this->readErrorMask(is_null($sysMask) ? E_ALL : $sysMask);
            return true;
        }
        return false;
    }

    public function getErrorBuffering(): ?array
    {
        return empty($this->sysErrorBuffer) ? null : $this->sysErrorBuffer;
    }

    public function offErrorBuffering(): ?array
    {
        $result = $this->getErrorBuffering();
        $this->isBufferingSysError = false;
        $this->sysMask = $this->bufBakSysMask;
        return $result;
    }

    public function addIgnorePath(mixed $mask, mixed $path): void
    {
        $mask = $this->readErrorMask($mask);
        if (!empty($path)) {
            $path = \bootstrap::parsePath((string)$path);
            if (is_dir($path)) {
                $realPath = realpath($path);
                $path = is_string($realPath) ? str_replace('\\', '/', $realPath) : null;
                if (!is_null($path) && (!isset($this->ignorePath[$mask]) || !in_array($path, $this->ignorePath[$mask]))) {
                    $this->ignorePath[$mask][] = $path;
                }
            }
        }
    }

    protected function readErrorMask(mixed $mask): int
    {
        if (is_int($mask) || is_float($mask) || is_bool($mask)) {
            return (int)$mask;
        }
        $mask = trim((string)$mask);
        if (is_numeric($mask)) {
            return (int)$mask;
        }

        throw new \UnexpectedValueException('Error mask must be numeric. Got "' . $mask . '".');
    }

    public function setParseDBerror(bool $valj): void
    {
        $this->parseDBerror = $valj ? true : false;
    }

    public function logDatabaseError(mixed $connectionName, string $operation, mixed $errorMessage, int|float $errorNum, mixed $parsedSql): void
    {
        if (!$this->parseDBerror) {
            return;
        }

        $message = '<div style="color: #990000;">' . htmlentities((string)preg_replace('/\s*(\n*\r+|\r*\n+)+\s*/s', ' ', (string)$errorMessage)) . '</div>';
        $header  = 'Data Base Error: ' . $connectionName . ' - ' . $operation . ', Error No ' . $errorNum;
        $note    = $parsedSql ? htmlentities((string)$parsedSql) : '';

        $this->_logError('sql', $message, $header, $note, true, true);
    }

    public function logSoapError(object $soapError): string
    {
        $errMsg = 'Error ' . $soapError->getCode() . ': ' . $soapError->getMessage();
        $this->_logError('soap', $errMsg, 'Soap Error', '', true, true);
        return $errMsg;
    }


    public function logErrorMessage(string $message, string $header = '', string $note = '', bool $isTrace = false, bool $duplicateByEmail = false): void
    {
        $this->_logError('custom', $message, $header ? $header : 'Custom error', $note, $isTrace, $duplicateByEmail);
    }

    public function logExceptionMessage(string $message, string $header = '', string $note = ''): void
    {
        $this->_logError('exception', $message, $header ? $header : 'Custom error', $note, true, true);
    }

    protected function _logError(string $type, string $message, string $header, string $note, bool $isTrace, bool $duplicateByEmail): void
    {
        if (isset($_SERVER['REQUEST_METHOD']) && !in_array(strtoupper($_SERVER['REQUEST_METHOD']), ['GET', 'POST'])) {
            if (!empty($note)) {
                $note .= '<br />';
            }
            $note .= '$_SERVER = ' . var_export($_SERVER, true);
        }

        if (!$this->servLog) {
            $this->servLog = \fan\project\service\log::instance();
        }
        $this->servLog->logError($type, $message, $header, $note, $isTrace);

        if ($duplicateByEmail && $this->duplicateByEmail) {
            $this->makeErrorEmail($type, $header, $message);
        }
    }

    public function makeErrorEmail(string $type, string $subject, string $message): void
    {
        $config = $this->config;
        if ($config['MAIL_TO']) {
            $file = $config['MAIL_FILE'];
            if (strstr($subject, 'fatal') === false && $file) {
                $ctime = time();
                $file = \bootstrap::parsePath((string)$file) . $type . '.log.php';
                $fileExists = file_exists($file);

                $data = $fileExists ? \fan\project\adapter\php_array_file::load($file, ['start' => $ctime]) : ['start' => $ctime];
                $key = md5($message);
                if (isset($data[$key])) {
                    $data[$key]['qtt']++;
                } else {
                    $data[$key] = [
                        'subject' => $subject,
                        'message' => $message,
                        'qtt'     => 1,
                    ];
                }

                if ($fileExists && ($data['start'] + $config['SENT_TIME_LIMIT'] < $ctime)) {
                    $this->removePacketFile($file);
                    $data['start'] = date('d F Y H:i:s.', $data['start']);
                    $this->_sendErrorEmail('Packet email of ' . $type, var_export($data, true));
                } else {
                    file_put_contents($file, '<?php' . "\nreturn " . var_export($data, true) . ";\n" . '?>');
                    if (!$fileExists) {
                        $this->chmodPacketFile($file, 0666);
                    }
                }
            } else {
                $this->_sendErrorEmail($subject, $message);
            }
        }
    }


    public function sendPacketEmais(): void
    {
        $config = $this->config;
        if ($config['MAIL_TO'] && $config['MAIL_FILE']) {

            $ctime = time();

            $path = \bootstrap::parsePath((string)$config['MAIL_FILE']);

            $dirName = dirname($path);
            $prefix = basename($path);
            $len = strlen($prefix);

            foreach (scandir($dirName) as $v) {
                $file = $dirName . '/' . $v;
                if (substr($v, 0, $len) === $prefix && file_exists($file)) {
                    $data = \fan\project\adapter\php_array_file::load($file, []);
                    if ($data['start'] + $config['SENT_TIME_LIMIT'] < $ctime) {
                        $this->removePacketFile($file);
                        $data['start'] = date('d F Y H:i:s.', $data['start']);
                        $this->_sendErrorEmail('Packet email of ' . substr($v, $len, -8), var_export($data, true));
                    }
                }
            }
        }
    }

    private function removePacketFile(string $file): void
    {
        if (!is_file($file)) {
            return;
        }
        if (!is_writable($file)) {
            error_log('Cannot remove error packet file "' . $file . '": file is not writable.');
            return;
        }
        if (!unlink($file)) {
            error_log('Cannot remove error packet file "' . $file . '".');
        }
    }

    private function chmodPacketFile(string $file, int $mode): void
    {
        if (!file_exists($file)) {
            return;
        }
        if (!chmod($file, $mode)) {
            error_log('Cannot chmod error packet file "' . $file . '".');
        }
    }

    protected function _sendErrorEmail(string $subject, string $message): void
    {
        $config = $this->config;
        if (!$this->servEmail) {
            $this->servEmail = $this->containerService('email', 'err_message');
        }
        $this->servEmail->clearAllRecipients();
        if (isset($config['MAIL_CC'])) {
            foreach ($config['MAIL_CC'] as $v) {
                $v = trim((string)$v);
                if ($v) {
                    if (strpos($v, '/') > 0) {
                        list($email, $name) = explode('/', $v, 2);
                    } else {
                        $email = $v;
                        $name  = '';
                    }
                    $this->servEmail->addCc($email, $name);
                }
            }
        }
        $this->servEmail->send($subject, $message, (string)$config['MAIL_TO'], (string)$config['NAME_TO']);
    }

}
