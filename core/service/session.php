<?php

declare(strict_types=1);

namespace fan\core\service;
use fan\core\base\service\multi;

/**
 * Session service
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
 * @version of file: 05.02.005 (12.02.2015)
 */
class session extends multi
{
    /**
     * Session name-space in the group
     * @var string
     */
    private ?string $nameSpace = null;

    /**
     * Session group for several Session name-space
     * @var string
     */
    private ?string $group = null;

    private ?object $databaseConfig = null;

    private ?object $requestInput = null;

    private mixed $errorFactory = null;

    private ?object $requestService = null;
    private ?object $logService = null;

    /**
     * @var callable|null
     */
    private $sessionFactory = null;

    /**
     * @var callable|null
     */
    private $dateFactory = null;

    /**
     * @var callable|null
     */
    private $cookieFactory = null;

    private ?object $pearSessionSupportLoader = null;

    private mixed $sessionEngineFactory = null;

    private ?object $sessionState = null;

    private ?object $phpRuntimeSettings = null;

    private ?object $nativeSession = null;

    public function __construct(
        string $nameSpace,
        string $group,
        ?object $databaseConfig = null,
        ?object $requestInput = null,
        ?callable $errorFactory = null,
        ?object $requestService = null,
        ?object $logService = null,
        ?callable $sessionFactory = null,
        ?callable $dateFactory = null,
        ?callable $cookieFactory = null,
        ?object $pearSessionSupportLoader = null,
        ?callable $sessionEngineFactory = null,
        ?object $sessionState = null,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null,
        ?object $phpRuntimeSettings = null,
        ?object $nativeSession = null,
        ?callable $arrayValueReader = null
    )
    {
        $this->databaseConfig = $databaseConfig;
        $this->requestInput = $requestInput;
        $this->errorFactory = $errorFactory;
        $this->pearSessionSupportLoader = $pearSessionSupportLoader;
        $this->sessionEngineFactory = $sessionEngineFactory;
        $this->phpRuntimeSettings = $phpRuntimeSettings;
        $this->nativeSession = $nativeSession;
        $this->setSessionDependencies($requestService, $logService, $sessionFactory, $dateFactory, $cookieFactory);
        $this->sessionState = $sessionState;
        $state = $this->state();
        parent::__construct(
            !$state->hasInstances(),
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            null,
            null,
            null,
            $arrayValueReader
        );
        $nameSpace = (string)$nameSpace;
        $group = (string)$group;
        $state->setInstance($group, $nameSpace, $this);

        if ($this->isEnabled()) {
            $this->nameSpace = $nameSpace;
            $this->group     = $group;

            if (is_null($state->getEngine())) {
                $state->setRequestService($this->sessionRequestService());
                $sid = $this->_prepareParameters();

                // ========= {START session engine} ========= \\
                $class = $this->_getEngine((string)$this->config['ENGINE'], false);
                $state->setEngine($this->sessionEngine((string)$class, $sid, $state));
                if (method_exists($state->getEngine(), 'setFacade')) {
                    $state->getEngine()->setFacade($this);
                }

                // Compare Urer's system
                $mismatch = $this->_compareSystem();
                if (!empty($mismatch)) {
                    $erMsg  = '{key => ' . $mismatch['key'] . ', ';
                    $erMsg .= 'old => '  . $mismatch['old'] . ', ';
                    $erMsg .= 'new => '  . $mismatch['new'] . ', ';
                    $erMsg .= 'ip => ' . $this->sessionRequestInput()->serverValue('REMOTE_ADDR', '') . '}';
                    if ($this->logService !== null) {
                        $this->sessionLog()->logMessage('custom', $erMsg, 'Session is not compared');
                    } elseif (is_callable($this->errorFactory)) {
                        ($this->errorFactory)()->logErrorMessage($erMsg, 'Session is not compared');
                    }
                    $this->setSessionId(md5($this->getSessionId() . microtime()));
                    $this->_killAll();
                }

                // Check Last visit time
                $this->_checkSessionTimeout();
            }

            // Broadcast Message about start session
            $this->_broadcastMessage('sesson_start', [$nameSpace, $group]);
        } // check enabling status
    }

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    public function setSessionDependencies(
        ?object $requestService = null,
        ?object $logService = null,
        ?callable $sessionFactory = null,
        ?callable $dateFactory = null,
        ?callable $cookieFactory = null
    ): static
    {
        $this->requestService = $requestService;
        $this->logService = $logService;
        $this->sessionFactory = $sessionFactory;
        $this->dateFactory = $dateFactory;
        $this->cookieFactory = $cookieFactory;

        return $this;
    }

    public function setPhpRuntimeSettings(object $phpRuntimeSettings): static
    {
        $this->phpRuntimeSettings = $phpRuntimeSettings;

        return $this;
    }

    public function get(array|string $key, mixed $defaultValue = null, bool $removeFromSes = false): mixed
    {
        if ($this->state()->getEngine()) {
            $data = $this->_getEngineData();
            $result = array_get_element($data, $key, false);
            if ($removeFromSes) {
                $this->remove($key);
            }
            return is_null($result) ? $defaultValue : $result;
        }
        return null;
    }

    public function &getByLink(mixed $key, mixed $defaultValue = null): mixed
    {
        if ($this->state()->getEngine()) {
            $data   =& $this->_getEngineData();
            $result =& array_get_element($data, $key, true);
            if (is_null($result)) {
                $result = $defaultValue;
            }
        } else {
            $result = null;
        }
        return $result;
    }
    public function getAll(): mixed
    {
        return $this->_getEngineData();
    }

    public function set(mixed $key, mixed $value): ?bool
    {
        if ($this->state()->getEngine()) {
            if (is_array($key)) {
                $data = &$this->getByLink($key, null);
                $data = $value;
                return true;
            } elseif (is_scalar($key)) {
                $data = &$this->_getEngineData();
                $data[$key] = $value;
                return true;
            }
            return false;
        }
        return null;
    }

    public function remove(mixed $key): ?bool
    {
        if ($this->state()->getEngine()) {
            $data =& $this->_getEngineData();
            if (is_array($key) && count($key) === 1) {
                $key = reset($key);
            }
            if (is_array($key)) {
                $key = array_pop($key);
                $dest =& array_get_element($data, $key, false);
                if ($dest) {
                    unset($dest[$key]);
                    return true;
                }
            } else {
                unset($data[$key]);
                return true;
            }
            return false;
        }
        return null;
    }

    public function removeAll(): ?bool
    {
        if ($this->state()->getEngine()) {
            $data = &$this->_getEngineData();
            $data = null;
            return true;
        }
        return null;
    }

    public function setBufferData(string $key, mixed $val): static
    {
        $this->state()->setBufferData($key, $val);
        return $this;
    }

    public function getBufferData(string $key, mixed $default = null): mixed
    {
        return $this->state()->getBufferData($key, $default);
    }

    public function getSessionId(): ?string
    {
        if ($engine = $this->state()->getEngine()) {
            return $engine->getSessionId();
        }
        return null;
    }

    public function isByCookies(): ?bool
    {
        return $this->state()->isByCookie();
    }

    public function setSessionId(string $sid): bool
    {
        $engine = $this->state()->getEngine();
        if ($engine) {
            if ($this->_checkSessionId($sid)) {
                $engine->setSessionId($sid);
                $this->_setCookie((string)$this->getSessionName(), $sid);
                return true;
            }
        }
        return false;
    }

    public function getSessionName(): ?string
    {
        if ($engine = $this->state()->getEngine()) {
            return $engine->getSessionName();
        }
        return null;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function getNameSpace(): ?string
    {
        return $this->nameSpace;
    }

    public function isExpired(): bool
    {
        return $this->state()->isExpired();
    }

    public function resetExpired(bool $clearAll = true): static
    {
        if ($clearAll && $this->state()->isExpired()) {
            $this->_killAll();
        }
        $this->state()->setExpired(false);
        return $this;
    }

    public function destroy(): static
    {
        $engine = $this->state()->getEngine();
        if ($engine) {
            $engine->destroy();

            $this->state()->clear();
        }
        return $this;
    }


    // ======== Private/Protected methods ======== \\
    protected function _prepareParameters(): ?string
    {
        $config = $this->config->toArray();
        // Check conf - Session MAXLIFETIME
        if ($config['MAXLIFETIME']){
            $this->phpRuntimeSettings()->set('session.gc_maxlifetime', (string)$config['MAXLIFETIME']);
        }

        // Check conf - Session COOKIE_SECURE
        $this->phpRuntimeSettings()->set('session.cookie_secure', !empty($config['COOKIE_SECURE']) ? '1' : '0');

        // Check conf - Session COOKIE_HTTPONLY
        $this->phpRuntimeSettings()->set('session.cookie_httponly', !isset($config['COOKIE_HTTPONLY']) || !empty($config['COOKIE_HTTPONLY']) ? '1' : '0');

        // Set main session parameters
        if (empty($config['COOKIE_DOMAIN'])) {
            $this->nativeSession()->setCookieParams(0, '/');
        } else {
            $this->nativeSession()->setCookieParams(0, '/', (string)$config['COOKIE_DOMAIN']);
        }
        $this->nativeSession()->cacheLimiter((string)$config['CACHE_LIMITER']);

        // ---- Define sessin by Cookie/GET/POST ---- \\
        $request = $this->state()->getRequestService();
        $sesName   = (string)$this->config->get('SESSION_NAME', 'SID');
        $cookieSid = $request->get($sesName, 'C');
        $this->state()->setByCookie(!empty($cookieSid));

        // Check session ID by GET/POST
        $sid = $request->get(strtoupper($sesName), 'GP', $request->get(strtolower($sesName), 'GP'));
        if ($this->_checkSessionId($sid, $sesName) && (!$this->state()->isByCookie() || $this->config->get('IS_GET_PRIORITY', false))) {
            $this->state()->setByCookie($this->state()->isByCookie() && (string)$cookieSid === (string)$sid);
            $this->_setCookie($sesName, (string)$sid);
        } elseif ($this->state()->isByCookie() && !$this->_checkSessionId($cookieSid)) {
            $sid = md5((string)$cookieSid . microtime());
            $this->state()->setByCookie(false);
            $this->_setCookie($sesName, $sid);
        }
        $this->nativeSession()->name($sesName);

        return $this->state()->isByCookie() ? (string)$cookieSid : (is_null($sid) ? null : (string)$sid);
    }

    private function phpRuntimeSettings(): object
    {
        if ($this->phpRuntimeSettings === null) {
            throw new \RuntimeException('PHP runtime settings dependency is not configured for session service.');
        }

        return $this->phpRuntimeSettings;
    }

    private function nativeSession(): object
    {
        return $this->nativeSession ?? throw new \RuntimeException('Native session adapter is not configured for session service.');
    }

    protected function _setCookie(string $var, string $val): static
    {
        $this->sessionCookie('/', $this->config['COOKIE_DOMAIN'])->set($var, $val);
        return $this;
    }

    protected function &_getEngineData(): mixed
    {
        return $this->state()->getEngine()->getData((string)$this->group, (string)$this->nameSpace);
    }

    protected function _checkSessionId(mixed &$sid, ?string $sesName = null): bool
    {
        $sidSrc = $sid;
        $sid = substr((string)preg_replace('/\W/', '', (string)$sid), 0, 32);
        if ((string)$sidSrc === $sid && strlen($sid) > 16) {
            return true;
        }
        if ($sesName) { // ToDo: Make this by service request
            $this->state()->getRequestService()->remove((string)$sesName, 'GPR', true);
        }
        $sid = null;
        return false;
    }

    protected function _compareSystem(): ?array
    {
        $mismatch = null;
        $check    = $this->config['CHECK_SYSTEM'];
        if ($check) {
            $server = $this->state()->getRequestService()->getAll('S', []);
            $ses    = $this->sessionService('data', 'session');
            $param  = &$ses->getByLink('param');
            $arrayValueReader = $this->arrayValueReader();
            if ($ses->get('is_fill', false)) {
                foreach ($check as $v) {
                    if ((string)$arrayValueReader($param, $v) !== (string)$arrayValueReader($server, $v)) {
                        $mismatch = [
                            'key' => $v,
                            'old' => $arrayValueReader($param,  $v),
                            'new' => $arrayValueReader($server, $v),
                        ];
                        break;
                    }
                }
            }

            foreach ($check as $v) {
                if (isset($server[$v])) {
                    $param[$v] = $server[$v];
                }
            }
            $ses->set('is_fill', true);
        }
        return $mismatch;
    }

    protected function _checkSessionTimeout(): bool
    {
        $conf = $this->config;
        if ($conf['KILL_BY_TIMEOUT']) {
            $ses = $this->sessionService('time', 'session');

            $isExpired = &$ses->getByLink('isKilled');
            $this->state()->setExpiredReference($isExpired);
            $nowDt = date('Y-m-d H:i:s');
            $now = $this->sessionDate($nowDt);
            $differ = $now->getDifference($ses->get('reload', $nowDt));

            if ($differ > $conf['MAXLIFETIME']) {
                $this->_killAll();
                $this->state()->setExpired(true);
            }
            $ses->set('reload', $nowDt);
            return !$this->state()->isExpired();
        }
        return true;
    }

    protected function _killAll(): void
    {
        $ses = &$this->state()->getEngine()->getRoot();
        foreach ($ses as $group => &$gr) {
            if ($group !== 'ses' && is_array($gr)) {
                foreach ($gr as &$dt) {
                    $dt = [];
                }
            }
        }
    }

    private function sessionRequestService(): object
    {
        if ($this->requestService === null) {
            throw new \RuntimeException('Request service is not configured for session service.');
        }

        return $this->requestService;
    }

    private function sessionRequestInput(): object
    {
        if ($this->requestInput === null) {
            throw new \RuntimeException('Request input service is not configured for session service.');
        }

        return $this->requestInput;
    }

    private function sessionLog(): object
    {
        if ($this->logService === null) {
            throw new \RuntimeException('Log service is not configured for session service.');
        }

        return $this->logService;
    }

    private function sessionService(string $nameSpace, string $group): object
    {
        if (!is_callable($this->sessionFactory)) {
            throw new \RuntimeException('Session service factory is not configured for session service.');
        }

        return ($this->sessionFactory)($nameSpace, $group);
    }

    private function sessionDate(string $date): object
    {
        if (!is_callable($this->dateFactory)) {
            throw new \RuntimeException('Date service factory is not configured for session service.');
        }

        return ($this->dateFactory)($date);
    }

    private function sessionCookie(mixed $path, mixed $domain): object
    {
        if (!is_callable($this->cookieFactory)) {
            throw new \RuntimeException('Cookie service factory is not configured for session service.');
        }

        return ($this->cookieFactory)($path, $domain);
    }

    private function sessionEngine(string $class, ?string $sid, object $state): object
    {
        if (!is_callable($this->sessionEngineFactory)) {
            throw new \RuntimeException('Session engine factory is not configured for session service.');
        }

        return ($this->sessionEngineFactory)(
            $class,
            $sid,
            $this->config,
            $this->databaseConfig,
            $this->requestInput,
            $this->errorFactory,
            $state->getRequestService(),
            $this->pearSessionSupportLoader
        );
    }

    private function state(): object
    {
        if ($this->sessionState === null) {
            throw new \RuntimeException('Session state is not configured for session service.');
        }

        return $this->sessionState;
    }

}
