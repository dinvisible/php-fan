<?php

declare(strict_types=1);

namespace fan\core\exception;
/**
 * Exception base class
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
 * @version of file: 05.02.011 (03.10.2015)
 * @abstract
 */
abstract class base extends \Exception
{
    use \fan\core\di\container_aware_trait;

    /**
     * File to show the error for the user
     * @var string
     */
    protected ?string $showErrFile = null;

    /**
     * Public error message for the user
     * @var string
     */
    protected string $showErrMsg = '';

    /**
     * Public error message for the user
     * @var string
     */
    protected string $logErrMsg = '';

    /**
     * Operation with DB when exception occured
     * Possible values: 'rollback', 'commit' or NULL
     * @var string
     */
    private ?string $dbOper = null;

    /**
     * Vars are excluded for Logging
     * @var array
     */
    protected array $excludeLogVars = [
        'excludeLogVars',
        'message',
        'file',
        'line',
        'xdebug_message',
    ];

    public function __construct(string $logErrMsg, int $code = E_USER_ERROR, ?\Throwable $previous = null)
    {
        $this->logErrMsg = $logErrMsg;
        if (empty($this->showErrMsg)) {
            $this->showErrMsg = 'Please visit the site later.';
        }
        if (empty($this->showErrFile)) {
            $this->showErrFile = 'error_500';
        }

        $this->dbOper = $this->_defineDbOper();
        if (!empty($this->dbOper) && class_exists('\fan\core\service\database', false)) {
            \fan\project\service\database::fixAll($this->dbOper);
        }

        if (!empty($previous) && $previous instanceof \Exception) {
            parent::__construct((string)$logErrMsg, (int)$code, $previous);
        } else {
            parent::__construct((string)$logErrMsg, (int)$code);
        }
    }

    public function getErrorFile(): string
    {
        return $this->showErrFile;
    }

    public function getMessageForLog(): string
    {
        return $this->logErrMsg;
    }

    public function getMessageForShow(): string
    {
        return $this->showErrMsg;
    }

    public function getDbOper(): ?string
    {
        return $this->dbOper;
    }

    public function getLogVars(): string
    {
        $vars = [];
        $tmp  = get_object_vars($this);
        foreach ($tmp as $k => $v) {
            if (in_array($k, $this->excludeLogVars)) {
                continue;
            } elseif (is_scalar($v)) {
                $vars[$k] = $v;
            } elseif (is_null($v)) {
                $vars[$k] = 'NULL';
            } elseif (is_array($v)) {
                $vars[$k] = 'array[' . count($v) . ']';
            } elseif (is_object($v)) {
                $vars[$k] = 'object of "' . get_class($v) . '"';
            } else {
                $vars[$k] = strval($v);
            }
        }
        return var_export($vars, true);
    }

    protected function _logByPhp(string $errMsg, bool $exceptPos = true): static
    {
        if ($exceptPos) {
            $errMsg .= ' Error at the ' . str_replace('\\', '/', $this->file) . ', line ' . $this->line;
        }
        \bootstrap::logError($errMsg);
        return $this;
    }

    protected function _logByService(string $errMsg, string $errTitle = '', string $note = ''): static
    {
        if (!$note) {
            $note = self::staticContainerService('request')->getInfoString();
            if (!empty($_POST)) {
                $note .= "\nPOST = " . var_export($_POST, true);
            }
        }
        $this->containerService('error')->logExceptionMessage($errMsg, $errTitle ? $errTitle : 'Log exception', $note);
        return $this;
    }

    protected function _defineDbOper(?string $dbOper = null): ?string
    {
        if (in_array($dbOper, ['rollback', 'commit'])) {
            return (string)$dbOper;
        } elseif (!empty($dbOper) && (string)$dbOper !== 'nothing') {
            throw new \InvalidArgumentException('Incorret DB-operation name for Ecxeption ' . get_class($this));
        }
        return null;
    }

}
