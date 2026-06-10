<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\core\base\service\multi;


/**
 * CURL service
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
 * @version of file: 05.02.005 (12.02.2015)
 */
class curl extends multi
{
    /**
     * @var handle CURL instance
     */
    protected ?object $curl = null;

    protected string $url = '';

    /**
     * @var index - for separate the same URL
     */
    protected int|float|string $index = 0;

    protected array $headers = [];

    /**
     * Content (result of CURL-request)
     * @var string
     */
    protected ?string $content = null;

    protected string $separator = "\n";

    protected bool $separateResponse = true;

    private ?object $state = null;
    private ?object $curlAdapter = null;
    private mixed $arrayAdducer = null;
    private mixed $curlArrayValueReader = null;

    /**
     * @param mixed $url URL used as the external request target.
     */
    public function __construct(
        string $url,
        int|float|string $index,
        ?object $state = null,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null,
        ?object $curlAdapter = null,
        ?callable $arrayAdducer = null,
        ?callable $arrayValueReader = null
    )
    {
        $this->index = $index;
        $this->url   = (string)$url;
        $this->state = $state;
        $this->curlAdapter = $curlAdapter ?? throw new \RuntimeException('Curl adapter is not configured for curl service.');
        $this->arrayAdducer = $arrayAdducer;
        $this->curlArrayValueReader = $arrayValueReader;
        parent::__construct(true, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory);

        $this->curl = $this->curlAdapter->init($this->url);

        $this->setOption(CURLOPT_RETURNTRANSFER, 1);
        $this->setOption(CURLOPT_HEADER, 1);
        $this->setOption(CURLINFO_HEADER_OUT, 1);

        $conf = $this->config;
        if ($conf['CURLOPT_PROXY']) {
           $this->setOption(CURLOPT_PROXY, $conf['CURLOPT_PROXY']);
        }
        if ($conf['CURLOPT_PROXYUSERPWD']) {
           $this->setOption(CURLOPT_PROXYUSERPWD, $conf['CURLOPT_PROXYUSERPWD']);
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    public function setOption(int $key, mixed $val): static
    {
        $this->curlAdapter->setOption($this->curl, $key, $val);
        return $this;
    }

    public function setHeaders(array $headers = []): static
    {
        if ($headers) {
            $this->setOption(CURLOPT_HTTPHEADER, $this->arrayAdducer()($headers));
        }
        return $this;
    }

    public function setTimeout(int $timeout): static
    {
        $this->setOption(CURLOPT_TIMEOUT, $timeout);
        return $this;
    }

    public function setCookies(mixed $cookies): static
    {
        if (is_array($cookies)) {
            $cookieString = '';
            foreach ($cookies as $k => $v) {
                if (!empty($cookieString)) {
                    $cookieString .= '; ';
                }
                $cookieString .= $k . '=' . $v;
            }
            $cookies = $cookieString;
        } else {
            $cookies = (string)$cookies;
        }
        $this->setOption(CURLOPT_COOKIE, $cookies);
        return $this;
    }

    public function getCookies(?string $key = null): mixed
    {
        $matches = null;
        if (preg_match_all('/(\w+)\=(.*?)\;\s*/', (string)$this->getResponseHeaders('Set-Cookie'), $matches, PREG_SET_ORDER)) {
            $result = [];
            foreach ($matches as $v){
                $result[$v[1]] = $v[2];
            }
            return $key ? $this->curlArrayValueReader()($result, $key) : $result;
        }
        return null;
    }

    public function close(): static
    {
        if (!is_null($this->curl)) {
            $this->curlAdapter->close($this->curl);
            $this->curl = null;
            $this->state?->removeInstance($this->index, $this->url);
        }
        return $this;
    }

    public function getRequestHeaders(): mixed
    {
        return $this->getInfo(CURLINFO_HEADER_OUT);
    }

    public function getInfo(int|float|null $option = null): mixed
    {
        return $this->curlAdapter->getInfo($this->curl, $option);
    }

    public function getError(): string
    {
        return $this->curlAdapter->error($this->curl);
    }

    public function exec(mixed $postData = null, bool $allowExcept = true): ?string
    {

        if (!is_null($postData)) {
            if (is_array($postData)) {
                $optData = [];
                foreach ($postData as $k => $v) {
                    if (is_array($v)) {
                        $this->_convPostArray($optData, (string)$k, $v);
                    } else {
                        $optData[$k] = $v;
                    }
                }
            } else {
                $optData = $postData;
            }
            $this->setOption(CURLOPT_POST, 1);
            $this->setOption(CURLOPT_POSTFIELDS, $optData);
        }

        $this->headers = [];
        $this->content = null;

        $data = $this->curlAdapter->exec($this->curl);
        if ($data) {
            $separator = $this->_getSeparator($data);
            list($headers, $body) = explode($separator . $separator, $data, 2);
            $headers1 = '';
            while (trim($headers) === 'HTTP/1.1 100 Continue') {
                list($headers, $body) = explode($separator . $separator, $body, 2);
                $headers1 .= $headers . $separator;
            }
            foreach (explode($separator, $headers1 . $headers) as $v0) {
                if (strstr($v0, ':')) {
                    list($k, $v) = explode(':', $v0, 2);
                    $k = trim($k);
                    if (!isset($this->headers[$k])) {
                        $this->headers[$k] = '';
                    } else {
                        $this->headers[$k] .= '; ';
                    }
                    $this->headers[$k] .= trim($v);
                } elseif (substr($v0, 0, 5) === 'HTTP/') {
                    $this->headers['HTTP'] = trim($v0);
                }
            }
            $this->content = $body;
        }

        $err = $this->getError();
        if ($err && $allowExcept) {
            throw $this->createServiceFatalException('There is CURL error ocured: <b>' . $err . '</b>');
        }

        return $this->getContent();
    }

    public function setSeparateResponse(bool $separate = false): static
    {
        $this->separateResponse = (bool)$separate;
        return $this;
    }

    public function getResponseHeaders(?string $key = null): mixed
    {
        return $key ? $this->curlArrayValueReader()($this->headers, $key) : $this->headers;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    // ======== Private/Protected methods ======== \\

    protected function _getSeparator(?string $data = null): string
    {
        if ($data) {
            $pos = strpos($data, "\r");
            if ($pos) {
                $this->separator = $pos && $data[$pos + 1] === "\n" ? "\r\n" : "\r";
            }
        }
        return $this->separator;
    }

    protected function _convPostArray(array &$optData, string $key, array $data): static
    {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $this->_convPostArray($optData, $key . '[' . $k . ']', $v);
            } else {
                $optData[$key . '[' . $k . ']'] = $v;
            }
        }
        return $this;
    }

    private function arrayAdducer(): callable
    {
        if (is_callable($this->arrayAdducer)) {
            return $this->arrayAdducer;
        }

        throw new \RuntimeException('Array adducer is not configured for curl service.');
    }

    private function curlArrayValueReader(): callable
    {
        if (is_callable($this->curlArrayValueReader)) {
            return $this->curlArrayValueReader;
        }

        throw new \RuntimeException('Array value reader is not configured for curl service.');
    }

}
