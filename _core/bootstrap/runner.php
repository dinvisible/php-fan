<?php

declare(strict_types=1);

namespace fan\core\bootstrap;
/**
 * Description of runner
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
 */

class runner
{
    use \fan\core\di\container_aware_trait;

    public const MAIN_ERROR_DEMONSTRATOR    = '{CORE_DIR}/error/demonstrator.php';
    public const PROJECT_ERROR_DEMONSTRATOR = '{PROJECT_DIR}/error/demonstrator.php';

    /**
     * Ini-config data
     * @var array
     */
    protected ?array $config = null;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @param array $parameters Parameter set passed into the operation.
     */
    public function run(bool $isEcho = true, string|array|null $procedure = null, array $parameters = []): mixed
    {
        try {
            ob_start([$this, 'handleOb']);

            if (empty($procedure)) {
                list($procedure, $parameters) = $this->getHandler();
            }
            $ret = call_user_func_array($procedure, empty($parameters) ? [] : $parameters);

            ob_end_clean();

            if ($isEcho) {
                $this->containerService('header')->sendHeaders();

                if (is_array($ret) && is_callable($ret)) {
                    call_user_func($ret);
                } elseif (is_scalar($ret)) {
                    echo $ret;
                }
            }

            return $ret;
        } catch (\Exception $e) {
        }
        $this->_logException($e);
        ob_end_clean();
        $this->_showExceptionError($e, $isEcho);
        return null;
    }

    public function runCli($className, $methodName): mixed
    {
        try {
            list($procedure, $addParameters) = $this->getHandler();
            $parameters = [$className, $methodName];
            if (!empty($addParameters)) {
                $parameters = array_merge($parameters, $addParameters);
            }
            $ret = call_user_func_array($procedure, $parameters);

            if (is_array($ret) && is_callable($ret)) {
                call_user_func($ret);
            } elseif (is_scalar($ret)) {
                echo $ret;
            }

            return $ret;
        } catch (\Exception $e) {
        }
        $this->_logException($e);
        $this->_showExceptionError($e, true);
        return null;
    }

    public function getHandler(): array
    {
        $handler = service('matcher')->getCurrentHandler();
        return [$handler['method'], $handler['param']];
    }

    public function handleOb(string $message): ?string
    {
        if (trim($message)) {
            if (preg_match('/\w+\s+error.+$/', $message, $matches)) {
                $errMessage = trim(strip_tags($matches[0]));
                $errNote    = htmlspecialchars($message);
            } elseif (trim(strip_tags($message)) === '') {
                $errMessage = htmlspecialchars($message);
                $errNote    = '';
            } else {
                $errMessage = trim(strip_tags($message));
                $errNote    = htmlspecialchars($message);
            }
            if ($errMessage === $errNote) {
                $errNote = '';
            }

            $errNote .= $this->containerService('request')->getInfoString();
            $this->containerService('error')->logErrorMessage($errMessage, 'Intercepted fatal error', $errNote, false, true);

            $ret = $this->showError(null, 'error_500', false);
            return $ret ? $ret : 'Error 500';
        }

        return null;
    }

    public function showError(mixed $errMsg, string $tplName = 'error_500', bool $isEcho = true): mixed
    {
        if (empty($errMsg)) {
            $host = $_SERVER['HTTP_HOST'] ?? '';
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            //ToDo: make this message by special file
            $errMsg = [
                'errMsg' => [
                    'Please could you send a message about this error to <a href="mailto:' . ADMIN_EMAIL . '?subject=Error%20reporting&amp;body=Fatal%20Error%20at%20the%20request%20' . urlencode('http://' . $host . $requestUri) . '">' . ADMIN_EMAIL . '</a>',
                    'We will do everything we can to get this fixed ASAP.'
                ]
            ];
        } elseif (!is_array($errMsg)) {
            $errMsg = ['errMsg' => [strval($errMsg)]];
        }
        if ($isEcho) {
            while (ob_get_status()) {
                ob_end_clean();
            }
        }

        include str_replace('{CORE_DIR}', CORE_DIR, self::MAIN_ERROR_DEMONSTRATOR);
        $projectPath = str_replace('{PROJECT_DIR}', PROJECT_DIR, self::PROJECT_ERROR_DEMONSTRATOR);
        if (is_file($projectPath)) {
            include $projectPath;
            $demonstrator = new \fan\project\error\demonstrator($errMsg, $tplName);
        } else {
            $demonstrator = new \fan\core\error\demonstrator($errMsg, $tplName);
        }

        if ($isEcho) {
            return $demonstrator->showTplContent();
        }
        return $demonstrator->getTplContent();
    }

    protected function _logException(\Exception $e): static
    {
        $errMsg  = 'Uncaught exception "' . get_class($e) . '" with message:' . "\n";
        if (!($e instanceof \fan\core\exception\base)) {
            $errMsg .= method_exists($e, 'getMessageForShow') ? $e->getMessageForShow() . "\n" : '';
            $errMsg .= method_exists($e, 'getErrorMessage')   ? $e->getErrorMessage()   . "\n" : '';
        }
        $errMsg .= $e->getMessage() . "\n \n";

        if (method_exists($e, 'getLogVars')) {
            $errMsg .= 'Properties: <pre>' . htmlspecialchars((string)$e->getLogVars()) . "</pre>\n";
        }

        $errMsg .= 'Thrown in ' . $e->getFile() . ' on line ' . $e->getLine() . "\n";
        $errMsg .= "Stack trace:<pre>" . $e->getTraceAsString() . '</pre>';

        \bootstrap::logError($errMsg);
        return $this;
    }

    public function _showExceptionError(\Exception $e, bool $isEcho): void
    {
        if (method_exists($e, 'getMessageForShow')) {
            $errMsg = $e->getMessageForShow();
        } elseif (method_exists($e, 'getErrorMessage')) {
            $errMsg = $e->getErrorMessage();
        } else {
            $errMsg = '';
        }

        $errFile = method_exists($e, 'getErrorFile') ? $e->getErrorFile() : 'error_500';

        $this->showError($errMsg, $errFile, $isEcho);

        if ($isEcho) {
            exit();
        }
    }

}
