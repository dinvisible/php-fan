<?php

declare(strict_types=1);

namespace fan\core\service;
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
class session extends \fan\core\base\service\multi
{
    private static array $instances = [];
    private static ?object $engine = null;
    /**
     * @var \fan\core\service\request Session engine
     */
    protected static ?object $sr = null;
    /**
     * Flag: Session is got by cookie
     * @var boolean
     */
    private static ?bool $byCookie = null;
    /**
     * Flag: Session is expired
     * @var boolean
     */
    private static bool $isExpired = false;

    /**
     * Buffer of Data for data communication beetween different parts of code
     * @var array
     */
    private static array $bufferData = [];

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

    protected function __construct(string $nameSpace, string $group)
    {
        parent::__construct(empty(self::$instances));
        $nameSpace = (string)$nameSpace;
        $group = (string)$group;
        self::$instances[$group][$nameSpace] = $this;

        if ($this->isEnabled()) {
            $this->nameSpace = $nameSpace;
            $this->group     = $group;

            if (is_null(self::$engine)) {
                self::$sr = $this->containerService('request');
                $sid = $this->_prepareParameters();

                // ========= {START session engine} ========= \\
                $class = $this->_getEngine((string)$this->config['ENGINE'], false);
                self::$engine = new $class($sid);
                self::$engine->setFacade($this);

                // Compare Urer's system
                $mismatch = $this->_compareSystem();
                if (!empty($mismatch)) {
                    $erMsg  = '{key => ' . $mismatch['key'] . ', ';
                    $erMsg .= 'old => '  . $mismatch['old'] . ', ';
                    $erMsg .= 'new => '  . $mismatch['new'] . ', ';
                    $erMsg .= 'ip => ' . ($_SERVER['REMOTE_ADDR'] ?? '') . '}';
                    l($erMsg, 'Session is not compared');
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

    // ======== Static methods ======== \\
    /**
     * @throws \fan\project\exception\fatal
     */
    public static function instance(mixed $nameSpace = null, mixed $group = 'custom'): static
    {
        if (is_null($group)) {
            throw new \fan\project\exception\fatal('Unset group name for \fan\core\service\session.');
        }
        if (is_null($nameSpace)) {
            $config    = self::staticContainerService('config')->get('session');
            $group     = 'app';
            $nameSpace = self::staticContainerService('application')->getAppName();
            $repName   = $config->get(['REPLACE_APP', $nameSpace]);
            if ($repName) {
                $nameSpace = (string)$repName;
            }
        }
        $nameSpace = (string)$nameSpace;
        $group = (string)$group;
        if (!isset(self::$instances[$group][$nameSpace])) {
            new self($nameSpace, $group);
        }
        return self::$instances[$group][$nameSpace];
    }

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    public function get(array|string $key, mixed $defaultValue = null, bool $removeFromSes = false): mixed
    {
        if (self::$engine) {
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
        if (self::$engine) {
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
        if (self::$engine) {
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
        if (self::$engine) {
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
        if (self::$engine) {
            $data = &$this->_getEngineData();
            $data = null;
            return true;
        }
        return null;
    }

    public function setBufferData(string $key, mixed $val): static
    {
        self::$bufferData[$key] = $val;
        return $this;
    }

    public function getBufferData(string $key, mixed $default = null): mixed
    {
        return array_val(self::$bufferData, $key, $default);
    }

    public function getSessionId(): ?string
    {
        if (self::$engine) {
            return self::$engine->getSessionId();
        }
        return null;
    }

    public function isByCookies(): ?bool
    {
        return self::$byCookie;
    }

    public function setSessionId(string $sid): bool
    {
        if (self::$engine) {
            if ($this->_checkSessionId($sid)) {
                self::$engine->setSessionId($sid);
                $this->_setCookie((string)$this->getSessionName(), $sid);
                return true;
            }
        }
        return false;
    }

    public function getSessionName(): ?string
    {
        if (self::$engine) {
            return self::$engine->getSessionName();
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
        return self::$isExpired;
    }

    public function resetExpired(bool $clearAll = true): static
    {
        if ($clearAll && self::$isExpired) {
            $this->_killAll();
        }
        self::$isExpired = false;
        return $this;
    }

    public function destroy(): static
    {
        if (self::$engine) {
            self::$engine->destroy();

            self::$instances = [];
            self::$engine    = null;
            self::$byCookie  = null;
        }
        return $this;
    }


    // ======== Private/Protected methods ======== \\
    protected function _prepareParameters(): ?string
    {
        $config = $this->config->toArray();
        // Check conf - Session MAXLIFETIME
        if ($config['MAXLIFETIME']){
            ini_set('session.gc_maxlifetime', (string)$config['MAXLIFETIME']);
        }

        // Check conf - Session COOKIE_SECURE
        ini_set('session.cookie_secure', !empty($config['COOKIE_SECURE']) ? '1' : '0');

        // Check conf - Session COOKIE_HTTPONLY
        ini_set('session.cookie_httponly', !isset($config['COOKIE_HTTPONLY']) || !empty($config['COOKIE_HTTPONLY']) ? '1' : '0');

        // Set main session parameters
        if (empty($config['COOKIE_DOMAIN'])) {
            session_set_cookie_params (0, '/');
        } else {
            session_set_cookie_params (0, '/', (string)$config['COOKIE_DOMAIN']);
        }
        session_cache_limiter((string)$config['CACHE_LIMITER']);

        // ---- Define sessin by Cookie/GET/POST ---- \\
        $sesName   = (string)$this->config->get('SESSION_NAME', 'SID');
        $cookieSid = self::$sr->get($sesName, 'C');
        self::$byCookie = !empty($cookieSid);

        // Check session ID by GET/POST
        $sid = self::$sr->get(strtoupper($sesName), 'GP', self::$sr->get(strtolower($sesName), 'GP'));
        if ($this->_checkSessionId($sid, $sesName) && (!self::$byCookie || $this->config->get('IS_GET_PRIORITY', false))) {
            self::$byCookie = self::$byCookie && (string)$cookieSid === (string)$sid;
            $this->_setCookie($sesName, (string)$sid);
        } elseif (self::$byCookie && !$this->_checkSessionId($cookieSid)) {
            $sid = md5((string)$cookieSid . microtime());
            self::$byCookie = false;
            $this->_setCookie($sesName, $sid);
        }
        session_name($sesName);

        return self::$byCookie ? (string)$cookieSid : (is_null($sid) ? null : (string)$sid);
    }

    protected function _setCookie(string $var, string $val): static
    {
        \fan\project\service\cookie::instance('/', $this->config['COOKIE_DOMAIN'])->set($var, $val);
        return $this;
    }

    protected function &_getEngineData(): mixed
    {
        return self::$engine->getData((string)$this->group, (string)$this->nameSpace);
    }

    protected function _checkSessionId(mixed &$sid, ?string $sesName = null): bool
    {
        $sidSrc = $sid;
        $sid = substr((string)preg_replace('/\W/', '', (string)$sid), 0, 32);
        if ((string)$sidSrc === $sid && strlen($sid) > 16) {
            return true;
        }
        if ($sesName) { // ToDo: Make this by service request
            self::$sr->remove((string)$sesName, 'GPR', true);
        }
        $sid = null;
        return false;
    }

    protected function _compareSystem(): ?array
    {
        $mismatch = null;
        $check    = $this->config['CHECK_SYSTEM'];
        if ($check) {
            $server = self::$sr->getAll('S', []);
            $ses    = $this->containerService('session', 'data', 'session');
            $param  = &$ses->getByLink('param');
            if ($ses->get('is_fill', false)) {
                foreach ($check as $v) {
                    if ((string)array_val($param, $v) !== (string)array_val($server, $v)) {
                        $mismatch = [
                            'key' => $v,
                            'old' => array_val($param,  $v),
                            'new' => array_val($server, $v),
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
            $ses = $this->containerService('session', 'time', 'session');

            self::$isExpired = &$ses->getByLink('isKilled');
            $nowDt = date('Y-m-d H:i:s');
            $now = \fan\project\service\date::instance($nowDt);
            $differ = $now->getDifference($ses->get('reload', $nowDt));

            if ($differ > $conf['MAXLIFETIME']) {
                $this->_killAll();
                self::$isExpired = true;
            }
            $ses->set('reload', $nowDt);
            return !self::$isExpired;
        }
        return true;
    }

    protected function _killAll(): void
    {
        $ses = &self::$engine->getRoot();
        foreach ($ses as $group => &$gr) {
            if ($group !== 'ses' && is_array($gr)) {
                foreach ($gr as &$dt) {
                    $dt = [];
                }
            }
        }
    }

}
