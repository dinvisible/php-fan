<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
/**
 * Description of header
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
 *
 * @property string  $response
 * @property string  $protocol
 * @property string  $contentType
 * @property string  $encoding
 * @property string  $filename
 * @property string  $disposition
 * @property string  $length
 * @property string  $legthRange
 * @property string  $modified
 * @property string  $expired
 * @property string  $cacheLimit
 *
 */
class header extends \fan\core\base\service\single
{
    /**
     * Mapping of methods for send headers
     * @var array
     */
    protected array $sendMethodMap = [
        'response'    => ['sendResponseType', 0],
        'protocol'    => ['sendResponseType', 1],

        'contentType' => ['sendContentType', 0],
        'encoding'    => ['sendContentType', 1],

        'filename'    => ['sendFilename', 0],
        'disposition' => ['sendFilename', 1],

        'length'      => ['sendLength', 0],
        'legthRange'  => ['sendLength', 1],

        'modified'    => ['sendTime', 0],
        'expired'     => ['sendTime', 1],

        'cacheLimit'  => ['sendCache', 0],

        //'' => ['', 0],
    ];

    /**
     * Mapping of special methods for set headers
     * @var array
     */
    protected array $setMethodMap = [
        'response' => 'setResponseType',
    ];

    /**
     * If requested code isn't present there, they will be loaded automatically
     * @var array Frequently Response Codes
     */
    protected array $responseCodes = [
        200 => 'OK',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Internal Server Error',
    ];

    /**
     * Stack of Headers ordered by MethodMap
     * @var array
     */
    protected array $headerData = [];

    protected function __construct(bool $allowIni = true)
    {
        parent::__construct($allowIni);

        $this->clearHeaders();
    }

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\

    // ------ Prepare of headers setting ------ \\
    /**
     * @param string $value Value that should be applied or transformed.
     */
    public function addHeader(string $param, mixed $value): static
    {
        if (in_array($param, $this->setMethodMap)) {
            $method = $this->setMethodMap[$param];
            $this->$method($value);
        } else {
            $this->_setHeadStack($param, $value);
        }
        return $this;
    }

    public function setHeaders(array $headers): static
    {
        $this->clearHeaders();
        foreach ($headers as $k => $v) {
            $this->addHeader($k, $v);
        }
        return $this;
    }

    public function removeHeader(string $param): static
    {
        unset($this->headerData[$param]);
        return $this;
    }

    public function getHeader(?string $param = null): mixed
    {
        return empty($param) ? $this->headerData : $this->headerData[$param];
    }

    public function sendHeaders(): array
    {
        $fileName = $lineNum = null;
        if (headers_sent($fileName, $lineNum)) {
            throw new \RuntimeException('Headers have been sent in "' . $fileName . '" at the line ' . $lineNum);
        }

        if (empty($this->headerData['response'])) {
            $this->setResponseType();
        }

        foreach ($this->_prepareFunctions() as $k => $v) {
            $arg = $this->_orderArguments($v);
            call_user_func_array([$this, $k], $arg);
        }

        return $this->clearHeaders();
    }

    public function clearHeaders(): array
    {
        $ret = $this->headerData;
        $this->headerData = [
            'protocol' => empty($_SERVER['SERVER_PROTOCOL']) ? 'HTTP/1.1' : $_SERVER['SERVER_PROTOCOL'],
        ];
        $this->setResponseType();
        return $ret;
    }

    // ------ Sepecial header setter/getter ------ \\
    public function setResponseType(mixed $code = null): static
    {
        if (is_null($code)) {
            $code = 200;
        } else {
            $this->_checkResponseCode((int)$code);
        }

        $this->_setHeadStack('response', $code);

        return $this;
    }
    public function getResponseCode(): mixed
    {
        return $this->headerData['response'];
    }

    public function getProtocol(): string
    {
        return isset($this->headerData['protocol']) ? $this->headerData['protocol'] : 'HTTP/1.1';
    }

    // ------ Senders of header ------ \\
    public function sendResponseType(?int $code = null, ?string $protocol = null): void
    {
        header($this->_getResponseText($code ?? (int)$this->getResponseCode(), $protocol ?? $this->getProtocol()));
    }

    /**
     * @param ?string $value Value that should be applied or transformed.
     */
    public function sendContentType(?string $value = null, ?string $encoding = null): static
    {
        if (!empty($value) || !empty($encoding)) {
            if (empty($value)) {
                $value = 'text/html';
            }
            header('Content-Type: ' . $value . (empty($encoding) ? '' : '; ' . $encoding));
        }
        return $this;
    }

    public function sendLength(string $len, ?string $ranges = null): static
    {
        if (!empty($len)) {
            if (empty($ranges)) {
                $ranges = 'bytes';
            }
            header('Accept-Ranges: ' . $ranges);
            header('Content-Length: ' . $len);
        }
        return $this;
    }

    public function sendTime(int|float|null $modified = NULL, int|float|null $expired = NULL): static
    {
        if (!is_null($modified)) {
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $modified) . ' GMT');
        }
        if (!is_null($expired)) {
            header('Expires: ' . gmdate('D, d M Y H:i:s', $expired) . ' GMT');
            header('Cache-Control: post-check=1,pre-check=1');
        }
        return $this;
    }

    public function sendFilename(string $fileName, bool $isInline = true): static
    {
        header('Content-Disposition: ' . ($isInline ? 'inline' : 'attachment') . '; filename="' . ($fileName ? $fileName : 'no_name') . '"');
        return $this;
    }

    public function sendCache(int|float $timeExpires = 0): static
    {
        $time = time();
        if ($timeExpires > 0) {
            // Enable cache
            $this->sendTime(isset($this->headerData['modified']) ? null : $time, $time + $timeExpires);
        } else {
            // Disable cache
            $this->sendTime($time);
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

            if ($this->getProtocol() === 'HTTP/1.0') {
                header('Pragma: no-cache');
            } else {
                header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0'); //  max-age=0
            }
        }
        return $this;
    }

    /**
     * @param string $url URL used as the external request target.
     */
    public function sendLocation(string $url, bool $continueExec = false): static
    {
        header('Location: ' . str_replace('&amp;', '&', $url));
        if (!$continueExec) {
            exit;
        }
        return $this;
    }

    /**
     * @param string $url URL used as the external request target.
     */
    public function sendLocation301(string $url, bool $continueExec = false): static
    {
        header('Location: ' . str_replace('&amp;', '&', $url), true, 301);
        if (!$continueExec) {
            exit;
        }
        return $this;
    }

    /**
     * @param string $value Value that should be applied or transformed.
     */
    public function sendArbitrary(string $type, string $value, string $extraData = ''): static
    {
        header($type . ': ' . $value . (empty($extraData) ? '' : '; ' . $extraData));
        return $this;
    }

    // ------ Frequently response headers set ------ \\
    public function ok200(bool $send = false): static
    {
        return $this->_setSpecialType(200, $send);
    }

    public function error403(bool $send = false): static
    {
        return $this->_setSpecialType(403, $send);
    }

    public function error404(bool $send = false): static
    {
        return $this->_setSpecialType(404, $send);
    }

    public function error500(bool $send = false): static
    {
        return $this->_setSpecialType(500, $send);
    }

    // ======== Protected methods ======== \\
    protected function _getSendMethodMap(): array
    {
        return $this->sendMethodMap;
    }

    /**
     * @param string $value Value that should be applied or transformed.
     */
    protected function _setHeadStack(string $param, mixed $value): static
    {
        $parameters = $this->_getSendMethodMap();
        if (!isset($parameters[$param])) {
            throw new fatalException($this, 'Incorrect header parameter "' . $param . '" for stack');
        }
        $this->headerData[$param] = $value;
        return $this;
    }

    protected function _prepareFunctions(): array
    {
        $result = [];
        foreach ($this->_getSendMethodMap() as $k => $v) {
            if (isset($this->headerData[$k])) {
                $result[$v[0]][$v[1]] = $this->headerData[$k];
            }
        }
        return $result;
    }

    protected function _orderArguments(array $arg): array
    {
        for ($i = 0; $i < max(array_keys($arg)); $i++) {
            if (!isset($arg[$i])) {
                $arg[$i] = null;
            }
        }
        ksort($arg);
        return $arg;
    }

    /**
     * @throws \fan\project\exception\service\fatal
     */
    protected function _getResponseText(int $code, string $protocol): string
    {
        $this->_checkResponseCode($code);
        if (empty($protocol)) {
            $protocol = $this->getProtocol();
        }
        return $protocol . ' ' . $code . ' ' . $this->responseCodes[$code];
    }

    /**
     * @throws \fan\project\exception\service\fatal
     */
    protected function _checkResponseCode(int $code): static
    {
        if (!isset($this->responseCodes[$code])) {
            if ($code >= 100 && $code <= 599) {
                $class = $this->_getEngine('code', false);
                $this->responseCodes = array_merge_recursive_alt(
                        $this->responseCodes,
                        call_user_func([$class, 'getCodes' . substr($code, 0, 1)])
                );
            }
            if (!isset($this->responseCodes[$code])) {
                throw new fatalException($this, 'Unknown response code "' . $code . '"');
            }
        }
        return $this;
    }

    protected function _setSpecialType(int $code, bool $send): static
    {
        $this->setResponseType($code);
        if ($send) {
            $this->sendResponseType($code, null);
        }
        return $this;
    }

    // ======== The magic methods ======== \\

    /**
     * Handles dynamic property writes for this current component.
     *
     * @param mixed $value Value that should be applied or transformed.
     */
    public function __set(string $key, mixed $value): void
    {
        $this->addHeader($key, $value);
    }

    /**
     * Handles dynamic property reads for this current component.
     */
    public function __get(string $key): mixed
    {
        return $this->getHeader($key);
    }

    // ======== Required Interface methods ======== \\
}
