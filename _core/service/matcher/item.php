<?php
declare(strict_types=1);

namespace fan\core\service\matcher;
use fan\core\service\config\row;
use fan\core\service\matcher;
use fan\core\service\matcher\item\handler;
use fan\core\service\matcher\item\parsed;

/**
 * Description of item
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
 *
 * @property-read \fan\core\service\matcher\item\source  $source
 * @property-read \fan\core\service\matcher\item\uri     $uri
 * @property-read \fan\core\service\matcher\item\handler $handler
 * @property-read \fan\core\service\matcher\item\parsed  $parsed
 */
class item implements \ArrayAccess
{
    /**
     * Index of this item
     * @var array
     */
    protected ?int $index = null;

    /**
     * Allowed property
     * @var array
     */
    protected array $data = [
        // \fan\core\service\matcher\item\source
        'source'  => null,
        // \fan\core\service\matcher\item\uri
        'uri'     => null,
        // \fan\core\service\matcher\item\handler
        'handler' => null,
        // \fan\core\service\matcher\item\parsed
        'parsed'  => null,
    ];

    /**
     * Facade of service
     * @var fan\core\service\matcher
     */
    protected ?object $facade = null;

    private ?object $input = null;
    private ?object $runtime = null;
    private ?object $locale = null;
    private ?object $application = null;
    private ?object $routeFileStorage = null;
    private mixed $componentFactory = null;
    private mixed $serviceExceptionFactory = null;
    private mixed $fatalExceptionFactory = null;

    public function __construct(
        int $index,
        ?object $input = null,
        ?object $runtime = null,
        ?object $locale = null,
        ?object $application = null,
        ?object $routeFileStorage = null,
        ?callable $componentFactory = null,
        ?callable $serviceExceptionFactory = null,
        ?callable $fatalExceptionFactory = null
    )
    {
        $this->index = (int)$index;
        $this->componentFactory = $componentFactory;
        $this->serviceExceptionFactory = $serviceExceptionFactory;
        $this->fatalExceptionFactory = $fatalExceptionFactory;
        $this->setDependencies($input, $runtime, $locale, $application, $routeFileStorage);
        foreach ($this->data as $k => &$v) {
            $class = '\fan\project\service\matcher\item\\' . $k;
            $v = $this->matcherItemComponent($class, $k);
        }
    }

    // ========== Public interface functions ========== \\
    /**
     * @param string $request Request object or payload handled by the operation.
     */
    public function initOut(string $request, string $host): void
    {
        // Save current source data
        $this->data['source']['request'] = $request;
        $this->data['source']['host']    = $host;

        // Save current array of URI
        foreach ($this->_parseRequestedUri($request, $host) as $k => $v) {
            $this->data['uri'][$k] = $v;
        }
    }
    /**
     * @param string $file File path or file descriptor handled by the operation.
     */
    public function initCli(string $file, string $path): void
    {
        // Save current source data
        $this->data['source']['file'] = $file;
        $this->data['source']['path'] = $path;

        // Save current array of CLI
        foreach ($this->_parseRequestedCli($file, $path) as $k => $v) {
            $this->data['cli'][$k] = $v;
        }
    }

    public function setFacade(matcher $facade): static
    {
        $this->facade = $facade;
        foreach ($this->data as $v) {
            $v->setFacade($facade);
        }
        return $this;
    }

    public function getFacade(): ?matcher
    {
        return $this->facade;
    }

    public function serviceExceptionFactory(): ?callable
    {
        return is_callable($this->serviceExceptionFactory) ? $this->serviceExceptionFactory : null;
    }

    public function createServiceFatalException(string $message, int $code = E_USER_ERROR, ?\Throwable $previous = null): \Throwable
    {
        $factory = $this->serviceExceptionFactory();
        if ($factory === null) {
            throw new \RuntimeException('Service exception factory is not configured for matcher item.');
        }
        if ($this->facade === null) {
            throw new \RuntimeException('Matcher facade is not configured for matcher item.');
        }

        $exception = $factory(
            '\fan\project\exception\service\fatal',
            $this->facade,
            $message,
            $code,
            $previous
        );
        if (!$exception instanceof \Throwable) {
            throw new \UnexpectedValueException('Service exception factory must return a throwable object.');
        }

        return $exception;
    }

    public function createMatcherFatalException(string $message, int $code = E_USER_ERROR, ?\Throwable $previous = null): \Throwable
    {
        if (!is_callable($this->fatalExceptionFactory)) {
            throw new \RuntimeException('Matcher item fatal exception factory is not configured.');
        }

        $exception = ($this->fatalExceptionFactory)($message, $code, $previous, $this->input);
        if (!$exception instanceof \Throwable) {
            throw new \UnexpectedValueException('Matcher item fatal exception factory must return a throwable object.');
        }

        return $exception;
    }

    public function getIndex(): ?int
    {
        return $this->index;
    }

    public function getMainBlockBasePath(): string
    {
        return rtrim($this->runtime()->getLoader()->main, '\\/');
    }

    public function setDependencies(?object $input = null, ?object $runtime = null, ?object $locale = null, ?object $application = null, ?object $routeFileStorage = null): static
    {
        $this->input = $input ?? $this->input;
        $this->runtime = $runtime ?? $this->runtime;
        $this->locale = $locale ?? $this->locale;
        $this->application = $application ?? $this->application;
        $this->routeFileStorage = $routeFileStorage ?? $this->routeFileStorage;

        return $this;
    }

    public function getHandler(bool $forceDefine = false): handler
    {
        if (empty($this->data['handler']['method'])){
            foreach ($this->_defineHandler($forceDefine) as $k => $v) {
                $this->data['handler'][$k] = $v;
            }
        }
        return $this->data['handler'];
    }


    public function preParseRequest(): static
    {
        $uri = $this['uri'];
        $subject = [
            'request' => $uri['path'] . (empty($uri['query']) ? '' : '?' . $uri['query']),
            'host'    => $uri['host'],
            'full'    => $uri['full'],
        ];

        $language = null;
        $appName  = null;
        $reqData  = [];
        $pathPos  = null;
        $locale   = $this->locale();
        $languages      = $locale->getAvailableLanguages();
        $regexpLanguage = implode('|', array_keys($languages));

        $prefix  = '';
        $matches = null;
        foreach ($this->facade->getConfig('app', []) as $k => $v) {
            $regexp = str_replace('{LANGUAGE}', $regexpLanguage, $v['regexp']);
            $way    = isset($v['way']) ? $v['way'] : 'path';
            if (preg_match($regexp, $subject[$way], $matches)) {
                if (isset($v['language']) && !empty($matches[$v['language']])) {
                    $language = $matches[$v['language']];
                }
                $appName = $k;
                $reqData = $matches;
                $pathPos = empty($v['path']) ? null : $v['path'];
                $prefix  = empty($v['prefix']) ? '' : (empty($matches[$v['prefix']]) ? '' : $matches[$v['prefix']]);
                break;
            }
        }

        if (empty($reqData)) {
            $srcPath = $subject['request'];
        } elseif (empty($pathPos)) {
            $len     = strlen($reqData[0]);
            $subj    = $subject[$way];
            $srcPath = substr($subj, 0, $len) === $reqData[0] ? substr($subj, $len) : $subject['request'];
        } else {
            $srcPath = '';
            $parts   = explode('-', $pathPos);
            foreach ($parts as $v) {
                if (!empty($reqData[$v])) {
                    $srcPath .= $reqData[$v];
                }
            }
        }

        $parsed = $this->data['parsed'];
        $parsed['language']   = $language;
        $parsed['app_name']   = $appName;
        $parsed['app_prefix'] = $prefix;

        $queryPos = strpos ($srcPath, '?');
        if ($queryPos === false) {
            $parsed['src_path'] = $srcPath;
            $parsed['query']    = '';
        } else {
            $parsed['src_path'] = substr($srcPath, 0, $queryPos);
            $parsed['query']    = substr($srcPath, $queryPos);
        }


        // ToDo: Check all paths
        //   - local (disk-paths) must point by last item;
        //   - outer (URN) must point by current item;
        // If this is departed from a rule - will be big error when AppName is changed
        $this->application()->setAppName((string)$appName);
        return $this;
    }


    public function getParsedSrc(): array
    {
        $parsed = $this->data['parsed'];
        $data   = explode('/', $parsed['src_path']);

        $regExp = $this->_getConfig(['app', $parsed['app_name'], 'regexp_trim_ext']);
        if (empty($regExp)) {
            $regExp = $this->_getConfig('default_regexp_trim_ext', '/^(.+)\\.(?:php|html?)/');
        }
        $matches = null;
        if (!empty($data) && preg_match((string)$regExp, (string)end($data), $matches)) {
            $data[count($data) - 1] = $matches[1];
        }
        return $data;
    }


    /**
     * Transforms request between supported representations.
     */
    public function parseRequest(): static
    {
        $parsed = $this->data['parsed'];
        $data   = $this->getParsedSrc();

        $handlerKey = ucfirst(strtolower((string)$this->getHandler()->key));
        $methodName = empty($handlerKey) || !method_exists($this, '_parseRequestFor' . $handlerKey) ? null : '_parseRequestFor' . $handlerKey;
        if (empty($methodName)) {
            $parsed['main_request'] = [];
            $parsed['add_request']  = [];
        } else {
            $this->$methodName($parsed, $data);
        }
        return $this;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    // ========== Private/protected functions ========== \\
    /**
     * @param string $request Request object or payload handled by the operation.
     *
     * @throws fatalException
     */
    protected function _parseRequestedUri(string $request, string $host): array
    {
        // Prepare global URI-parameters
        if (empty($this->index)) {
            $input = $this->input();
            $scheme   = $input->serverValue('HTTPS') === 'on' ? 'https' : 'http';
            $userName = $password = $anchor = null; // ToDo: Set start default values there
            if (empty($host)) {
                $host = $input->serverValue('HTTP_HOST');
            }
        } else {
            $prevUri  = $this->facade->getUri($this->index - 1);
            $scheme   = $prevUri['scheme'];
            $userName = $prevUri['user'];
            $password = $prevUri['pass'];
            $anchor   = $prevUri['fragment'];
            if (empty($host)) {
                $host = $prevUri['host'];
            } elseif (!$this->facade->getConfig('allow_switch_host', false)) {
                throw $this->createServiceFatalException('Host switching isn\'t allowed there');
            }
        }

        // Prepare Path and Query
        if (strpos($request, '?') === false) {
            $path  = $request;
            $query = empty($this->index) ? null : $prevUri['query'];
        } else {
            list($path, $query) = explode('?', $request, 2);
        }

        $uri = [
            'scheme'   => $scheme,
            'host'     => $host,
            'user'     => $userName,
            'pass'     => $password,
            'path'     => $path,
            'query'    => empty($query) ? null : $query,
            'fragment' => $anchor,
        ];

        // Make full URI
        $fullUri = $scheme . '://' . $userName;
        if ($password) {
            $fullUri .= ':' . $password;
        }
        if ($userName || $password) {
            $fullUri .= '@';
        }
        $fullUri .= $host . $path;
        if ($query) {
            $fullUri .= '?' . $query;
        }
        if ($anchor) {
            $fullUri .= '#' . $anchor;
        }
        $uri['full'] = $fullUri;

        return $uri;
    }
    /**
     * @param string $file File path or file descriptor handled by the operation.
     *
     * @throws fatalException
     */
    protected function _parseRequestedCli(string $file, string $path): array
    {
        $cli = [
            'file' => $file,
            'path' => $path,
            'argv' => $this->input()->argv(),
        ];

        return $cli;
    }

    private function input(): object
    {
        return $this->requireDependency($this->input, 'Request input service');
    }

    private function runtime(): object
    {
        return $this->requireDependency($this->runtime, 'Bootstrap runtime service');
    }

    private function locale(): object
    {
        return $this->requireDependency($this->locale, 'Locale service');
    }

    private function application(): object
    {
        return $this->requireDependency($this->application, 'Application service');
    }

    private function routeFileStorage(): object
    {
        return $this->requireDependency($this->routeFileStorage, 'Matcher route file storage');
    }

    private function requireDependency(mixed $dependency, string $name): object
    {
        if (is_object($dependency)) {
            return $dependency;
        }

        throw new \RuntimeException($name . ' is not configured for matcher item.');
    }

    private function matcherItemComponent(string $className, string $key): object
    {
        if (!is_callable($this->componentFactory)) {
            throw new \RuntimeException('Matcher item component factory is not configured for matcher item.');
        }

        return ($this->componentFactory)($className, $this, $key);
    }

    protected function _defineHandler(bool $forceDefine): array
    {
        if ($this->runtime()->isCli()) {
            return $this->_handlerDefinerSapiName();
        } elseif (empty($this->index) || $forceDefine) {
            // Find handler by RegExp
            foreach ($this->facade->getConfig('plain', []) as $k => $v) {
                $handlerConfig = $this->readHandlerConfig($v);
                $method = '_handlerDefiner' . ucfirst($handlerConfig['definer']);
                if (!method_exists($this, $method)) {
                    $this->_makeException('Incorrect Handler definer at the Config of Matcher');
                }
                $result = $this->$method($k, $handlerConfig);

                if (!empty($result)) {
                    return $result;
                }
            }

            // Set default handler if it doesn't macth any RegExp
            $default = $this->facade->getConfig('default_handler', [
                'key'     => 'tab',
                'service' => 'tab',
                'method'  => 'handleContent',
                'param'   => null,
            ]);
            return [
                'key'     => $default['key'],
                'service' => $default['service'] ?? null,
                'method'  => $default['method'],
                'param'   => empty($default['param']) ? null : $default['param'],
            ];
        }
        // Copy handler from first item
        $firstHandler = $this->facade->getHandler(0);
        return [
            'key'     => $firstHandler['key'],
            'service' => $firstHandler['service'],
            'method'  => $firstHandler['method'],
            'param'   => $firstHandler['param'],
        ];
    }

    protected function readHandlerConfig(mixed $data): array
    {
        if ($data instanceof row) {
            return $data->toArray();
        }
        if (is_array($data)) {
            return $data;
        }

        throw new \UnexpectedValueException('Matcher handler config must be an array or config row.');
    }

    protected function _handlerDefinerSapiName(): array
    {
        return [
            'key'     => 'cli',
            'service' => 'cli',
            'method'  => 'handleContent',
            'param'   => [],
            'ctrlKey' => null,
        ];
    }

    protected function _handlerDefinerRequest(string $key, array $data): ?array
    {
        $matches = [];
        if (preg_match((string)$data['regexp'], (string)$this->data['uri']['path'], $matches)) {
            return [
                'key'     => 'plain',
                'service' => 'plain',
                'method'  => 'handleContent',
                'param'   => [$key, $data['class'], $this->_getControllerMethod($matches, $data['method'])],
                'ctrlKey' => $key,
                'reqKey' => isset($matches[1]) ? $matches[1] : null,
            ];
        }
        return null;
    }

    protected function _parseRequestForTab(parsed $parsed, array $data): ?static
    {
        $path  = $this->runtime()->getLoader()->project;
        $path .= '/app/' . $parsed['app_name'] . '/' . $this->_getConfig('main_block_dir', 'main');

        $mainRequest = [];
        foreach ($data as $k => $v) {
            if (empty($v)) {
                unset($data[$k]);
            } else {
                if ($this->routeFileStorage()->isFile($path . '/' . $v . '.php')) {
                    $mainRequest[] = $v;
                    unset($data[$k]);
                    if (
                        !isset($data[$k + 1])
                        || !$this->routeFileStorage()->isDirectory($path . '/' . $v)
                        || !$this->routeFileStorage()->isDirectory($path . '/' . $data[$k + 1]) && !$this->routeFileStorage()->isFile($path . '/' . $data[$k + 1] . '.php')
                    ) {
                        $parsed['main_request'] = $mainRequest;
                        $parsed['add_request']  = array_merge([], $data);
                        return null;
                    }
                } elseif ($this->routeFileStorage()->isDirectory($path . '/' . $v)) {
                    $path .= '/' . $v;
                    $mainRequest[] = $v;
                    unset($data[$k]);
                } else {
                    break;
                }
            }
        }

        // Set Index file if in URI it is not requested
        if (empty($data)) {
            $index = empty($mainRequest) ? $this->_getConfig('directory_index', 'index') : end($mainRequest);
            if ($this->routeFileStorage()->isFile($path . '/' . $index . '.php')) {
                $parsed['main_request'] = array_merge($mainRequest, [$index]);
                $parsed['add_request']  = [];
            }
        }

        return $this;
    }
    protected function _parseRequestForPlain(parsed $parsed, array $data): static
    {
        $mainRequstPref = $this->data['handler']->reqKey;
        if (empty($mainRequstPref)) {
            $parsed['main_request'] = [array_shift($data)];
            $parsed['add_request']  = $data;
        } else {
            $mr = explode('/', $mainRequstPref);
            $parsed['main_request'] = $mr;
            foreach ($mr as $v) {
                if ((string)$data[0] === (string)$v) {
                    array_shift($data);
                }
            }
            $parsed['add_request']  = $data;
        }
        return $this;
    }
    protected function _parseRequestForCli(parsed $parsed, array $data): static
    {
        return $this;
    }

    protected function _checkKey(string $key): void
    {
        if (!array_key_exists($key, $this->data)) {
            $this->_makeException('Invalid key "' . $key . '" while accessing the item of matcher.');
        }
    }

    /**
     * @throws \fan\project\exception\service\fatal
     * @throws \fan\project\exception\fatal
     */
    protected function _makeException(string $errMsg): never
    {
        if ($this->facade) {
            throw $this->createServiceFatalException($errMsg);
        }
        throw $this->createMatcherFatalException($errMsg);
    }

    protected function _getControllerMethod(array $matches, string $pattern): string
    {
        for ($i = 1; $i < count($matches); $i++) {
            $pattern = str_replace('{\\' . $i . '}', ucfirst(strtolower($matches[$i])), $pattern);
        }

        $tmp = explode('_', $pattern);
        $res = array_shift($tmp);
        foreach ($tmp as $v) {
            $res .= ucfirst($v);
        }

        return $res;
    }

    /**
     * @param mixed $default Fallback value returned when no explicit value is available.
     */
    public function _getConfig(mixed $key, mixed $default = null): mixed
    {
        return $this->facade->getConfig()->get($key, $default);
    }

    // ========== Magic functions ========== \\
    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->_checkKey((string)$key);
        $this->_makeException('Isn\'t allowed direct set property of item of matcher. Try to set "' . $value . '" for "' . $key . '"');
    }

    public function offsetExists(mixed $key): bool
    {
        $key = (string)$key;
        $this->_checkKey($key);
        return !is_null($this->data[$key]);
    }

    public function offsetUnset(mixed $key): void
    {
        $key = (string)$key;
        $this->_checkKey($key);
        $this->_makeException('Isn\'t allowed unset property of item of matcher.');
    }

    public function offsetGet(mixed $key): mixed
    {
        $key = (string)$key;
        $this->_checkKey($key);
        $method = 'get' . ucfirst($key);
        return method_exists($this, $method) ? $this->$method() : $this->data[$key];
    }

    /**
     * Handles dynamic property writes for this current component.
     *
     * @param mixed $value Value that should be applied or transformed.
     */
    public function __set(string $key, mixed $value): void
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Handles dynamic property reads for this current component.
     */
    public function __get(string $key): mixed
    {
        return $this->offsetGet($key);
    }

    // ======== Required Interface methods ======== \\
}
