<?php
declare(strict_types=1);

namespace fan\core\service\tab\delegate;
use fan\core\service\tab;
use fan\core\service\tab\delegate;

/**
 * Description of urlMaker
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
class urlMaker extends delegate
{
    /**
     * @var \fan\core\service\matcher
     */
    protected ?object $matcher = null;

    private ?object $request = null;

    private ?object $locale = null;

    private mixed $sessionFactory = null;

    private ?object $input = null;

    private mixed $arrayValueReader = null;

    public function __construct(
        ?object $matcher = null,
        ?object $request = null,
        ?object $locale = null,
        ?callable $sessionFactory = null,
        ?object $input = null,
        ?callable $arrayValueReader = null
    )
    {
        $this->matcher = $matcher;
        $this->request = $request;
        $this->locale = $locale;
        $this->sessionFactory = $sessionFactory;
        $this->input = $input;
        $this->arrayValueReader = $arrayValueReader;
    }
    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\
    public function isUseHttps(): bool
    {
        return !empty($this->config['USE_HTTPS']);
    }

    public function getCurrentURI(mixed $corLanguage = true, bool $addExt = true, bool $addQueryStr = true, mixed $addSid = null, mixed $sprtr = null): string
    {
        if (is_array($corLanguage)) {
            $arrayValueReader = $this->arrayValueReader();

            return $this->getCurrentURI(
                    $arrayValueReader($corLanguage, 'correct_language', true),
                    (bool)$arrayValueReader($corLanguage, 'add_extension',    true),
                    (bool)$arrayValueReader($corLanguage, 'add_query_string', true),
                    $arrayValueReader($corLanguage, 'add_session_id',   null),
                    $arrayValueReader($corLanguage, 'query_separator',  null)
            );
        }

        $req    = $this->request();
        /* @var $req \fan\core\service\request */
        $parsed = $this->matcher->getCurrentItem()->parsed;
        /* @var $parsed \fan\core\service\matcher\item\parsed */

        // Set Request path
        $request = $req->getAll('B'); // ToDo: Do non receive empty elemenents there.
        if (empty($request) && (string)$parsed->src_path !== '') {
            // If sham transter from fake URN (for example by alias)
            $request = explode('/', trim($parsed->src_path, '/'));
            $last    =& $request[count($request) - 1];
            $matche  = null;
            if (preg_match('/^(.+)\.\w{1,5}$/', $last, $matche)) {
                $last = $matche[1];
            }
        }
        foreach ($request as &$v) {
            $v = urlencode((string)$v);
        }

        // Add app prefix
        if ($parsed->app_prefix) {
            array_unshift($request, trim((string)$parsed->app_prefix, '/'));
        }
        // Add language
        if (is_null($corLanguage)) {
            $corLanguage = $this->locale()->isEnabled();
        }
        if ($corLanguage) {
            $lng = $this->locale()->getLanguage();
            if (!empty($lng)) {
                array_unshift($request, $lng);
            }
        }

        $curRequest = '/' . implode('/', $request);

        // Add extension
        if ($addExt && substr($curRequest, -1) !== '/') {
            $curRequest .= '.' . $this->getDefaultExtension();
        }

        if (is_null($sprtr)) {
            $sprtr = (string)$this->getConfig('GET_SEPARATOR', '&amp;');
        }

        // Add Query String
        if ($addQueryStr) {
            $queryStr = $req->getQueryString(true, true, (string)$sprtr);
            $curRequest .= empty($queryStr) ? '' : '?' . $queryStr;
        }

        // Add Session ID
        if (is_null($addSid)) {
            $addSid = $this->getConfig('ALLOW_GET_SID', true);
        }
        if ($addSid) {
            $ses = $this->session();
            if (!$ses->isByCookies()) {
                $curRequest = $this->addQuery($curRequest, $ses->getSessionName(), $ses->getSessionId(), (string)$sprtr);
            }
        }

        return $curRequest;
    }


    public function getModifiedCurrentURI(array $modifier, mixed $addExt = true, mixed $addSid = null, mixed $protocol = null): string
    {
        $request = $this->request();
        /* @var $request \fan\core\service\request */
        $main  = $request->getAll('M', []);
        $add   = $request->getAll('A', [], false);
        $get   = $request->getAll('G', []);
        $delim = $request->getAddDelimiter();

        // --- Modifying Add-request --- \\
        // Exclude data
        if (!empty($add) && !empty($modifier['exclude']['A'])) {
            foreach ($modifier['exclude']['A'] as $k0) {
                if (is_int($k0)) {
                    unset($add[$k0]);
                } else {
                    $k0 .= $delim;
                    foreach ($add as $k1 => $v) {
                        if (substr((string)$v, 0, strlen($k0)) === $k0) {
                            unset($add[$k1]);
                        }
                    }
                }
            }
        }
        // Include data
        if (!empty($modifier['include']['A'])) {
            foreach ($modifier['include']['A'] as $k => $v) {
                if (is_int($k)) {
                    $add[$k] = $v;
                }
            }
            $add = array_merge($add);
            foreach ($modifier['include']['A'] as $k => $v) {
                if (!is_int($k)) {
                    $add[] = $k . $delim . urlencode($v);
                }
            }
        }

        // --- Modifying GET --- \\
        // Exclude data
        if (!empty($get) && !empty($modifier['exclude']['G'])) {
            foreach ($modifier['exclude']['G'] as $k0) {
                unset($get[$k0]);
            }
        }
        // Include data
        if (!empty($modifier['include']['G'])) {
            foreach ($modifier['include']['G'] as $k => $v) {
                $get[$k] = $v;
            }
        }

        // --- Make URN --- \\
        $urn = '~/' . implode('/', $main);
        if (!empty($add)) {
            $urn .= '/' . implode('/', $add);
        }
        if ($addExt) {
            $ext  = $this->getDefaultExtension();
            $urn .= empty($ext) ? '' : '.' . $ext;
        }
        if (!empty($get)) {
            $urn .= '?' . http_build_query($get);
        }

        return $this->getURI($urn, 'link', $addSid, $protocol);
    }

    public function getURI(string $urn = '', string $type = 'link', mixed $addSid = null, mixed $protocol = null): string
    {
        if (is_null($addSid)) {
            $addSid = ($type === 'link') && $this->getConfig('ALLOW_GET_SID', true);
        }

        if (!$urn) {
            $urn = $this->matcher->getCurrentUri();
        }

        $urnApplicationPrefix = defined('\fan\core\service\tab::URN_AP') ? tab::URN_AP : '~';
        if (substr($urn, 0, 1) === $urnApplicationPrefix) {
            $urnPrefix = $this->getConfig(['URN_prefix', $type]);
            $appPrefix = trim((string)$this->matcher->getCurrentItem()->parsed['app_prefix'], '/');
            $urn = (empty($appPrefix) ? '' : '/' . $appPrefix) . (string)$urnPrefix . substr($urn, 1);
        }

        if ($addSid) {
            $ses = $this->session();
            if (!$ses->isByCookies()) {
                $urn = $this->addQuery($urn, $ses->getSessionName(), $ses->getSessionId());
            }
        }

        if (!preg_match('/^https?\:\/\/\w/i', $urn)) {
            $locale = $this->locale();
            if ($type === 'link' && $locale->isUriParsing()) {
                $urn = $locale->modifyUrn($urn);
            }

            if ($this->isUseHttps() && !is_null($protocol) && (bool)$protocol !== ($this->input()->serverValue('HTTPS') === 'on')) {
                $urn = ($protocol ? 'https' : 'http') . '://' . (string)$this->input()->serverValue('HTTP_HOST', '') . $urn;
            }
        }
        return $urn;
    }

    private function input(): object
    {
        if ($this->input !== null) {
            return $this->input;
        }

        throw new \RuntimeException('Request input service is not configured for tab URL maker.');
    }

    private function request(): object
    {
        if ($this->request !== null) {
            return $this->request;
        }

        throw new \RuntimeException('Request service is not configured for tab URL maker.');
    }

    private function locale(): object
    {
        if ($this->locale !== null) {
            return $this->locale;
        }

        throw new \RuntimeException('Locale service is not configured for tab URL maker.');
    }

    private function session(): object
    {
        if (!is_callable($this->sessionFactory)) {
            throw new \RuntimeException('Session service factory is not configured for tab URL maker.');
        }

        return ($this->sessionFactory)();
    }

    private function arrayValueReader(): callable
    {
        if (!is_callable($this->arrayValueReader)) {
            throw new \RuntimeException('Array value reader is not configured for tab URL maker.');
        }

        return $this->arrayValueReader;
    }

    public function addQuery(string $urn, mixed $key, mixed $val, mixed $sprtr = null): string
    {
        if ($key && $val) {
            if (is_null($sprtr)) {
                $sprtr = $this->getConfig('GET_SEPARATOR', '&amp;');
            }

            $key = (string)$key;
            $sprtr = (string)$sprtr;
            $val = htmlspecialchars((string)$val);
            if (!preg_match('/(?:\?|' . $this->_addSlashes($sprtr) . ')' . $this->_addSlashes($key) . '\=' . $this->_addSlashes($val) . '/', $urn)) {
                $urn .= (strstr($urn, '?') ? $sprtr : '?') . urlencode($key) . '=' . urlencode($val);
            }
        }
        return $urn;
    }

    public function getDefaultExtension(): string
    {
        return trim((string)$this->facade->getConfig('DEFAULT_EXT', 'html'), ' .');
    }

    public function reduceExt(string $url, mixed $minLen = 2, mixed $maxLen = 4): string
    {
        $matches = null;
        if (preg_match('/^(.+?)(\.\w{' . (int)$minLen. ',' . (int)$maxLen . '})?$/', $url, $matches)) {
            return $matches[1];
        }
        return $url;
    }

    // ======== Private/Protected methods ======== \\

    protected function _addSlashes(string $val): string
    {
        return addcslashes($val, '~@#$%^&|\\.,!?:;-+*/=<>()[]{}`"\'');
    }
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
}
