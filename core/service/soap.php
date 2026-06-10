<?php

declare(strict_types=1);

namespace fan\core\service;
use fan\core\base\service\multi;

/**
 * SOAP operation service
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
 * @version of file: 05.02.007 (31.08.2015)
 */
class soap extends multi
{
    /**
     * @var SoapClient
     */
    private ?object $soapObj = null;
    /**
     * @var \SoapFault
     */
    private ?object $soapFault = null;
    private ?bool $logEnabled = null;

    private \Closure $errorFactory;

    private ?object $runtime = null;

    private ?object $phpRuntimeSettings = null;
    private ?object $wsdlFileStorage = null;
    private \Closure $arrayValueReader;
    private \Closure $soapHeaderFactory;
    private \Closure $soapClientFactory;
    private \Closure $soapVarFactory;
    private \Closure $domDocumentFactory;
    private \Closure $streamContextFactory;

    /**
     * Soap Headers
     * @var array
     */
    private array $soapHeaders = [];

    public function __construct(
        bool $logEnabled,
        callable $errorFactory,
        object $runtime,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory,
        object $phpRuntimeSettings,
        object $wsdlFileStorage,
        ?callable $arrayValueReader = null,
        ?callable $classNameResolver = null,
        ?callable $soapHeaderFactory = null,
        ?callable $soapClientFactory = null,
        ?callable $soapVarFactory = null,
        ?callable $domDocumentFactory = null,
        ?callable $streamContextFactory = null
    )
    {
        $this->errorFactory = \Closure::fromCallable($errorFactory);
        $this->runtime = $runtime;
        $this->phpRuntimeSettings = $phpRuntimeSettings;
        $this->wsdlFileStorage = $wsdlFileStorage;
        $this->arrayValueReader = \Closure::fromCallable(
            $arrayValueReader ?? static function (array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed {
                throw new \RuntimeException('Array value reader is not configured for SOAP service.');
            }
        );
        $this->soapHeaderFactory = \Closure::fromCallable(
            $soapHeaderFactory ?? static function (string $nameSpace, array $name, ?array $data = null): \SoapHeader {
                throw new \RuntimeException('SOAP header factory is not configured for SOAP service.');
            }
        );
        $this->soapClientFactory = \Closure::fromCallable(
            $soapClientFactory ?? static function (string $wsdlFile, ?array $param = null): \SoapClient {
                throw new \RuntimeException('SOAP client factory is not configured for SOAP service.');
            }
        );
        $this->soapVarFactory = \Closure::fromCallable(
            $soapVarFactory ?? static function (
                mixed $data,
                int $encoding,
                ?string $typeName = null,
                ?string $typeNamespace = null,
                ?string $nodeName = null,
                ?string $nodeNamespace = null
            ): \SoapVar {
                throw new \RuntimeException('SOAP var factory is not configured for SOAP service.');
            }
        );
        $this->domDocumentFactory = \Closure::fromCallable(
            $domDocumentFactory ?? static function (): \DOMDocument {
                throw new \RuntimeException('DOM document factory is not configured for SOAP service.');
            }
        );
        $this->streamContextFactory = \Closure::fromCallable(
            $streamContextFactory ?? static function (array $options): mixed {
                throw new \RuntimeException('Stream context factory is not configured for SOAP service.');
            }
        );
        parent::__construct(false, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory, null, null, $classNameResolver, $arrayValueReader);
        $enableCache = $this->config['CACHE_ENABLED'] ? 1 : 0;
        $this->phpRuntimeSettings()->set('soap.wsdl_cache_enabled', (string)$enableCache);
        if ($enableCache) {
            if ($this->config['CACHE_DIR']) {
                $this->phpRuntimeSettings()->set('soap.wsdl_cache_dir', (string)$this->config['CACHE_DIR']);
            }
            if ($this->config['CACHE_TTL']) {
                $this->phpRuntimeSettings()->set('soap.wsdl_cache_ttl', (string)$this->config['CACHE_TTL']);
            }
        }
        if ($this->config['TRACE_ENABLED']) {
            $this->config['PARAM']['trace'] = 1;
        }
        $this->logEnabled = (bool)$logEnabled;
    }

    public function initializeSoapObject(string $wsdlFile, mixed $param = null): ?\SoapClient
    {
        return $this->_initSoapObj($wsdlFile, $param);
    }

    public function setPhpRuntimeSettings(object $phpRuntimeSettings): static
    {
        $this->phpRuntimeSettings = $phpRuntimeSettings;

        return $this;
    }

    private function phpRuntimeSettings(): object
    {
        if ($this->phpRuntimeSettings === null) {
            throw new \RuntimeException('PHP runtime settings dependency is not configured for SOAP service.');
        }

        return $this->phpRuntimeSettings;
    }

    private function wsdlFileStorage(): object
    {
        return $this->wsdlFileStorage ?? throw new \RuntimeException('SOAP WSDL file storage is not configured for SOAP service.');
    }

    /**
     * @param mixed $options Optional settings that refine the operation behavior.
     */
    public function call(mixed $funcName, mixed $arguments = [], mixed $options = null): mixed
    {
        if (!is_object($this->soapObj)) {
            $this->_makeServiceException('Soap Object is not set');
        }
        if (!is_string($funcName)) {
            $this->_makeServiceException('Error! Function name is not string there: (' . gettype($funcName) . ') "' . strval($funcName) . '"');
        }

        $errorService = $this->error();
        /* @var $errorService \fan\core\service\error */
        if (!is_array($arguments)) {
            $errorService->logErrorMessage('Error! Arguments is not array there: (' . gettype($arguments) . ') "' . strval($arguments) . '"', 'SOAP: incorrect arguments.', null, true);
            $arguments = [];
        }
        if (!is_null($options) && !is_array($options)) {
            $errorService->logErrorMessage('Error! Options is not array there: (' . gettype($options) . ') "' . strval($options) . '"', 'SOAP: incorrect options.', null, true);
            $options = [];
        }
        try {
            $this->soapFault   = null;
            $soapHeaders       = $this->soapHeaders;
            $this->soapHeaders = [];
            $errorService->setErrorBuffering();
            $ret = $this->soapObj->__soapCall($funcName, $arguments, $options, empty($soapHeaders) ? null : $soapHeaders);
            $err = $errorService->offErrorBuffering();
            if ($err) {
                $lastErr = end($err);
                $errorService->logErrorMessage($lastErr['sys_err_message'], 'Soap call error', 'SOAP method: ' . $funcName, true);
            }
            return $ret;
        } catch (\SoapFault $soapErr) {
            $this->soapFault = $soapErr;
            if ($this->logEnabled) {
                $errorService->logSoapError($soapErr);
            }
            return null;
        }
    }

    public function setHeader(string $nameSpace, array $name, ?array $data = null): void
    {
        $this->soapHeaders[] = $this->createSoapHeader($nameSpace, $name, $data);
    }


    public function setSoapVar(array $data, array $varParam = [], array $levels = [0]): mixed
    {
        sort($levels);
        return $this->_setSoapVarRecursive($data, $varParam, $levels, 0);
    }

    public function allowErrLogging(bool $logEnabled): void
    {
        $this->logEnabled = !empty($logEnabled);
    }

    public function isError(): bool
    {
        return !is_null($this->soapFault);
    }

    public function getSoapFault(): ?\SoapFault
    {
        return $this->soapFault;
    }

    public function getDebugInfo(): ?string
    {
        if (!$this->soapObj) {
            return null;
        } else if (!$this->config['TRACE_ENABLED']) {
            return '';
        }

        $msg  = '<fieldset class="soap_log"><legend>Sent Request DATA</legend>';
        $msg .= '<fieldset><legend>Headers</legend><div>' . trim((string)$this->soapObj->__getLastRequestHeaders()) . '</div></fieldset>';
        $msg .= '<fieldset><legend>Request</legend><pre>' . $this->_format4log((string)$this->soapObj->__getLastRequest()) . '</pre></fieldset>';
        $msg .= '</fieldset>';

        $msg .= '<fieldset class="soap_log"><legend>Received Response DATA</legend>';
        $msg .= '<fieldset><legend>Headers</legend><div>' . trim((string)$this->soapObj->__getLastResponseHeaders()) . '</div></fieldset>';
        $msg .= '<fieldset><legend>Response</legend><pre>' . $this->_format4log((string)$this->soapObj->__getLastResponse()) . '</pre></fieldset>';
        $msg .= '</fieldset>';
        return $msg;
    }

    protected function _initSoapObj(string $wsdlFile, mixed $param = null): ?\SoapClient
    {
        $isURL = (bool)preg_match('/^https?:\/\//', $wsdlFile);
        $wsdlFile_Full = $isURL ? $wsdlFile : $this->runtime()->parsePath((string)$this->config['WSDL_DIR']) . $wsdlFile;

        if (isset($this->config['PARAM'])) {
            if (!is_array($param)) {
                $param = [];
            }
            foreach ($this->config['PARAM'] as $k => $v) {
                if (!array_key_exists($k, $param)) {
                    $param[$k] = $v;
                }
            }
        }

        if ($this->getConfig('BLOCK_SSL_VERIFY', false)) {
            if (!is_array($param)) {
                $param = [];
            }
            $param['stream_context'] = $this->createStreamContext([
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ]]
            );
        }

        if ($isURL || $this->wsdlFileStorage()->exists($wsdlFile_Full)) {
            try {
                if (isset($param['soap_version'])) {
                    if (is_numeric($param['soap_version'])) {
                        $param['soap_version'] = (int)$param['soap_version'];
                    } else {
                        $const = get_defined_constants();
                        $param['soap_version'] = $const[$this->arrayValueReader()($param, 'soap_version')];
                    }
                }
                $this->soapObj = $this->createSoapClient($wsdlFile_Full, $param && is_array($param) ? $param : null);
                return $this->soapObj;
            } catch (\SoapFault $err) {
                $this->soapFault = $err;
                $this->error()->logSoapError($err);
                return null;
            }
        } else {
            $this->error()->logErrorMessage('Error. WSDL-file "' . $wsdlFile_Full . '" isn\'t exist.');
            return null;
        }
    }

    protected function _setSoapVarRecursive(mixed $data, array $varParam, array $levels, int $currentLevel): mixed
    {
        if (is_array($data)) {
            foreach ($data as &$v) {
                $v = $this->_setSoapVarRecursive($v, $varParam, $levels, $currentLevel + 1);
            }
        }
        if (in_array($currentLevel, $levels) && !is_scalar($data)) {
            $data = $this->createSoapVar(
                $data,
                SOAP_ENC_OBJECT,
                $this->arrayValueReader()($varParam, 'type_name'),
                $this->arrayValueReader()($varParam, 'type_namespace'),
                $this->arrayValueReader()($varParam, 'node_name'),
                $this->arrayValueReader()($varParam, 'node_namespace')
            );
        }
        return $data;
    }

    protected function _format4log(string $xml): string
    {
        if ($xml === '') {
            return '';
        }
        $doc = $this->createDomDocument();
        $doc->loadXML($xml);
        $doc->formatOutput = true;
        return htmlspecialchars((string)$doc->saveXML());
    }

    private function error(): object
    {
        if (!isset($this->errorFactory)) {
            $this->errorFactory = \Closure::fromCallable(
                static function (): object {
                    throw new \RuntimeException('Error dependency is not configured for SOAP service.');
                }
            );
        }

        $error = ($this->errorFactory)();
        if (!is_object($error)) {
            throw new \UnexpectedValueException('Error dependency must be an object.');
        }

        return $error;
    }

    private function runtime(): object
    {
        if ($this->runtime === null) {
            throw new \RuntimeException('Bootstrap runtime dependency is not configured for SOAP service.');
        }

        return $this->runtime;
    }

    protected function arrayValueReader(): callable
    {
        if (!isset($this->arrayValueReader)) {
            $this->arrayValueReader = \Closure::fromCallable(
                static function (array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed {
                    throw new \RuntimeException('Array value reader is not configured for SOAP service.');
                }
            );
        }

        return $this->arrayValueReader;
    }

    private function createSoapHeader(string $nameSpace, array $name, ?array $data = null): \SoapHeader
    {
        if (!isset($this->soapHeaderFactory)) {
            $this->soapHeaderFactory = \Closure::fromCallable(
                static function (string $nameSpace, array $name, ?array $data = null): \SoapHeader {
                    throw new \RuntimeException('SOAP header factory is not configured for SOAP service.');
                }
            );
        }

        $header = ($this->soapHeaderFactory)($nameSpace, $name, $data);
        if (!$header instanceof \SoapHeader) {
            $actual = is_object($header) ? get_class($header) : gettype($header);
            throw new \UnexpectedValueException('SOAP header factory returned "' . $actual . '".');
        }

        return $header;
    }

    private function createSoapClient(string $wsdlFile, ?array $param = null): \SoapClient
    {
        if (!isset($this->soapClientFactory)) {
            $this->soapClientFactory = \Closure::fromCallable(
                static function (string $wsdlFile, ?array $param = null): \SoapClient {
                    throw new \RuntimeException('SOAP client factory is not configured for SOAP service.');
                }
            );
        }

        $client = ($this->soapClientFactory)($wsdlFile, $param);
        if (!$client instanceof \SoapClient) {
            $actual = is_object($client) ? get_class($client) : gettype($client);
            throw new \UnexpectedValueException('SOAP client factory returned "' . $actual . '".');
        }

        return $client;
    }

    private function createSoapVar(
        mixed $data,
        int $encoding,
        ?string $typeName = null,
        ?string $typeNamespace = null,
        ?string $nodeName = null,
        ?string $nodeNamespace = null
    ): \SoapVar {
        if (!isset($this->soapVarFactory)) {
            $this->soapVarFactory = \Closure::fromCallable(
                static function (
                    mixed $data,
                    int $encoding,
                    ?string $typeName = null,
                    ?string $typeNamespace = null,
                    ?string $nodeName = null,
                    ?string $nodeNamespace = null
                ): \SoapVar {
                    throw new \RuntimeException('SOAP var factory is not configured for SOAP service.');
                }
            );
        }

        $soapVar = ($this->soapVarFactory)($data, $encoding, $typeName, $typeNamespace, $nodeName, $nodeNamespace);
        if (!$soapVar instanceof \SoapVar) {
            $actual = is_object($soapVar) ? get_class($soapVar) : gettype($soapVar);
            throw new \UnexpectedValueException('SOAP var factory returned "' . $actual . '".');
        }

        return $soapVar;
    }

    private function createDomDocument(): \DOMDocument
    {
        if (!isset($this->domDocumentFactory)) {
            $this->domDocumentFactory = \Closure::fromCallable(
                static function (): \DOMDocument {
                    throw new \RuntimeException('DOM document factory is not configured for SOAP service.');
                }
            );
        }

        $document = ($this->domDocumentFactory)();
        if (!$document instanceof \DOMDocument) {
            $actual = is_object($document) ? get_class($document) : gettype($document);
            throw new \UnexpectedValueException('DOM document factory returned "' . $actual . '".');
        }

        return $document;
    }

    private function createStreamContext(array $options): mixed
    {
        if (!isset($this->streamContextFactory)) {
            $this->streamContextFactory = \Closure::fromCallable(
                static function (array $options): mixed {
                    throw new \RuntimeException('Stream context factory is not configured for SOAP service.');
                }
            );
        }

        $context = ($this->streamContextFactory)($options);
        if (!is_resource($context)) {
            $actual = is_object($context) ? get_class($context) : gettype($context);
            throw new \UnexpectedValueException('Stream context factory returned "' . $actual . '".');
        }

        return $context;
    }
}
