<?php

declare(strict_types=1);

namespace fan\core\bootstrap;
use fan\core\exception\base;

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
    /**
     * Configuration data
     * @var array
     */
    protected ?array $config = null;

    protected ?object $matcher = null;
    protected ?object $request = null;
    protected ?object $error = null;
    protected ?object $input = null;
    protected ?object $runtime = null;
    protected ?object $header = null;
    protected array $handlerFactories = [];
    private $phpArrayFileLoader = null;
    private $errorDemonstratorFactory = null;

    public function __construct(
        $config,
        callable $phpArrayFileLoader,
        callable $errorDemonstratorFactory,
        ?object $matcher = null,
        ?object $request = null,
        ?object $error = null,
        ?object $input = null,
        ?object $runtime = null,
        ?object $header = null,
        array $handlerFactories = []
    )
    {
        $this->config = $config;
        $this->phpArrayFileLoader = \Closure::fromCallable($phpArrayFileLoader);
        $this->errorDemonstratorFactory = \Closure::fromCallable($errorDemonstratorFactory);
        $this->matcher = $matcher;
        $this->request = $request;
        $this->error = $error;
        $this->input = $input;
        $this->runtime = $runtime;
        $this->header = $header;
        $this->handlerFactories = $handlerFactories;
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
            $ret = $procedure(...(empty($parameters) ? [] : $parameters));

            ob_end_clean();

            if ($isEcho) {
                $this->header()->sendHeaders();

                if (is_array($ret) && is_callable($ret)) {
                    $ret();
                } elseif (is_scalar($ret)) {
                    echo $ret;
                }
            }

            return $ret;
        } catch (\Throwable $e) {
        }
        $this->_logException($e);
        ob_end_clean();
        $this->_showExceptionError($e, $isEcho);
        return null;
    }

    public function getHandler(): array
    {
        $handler = $this->matcher()->getCurrentHandler();
        $procedure = $handler['method'];
        if (!empty($handler['service'])) {
            $procedure = [$this->handlerService((string)$handler['service']), (string)$handler['method']];
        }

        return [$procedure, $handler['param']];
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

            $errNote .= $this->request()->getInfoString();
            $this->error()->logErrorMessage($errMessage, 'Intercepted fatal error', $errNote, false, true);

            $ret = $this->showError(null, 'error_500', false);
            return $ret ? $ret : 'Error 500';
        }

        return null;
    }

    public function showError(mixed $errMsg, string $tplName = 'error_500', bool $isEcho = true): mixed
    {
        if (empty($errMsg)) {
            $input = $this->input();
            $host = $input->serverValue('HTTP_HOST', '');
            $requestUri = $input->serverValue('REQUEST_URI', '');
            $adminEmail = defined('ADMIN_EMAIL') ? (string)constant('ADMIN_EMAIL') : 'admin@example.invalid';
            //ToDo: make this message by special file
            $errMsg = [
                'errMsg' => [
                    'Please could you send a message about this error to <a href="mailto:' . $adminEmail . '?subject=Error%20reporting&amp;body=Fatal%20Error%20at%20the%20request%20' . urlencode('http://' . $host . $requestUri) . '">' . $adminEmail . '</a>',
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

        $demonstrator = ($this->errorDemonstratorFactory())($errMsg, $tplName, $this->input(), $this->phpArrayFileLoader());

        if ($isEcho) {
            return $demonstrator->showTplContent();
        }
        return $demonstrator->getTplContent();
    }

    protected function _logException(\Throwable $e): static
    {
        $errMsg  = 'Uncaught exception "' . get_class($e) . '" with message:' . "\n";
        if (!($e instanceof base)) {
            $errMsg .= method_exists($e, 'getMessageForShow') ? $e->getMessageForShow() . "\n" : '';
            $errMsg .= method_exists($e, 'getErrorMessage')   ? $e->getErrorMessage()   . "\n" : '';
        }
        $errMsg .= $e->getMessage() . "\n \n";

        if (method_exists($e, 'getLogVars')) {
            $errMsg .= 'Properties: <pre>' . htmlspecialchars((string)$e->getLogVars()) . "</pre>\n";
        }

        $errMsg .= 'Thrown in ' . $e->getFile() . ' on line ' . $e->getLine() . "\n";
        $errMsg .= "Stack trace:<pre>" . $e->getTraceAsString() . '</pre>';

        $this->runtime()->logError($errMsg);
        return $this;
    }

    public function _showExceptionError(\Throwable $e, bool $isEcho): void
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

    private function matcher(): object
    {
        return $this->requireDependency($this->matcher, 'Matcher service');
    }

    private function request(): object
    {
        return $this->requireDependency($this->request, 'Request service');
    }

    private function error(): object
    {
        return $this->requireDependency($this->error, 'Error service');
    }

    private function input(): object
    {
        return $this->requireDependency($this->input, 'Request input service');
    }

    private function runtime(): object
    {
        return $this->requireDependency($this->runtime, 'Bootstrap runtime service');
    }

    private function header(): object
    {
        return $this->requireDependency($this->header, 'Header service');
    }

    private function phpArrayFileLoader(): callable
    {
        if (!is_callable($this->phpArrayFileLoader)) {
            throw new \RuntimeException('PHP-array file loader is not configured for bootstrap runner.');
        }

        return $this->phpArrayFileLoader;
    }

    private function errorDemonstratorFactory(): callable
    {
        if (is_callable($this->errorDemonstratorFactory)) {
            return $this->errorDemonstratorFactory;
        }

        throw new \RuntimeException('Error demonstrator factory is not configured for bootstrap runner.');
    }

    private function handlerService(string $serviceName): object
    {
        if (!isset($this->handlerFactories[$serviceName]) || !is_callable($this->handlerFactories[$serviceName])) {
            throw new \RuntimeException('Handler service "' . $serviceName . '" is not configured for bootstrap runner.');
        }

        $factory = $this->handlerFactories[$serviceName];

        return $this->requireDependency($factory(), 'Handler service "' . $serviceName . '"');
    }

    private function requireDependency(mixed $dependency, string $name): object
    {
        if (is_object($dependency)) {
            return $dependency;
        }

        throw new \RuntimeException($name . ' is not configured for bootstrap runner.');
    }

}
