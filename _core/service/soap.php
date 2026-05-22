<?php

declare(strict_types=1);

namespace fan\core\service;
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
class soap extends \fan\core\base\service\multi
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

    /**
     * Soap Headers
     * @var array
     */
    private array $soapHeaders = [];

    protected function __construct(bool $logEnabled)
    {
        parent::__construct(false);
        $enableCache = $this->config['CACHE_ENABLED'] ? 1 : 0;
        ini_set('soap.wsdl_cache_enabled', $enableCache);
        if ($enableCache) {
            if ($this->config['CACHE_DIR']) {
                ini_set('soap.wsdl_cache_dir', $this->config['CACHE_DIR']);
            }
            if ($this->config['CACHE_TTL']) {
                ini_set('soap.wsdl_cache_ttl', $this->config['CACHE_TTL']);
            }
        }
        if ($this->config['TRACE_ENABLED']) {
            $this->config['PARAM']['trace'] = 1;
        }
        $this->logEnabled = (bool)$logEnabled;
    }

    public static function instance(string $wsdlFile, ?array $param = null, bool $logEnabled = true): static
    {
        $instance = new self((bool)$logEnabled);
        $instance->_initSoapObj($wsdlFile, $param);
        return $instance;
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

        $errorService = $this->containerService('error');
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
        $this->soapHeaders[] = new \SoapHeader($nameSpace, $name, $data);
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
        $wsdlFile_Full = $isURL ? $wsdlFile : \bootstrap::parsePath((string)$this->config['WSDL_DIR']) . $wsdlFile;

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
            $param['stream_context'] = stream_context_create([
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ]]
            );
        }

        if ($isURL || file_exists($wsdlFile_Full)) {
            try {
                if (isset($param['soap_version'])) {
                    if (is_numeric($param['soap_version'])) {
                        $param['soap_version'] = (int)$param['soap_version'];
                    } else {
                        $const = get_defined_constants();
                        $param['soap_version'] = $const[array_val($param, 'soap_version')];
                    }
                }
                $this->soapObj = $param && is_array($param) ? new \SoapClient($wsdlFile_Full, $param) : new \SoapClient($wsdlFile_Full);
                return $this->soapObj;
            } catch (\SoapFault $err) {
                $this->soapFault = $err;
                $this->containerService('error')->logSoapError($err);
                return null;
            }
        } else {
            $this->containerService('error')->logErrorMessage('Error. WSDL-file "' . $wsdlFile_Full . '" isn\'t exist.');
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
            $data = new \SoapVar(
                $data,
                SOAP_ENC_OBJECT,
                array_val($varParam, 'type_name'),
                array_val($varParam, 'type_namespace'),
                array_val($varParam, 'node_name'),
                array_val($varParam, 'node_namespace')
            );
        }
        return $data;
    }

    protected function _format4log(string $xml): string
    {
        if ($xml === '') {
            return '';
        }
        $xml = new \DOMDocument();
        $xml->loadXML($xml);
        $xml->formatOutput = true;
        return htmlspecialchars((string)$xml->saveXML());
    }
}
