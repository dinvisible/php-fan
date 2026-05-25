<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\core\base\service\single;

/**
 * Request service
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
 * @version of file: 05.02.007 (31.08.2015)
 */
class request extends single
{
    private array $data = [
        'A0' => null, // Add(itional) request (See \fan\core\service\matcher\item\parsed)
        'A1' => null, // Extra Add(itional) request by delimiter: $key => $val
        'B'  => null, // Both = Main request + Add request (See \fan\core\service\matcher\item\parsed)
        'C'  => null, // Cookies:               $_COOKIE
        'E'  => null, // Environment variables: $_ENV
        'F'  => null, // Files (uploaded):      $_FILES
        'G'  => null, // Get parameters:        $_GET
        'H'  => null, // Headers
        'M'  => null, // Main request (See \fan\core\service\matcher\item\parsed)
        'O'  => null, // Option list in CLI-mode
        'P'  => null, // Post parameters:       $_POST
        'R'  => null, // Request parameters:    $_REQUEST
        'S'  => null, // Server data:           $_SERVER
    ];

    /**
     * Data set by correspondence to global variables (static)
     * @var array
     */
    protected array $correspondence = [
        'E' => '_ENV',
        'F' => '_FILES',
        'P' => '_POST',
        'R' => '_REQUEST',
        'S' => '_SERVER',
    ];

    /**
     * Data set by special methods (dynamic)
     * @var array
     */
    protected array $maker = [
        'A' => '_makeAddRequest',
        'B' => '_makeBothRequest',
        'G' => '_makeGet',
        'M' => '_makeMainRequest',
    ];
    /**
     * Data maker indexes (for internal/sham trnsfer)
     * @var array
     */
    protected array $makerIndex = [
        'A' => -2,
        'B' => -2,
        'G' => -2,
        'M' => -2,
    ];

    /**
     * @var \fan\core\service\matcher
     */
    private mixed $matcher = null;

    private ?string $order = null;

    /**
     * Raw POST data
     * @var string
     */
    private ?string $rawPost = null;

    private ?object $input = null;

    private ?object $runtime = null;

    private mixed $jsonFactory = null;

    private mixed $cookieFactory = null;

    private mixed $matcherFactory = null;

    private mixed $arrayAdducer = null;

    private mixed $recursiveMerger = null;

    private mixed $arrayValueReader = null;

    public function __construct(
        ?object $input = null,
        ?object $runtime = null,
        ?callable $jsonFactory = null,
        ?callable $cookieFactory = null,
        ?callable $matcherFactory = null,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null,
        ?callable $arrayAdducer = null,
        ?callable $recursiveMerger = null,
        ?callable $arrayValueReader = null,
        ?callable $classNameResolver = null
    )
    {
        $this->input = $input;
        $this->runtime = $runtime;
        $this->jsonFactory = $jsonFactory;
        $this->cookieFactory = $cookieFactory;
        $this->matcherFactory = $matcherFactory;
        $this->arrayAdducer = $arrayAdducer;
        $this->recursiveMerger = $recursiveMerger;
        $this->arrayValueReader = $arrayValueReader;
        parent::__construct(true, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory, null, null, $classNameResolver, $arrayValueReader);
        $this->order = strtoupper((string)$this->getConfig('DEFAULT_ORDER', 'PAG'));

        // Set all basic data
        foreach ($this->correspondence as $k => $v) {
            $this->data[$k] = $this->input()->globalArray($v);
        }
        if ($this->runtime()->isCli()) {
            $this->data['O'] = $this->_makeOptions();
        } else {
            $this->data['H'] = $this->_makeHeaders();
            $this->data['C'] = $this->_makeCookies();
        }
    }

    // ======== Static methods ======== \\
    // ======== The magic methods ======== \\
    /**
     * Handles dynamic property reads for this current component.
     */
    public function __get(string $key): mixed
    {
        return $this->get((string)$key);
    }
    /**
     * Implements PHP magic behavior for this current component.
     *
     * @param mixed $default Fallback value returned when no explicit value is available.
     */
    public function __invoke(string $key, ?string $order = null, mixed $default = null): mixed
    {
        return $this->get($key, $order, $default);
    }
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\

    /**
     * @param mixed $default Fallback value returned when no explicit value is available.
     */
    public function get(string $key, ?string $order = null, mixed $default = null, bool $extraAdd = true): mixed
    {
        foreach ($this->_separateData($order, $extraAdd) as $v) {
            if (isset($v[$key])) {
                return $v[$key];
            }
        }
        return $default;
    }

    /**
     * @param mixed $default Fallback value returned when no explicit value is available.
     */
    public function getAll(?string $order = null, mixed $default = [], bool $extraAdd = true): mixed
    {
        $result = [];
        $recursiveMerger = $this->recursiveMerger();
        foreach ($this->_separateData($order, $extraAdd) as $v) {
            if (!empty($v)) {
                $result = $recursiveMerger($v, $result);
            }
        }
        return empty($result) ? $default : $result;
    }

    public function getRawPost(string $convFormat = 'json', bool $useBase64 = false): mixed
    {
        if (is_null($this->rawPost)) {
            $this->rawPost = $this->input()->rawPost();
        }
        switch (strtolower($convFormat)) {
        case 'json':
            return $this->getJsonDecoder((bool)$useBase64)->decode($this->rawPost);
        case 'xml':
            return array_map(
                fn(mixed $item): mixed => $this->convertXmlRawPostItem($item),
                (array)simplexml_load_string($this->rawPost)
            );
        }
        return $this->rawPost;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function set(string $key, mixed $value, string $type = 'P'): void
    {
        if ($this->_isAllowToSet($type)) {
            $this->data[$type][$key] = $value;
        }
    }

    public function remove(string $key, string $type = 'G', bool $fullUnset = false): void
    {
        $glob = $this->arrayAdducer()($this->getConfig('ALLOW_SET', ['G' => '_GET', 'P' => '_POST', 'R' => '_REQUEST']));
        for ($i = 0; $i < strlen($type); $i++) {
            $k = $type[$i];
            if ($this->_isAllowToSet($k)) {
                unset($this->data[$k][$key]);
                if ($fullUnset && isset($glob[$k])) {
                    $this->input()->unsetGlobalValue((string)$glob[$k], $key);
                }
            }
        }
    }

    public function getQueryString(bool $byGetData = true, bool $current = true, ?string $sprtr = null): string
    {
        $matcher = $this->_getMatcher();
        if ($byGetData || empty($matcher)) {
            $get = $current && !empty($matcher) ? $this->getAll('G') : $this->input()->get();
            return http_build_query($get, '', ($sprtr ? : '&'));
        }
        $item = $current ? $matcher->getCurrentItem() : $matcher->getItem(0);
        return ltrim((string)$item->parsed->query, '?');
    }

    public function getInfoString(): string
    {
        $keys = ['HTTP_HOST', 'HTTP_REFERER', 'HTTP_USER_AGENT', 'REMOTE_ADDR', 'REMOTE_PORT', 'REQUEST_METHOD', 'QUERY_STRING', 'REQUEST_URI'];
        $info = '';
        foreach ($keys as $key) {
            $value = $this->input()->serverValue($key);
            if ($value !== null) {
                $info .= $key . ' = ' . $value . ";\n";
            }
        }
        return trim($info);
    }

    public function checkIsData(?string $order = null, bool $extraAdd = true): bool
    {
        foreach ($this->_separateData($order, $extraAdd) as $v) {
            if (!empty($v)) {
                return true;
            }
        }
        return false;
    }

    public function getAddDelimiter(): mixed
    {
        return $this->config->get('ADD_REQUEST_DELIMITER', '-');
    }

    // ======== Private/Protected methods ======== \\

    /**
     * @throws \fan\project\exception\service\fatal
     */
    protected function _separateData(?string $order, bool $extraAdd): array
    {
        $data    = [];
        $order   = empty($order) ? (string)$this->order : strtoupper($order);
        $matcher = $this->_getMatcher();
        $index   = empty($matcher) ? -1 : $matcher->getCurrentIndex();
        $arrayValueReader = $this->arrayValueReader();

        for ($i = 0; $i < strlen($order); $i++) {
            $k0 = $k1 = $order[$i];
            if ($k1 === 'A') {
                $k1 .= $extraAdd ? '0' : '1';
            }
            if (array_key_exists($k1, $this->data)) {
                if (isset($this->maker[$k0]) && $arrayValueReader($this->makerIndex, $k1) !== $index) {
                    $maker = $this->maker[$k0];
                    $this->data[$k1] = $this->{$maker}($extraAdd);
                    $this->makerIndex[$k1] = $index;
                }
                $data[$k0] = $this->data[$k1];
            } else {
                throw $this->createServiceFatalException('Incorrect symbols in order "' . $order . '". Possible symbols "' . implode('', array_keys($this->data)) . '".');
            }
        }
        return $data;
    }

    protected function _makeHeaders(): array
    {
        return $this->input()->headers();
    }

    protected function _makeAddRequest(bool $extraAdd): array
    {
        $addRequest = $this->_getRequestData('add_request');
        if (empty($addRequest)) {
            return [];
        }

        $delimiter = (string)$this->getAddDelimiter();
        if ($extraAdd && $delimiter !== '') {
            foreach ($addRequest as $v) {
                $tmp = explode($delimiter, (string)$v, 2);
                if (count($tmp) === 2 && !isset($addRequest[$tmp[0]])) {
                    $addRequest[$tmp[0]] = $tmp[1];
                }
            }
        }
        return $addRequest;
    }

    protected function _makeMainRequest(): array
    {
        $mainRequest = $this->_getRequestData('main_request');
        return empty($mainRequest) ? [] : $mainRequest;
    }

    protected function _makeBothRequest(): array
    {
        $main = $this->_makeMainRequest();
        $add  = $this->_makeAddRequest(false);
        return array_merge($main, $add);
    }

    protected function _makeGet(): array
    {
        $data = [];
        $queryStr = $this->getQueryString(false);
        if (!empty($queryStr)) {
            parse_str($queryStr, $data);
        }
        return $data;
    }

    protected function _makeCookies(): array
    {
        return $this->getCookieService()->getAll();
    }

    protected function _makeOptions(): array
    {
        $options = $this->input()->argv();
        array_shift($options);
        return $options;
    }

    protected function _getRequestData(string $prop): mixed
    {
        $matcher = $this->_getMatcher();
        return $matcher ? $matcher->getCurrentItem()->parsed->$prop : [];
    }

    protected function _isAllowToSet(string $type): bool
    {
        if (in_array($type, ['A', 'B', 'M'])) {
            return false;
        }
        if (strlen($type) !== 1 || !array_key_exists($type, $this->data)) {
            throw $this->createServiceFatalException('Incorrect type for set "' . $type . '". Possible one of symbols "' . implode('', array_keys($this->data)) . '".');
        }
        $allowSet = $this->arrayAdducer()($this->getConfig('ALLOW_SET', ['G' => '_GET', 'P' => '_POST', 'R' => '_REQUEST']));
        return !empty($allowSet[$type]);
    }

    protected function _getMatcher(): mixed
    {
        if (empty($this->matcher) && is_callable($this->matcherFactory)) {
            $this->matcher = ($this->matcherFactory)();
        }
        return $this->matcher;
    }

    private function input(): object
    {
        if ($this->input !== null) {
            return $this->input;
        }

        throw new \RuntimeException('Request input service is not configured for request service.');
    }

    private function runtime(): object
    {
        if ($this->runtime !== null) {
            return $this->runtime;
        }

        throw new \RuntimeException('Bootstrap runtime service is not configured for request service.');
    }

    private function getJsonDecoder(bool $useBase64): object
    {
        if (is_callable($this->jsonFactory)) {
            return ($this->jsonFactory)($useBase64);
        }

        throw new \RuntimeException('JSON service factory is not configured for request service.');
    }

    private function getCookieService(): object
    {
        if (is_callable($this->cookieFactory)) {
            return ($this->cookieFactory)();
        }

        throw new \RuntimeException('Cookie service factory is not configured for request service.');
    }

    private function arrayAdducer(): callable
    {
        if (is_callable($this->arrayAdducer)) {
            return $this->arrayAdducer;
        }

        throw new \RuntimeException('Array adducer is not configured for request service.');
    }

    private function recursiveMerger(): callable
    {
        if (is_callable($this->recursiveMerger)) {
            return $this->recursiveMerger;
        }

        throw new \RuntimeException('Recursive merger is not configured for request service.');
    }

    protected function arrayValueReader(): callable
    {
        if (is_callable($this->arrayValueReader)) {
            return $this->arrayValueReader;
        }

        throw new \RuntimeException('Array value reader is not configured for request service.');
    }

    private function convertXmlRawPostItem(mixed $item): mixed
    {
        if (is_object($item) || is_array($item)) {
            return array_map(
                fn(mixed $value): mixed => $this->convertXmlRawPostItem($value),
                (array)$item
            );
        }

        return $item;
    }
}
