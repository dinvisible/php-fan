<?php
declare(strict_types=1);

namespace fan\core\service;
/**
 * Service defines several parameters of locale:
 *  - language
 *  - country
 *  - time-zone
 *  - character-set
 *  - currency-code
 *  - currency-sign
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
 * @version of file: 05.02.004 (25.12.2014)
 */
class locale extends \fan\core\base\service\single
{
    /**
     * List of Available Languages
     * @var array
     */
    protected array $availableLng = [];

    /**
     * Default language code
     * @var string
     */
    protected ?string $defaultLng = null;
    /**
     * Current language code
     * @var string
     */
    protected ?string $currentLanguage = null;
    /**
     * Current Country key
     * @var string
     */
    protected ?string $currentCountry = null;
    /**
     * Current Time Zone (integer value from -12 to +13)
     * @var integer
     */
    protected int|string|null $currentTimeZone = null;
    /**
     * Current Character Set
     * @var string
     */
    protected string $characterSet = 'utf-8';
    /**
     * Current Currency Code
     * @var string
     */
    protected ?string $currencyCode = null;

    /**
     * Service session
     * @var \fan\core\service\session
     */
    protected ?object $session = null;

    /**
     * If is locale defined
     * @var boolean
     */
    protected bool $isDefined = false;

    protected function __construct(bool $allowIni = true)
    {
        parent::__construct($allowIni);
        $this->_setBasicProp();

        $this->_subscribeForService('application', 'setAppName',   [$this, 'onAppChange']);
        $this->_subscribeForService('matcher',     'setNewUri',    [$this, 'onSetNewUri']);
        $this->_subscribeForService('session',     'sesson_start', [$this, 'onSessonStart']);
    }

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\

    public function getAvailableLanguages(): array
    {
        return $this->availableLng;
    }

    public function getLanguageShortNames(): array
    {
        return $this->getConfig('SHORT_NAME', []);
    }

    public function setLanguage(string $language): static|false
    {
        if ($this->isEnabled() && isset($this->availableLng[$language])) {
            $this->_defineLocale();
            return $this->_setCurrentLanguage($language, true);
        }
        return false;
    }
    public function getLanguage(): ?string
    {
        return $this->_defineLocale()->currentLanguage;
    }

    public function getLanguageId(): mixed
    {
        $serv = $this->containerService('entity');
        if (!$serv->getConfig(['delegate', 'getLngByName'], false)) {
            return null;
        }
        return $serv->getLngByName($this->getLanguage())->getId(false);
    }
    public function getDefaultLanguage(): ?string
    {
        return $this->defaultLng;
    }

    public function addLanguage(string $code, string $name, string $shortName): static
    {
        if (!isset($this->availableLng[$code])) {
            $this->availableLng[$code] = $name;
            $this->getConfig('SHORT_NAME')->set($code, $shortName);
        }
        return $this;
    }
    public function removeLanguage(string $code): static
    {
        return $this;
    }

    public function setCharacterSet(string $characterSet): static
    {
        if ((string)$this->characterSet !== $characterSet) {
            $this->characterSet = $characterSet;
            $this->_broadcastMessage('setCharacterSet', $this);
        }
        return $this;
    }
    public function getCharacterSet(): string
    {
        return $this->characterSet;
    }

    public function setTimeZone(string $timeZone): static
    {
        if ((string)$this->currentTimeZone !== $timeZone) {
            $this->currentTimeZone = $timeZone;
            $this->_getSession()->set('current_time_zone', $timeZone);
            $this->_broadcastMessage('setTimeZone', $this);
        }
        return $this;
    }
    public function getTimeZone(): int|string|null
    {
        return $this->currentTimeZone;
    }

    public function setCurrencyCode(string $currencyCode): static
    {
        if ((string)$this->currencyCode !== $currencyCode) {
            $this->currencyCode = $currencyCode;
            $this->_getSession()->set('currency_code', $currencyCode);
            $this->_broadcastMessage('setCurrencyCode', $this);
        }
        return $this;
    }
    public function getCurrencyCode(): ?string
    {
        return $this->currencyCode;
    }

    public function setCountry(string $country): static
    {
        if ((string)$this->currentCountry !== $country) {
            $this->currentCountry  = $country;
            $this->_getSession()->set('current_country', $country);
            $this->_broadcastMessage('setCountry', $this);
        }
        return $this;
    }
    public function getCountry(): ?string
    {
        return $this->currentCountry;
    }

    public function isUriParsing(): bool
    {
        return $this->isEnabled() && !empty($this->config['REQUEST_HAS_LNG']);
    }

    public function modifyUrn(string $urn, ?string $lng = null): string
    {
        if ($this->isEnabled()  && $this->isUriParsing()) {
            $matches = $this->checkUriLng($urn);
            if (is_null($matches)) {
                if (empty($lng) || !isset($this->availableLng[$lng])) {
                    $lng = $this->getLanguage();
                }
                $urn = '/' . $lng . $urn;
            }
        }
        return $urn;
    }

    /**
     * @param ?string $url URL used as the external request target.
     */
    public function getSwitcherLinks(?string $url = null, ?string $lng = null): array
    {
        $ret = [];
        $tab = $this->containerService('tab');
        /* @var $tab \fan\core\service\tab */
        if (empty($url)) {
            $url = $tab->getCurrentURI(false, true, true, true);
        }

        $sep = $tab->getConfig('GET_SEPARATOR', '&amp;');
        foreach ($this->getAvailableLanguages() as $k => $v) {
            $newUrn = empty($this->config['REQUEST_HAS_LNG']) ?
                $url . (strpos($url, '?') === false ? '?' : $sep) . $this->getConfig('LANGUAGE_KEY', 'lng') . '=' . $k :
                '/' . $k . $url;
            $ret[$k] = [
                'key'     => $k,
                'urn'     => $newUrn,
                'f_name'  => $v,
                's_name'  => $this->config['SHORT_NAME'][$k],
                'current' => (string)$k === $this->getLanguage(),
            ];
        }
        return $ret;
    }

    public function onSessonStart(): void
    {
        $this->_getSession();
        $this->_defineLocale();
    }

    public function onSetNewUri(\fan\core\service\matcher $matcher): void
    {
        if ($this->isDefined) {
            $language = $this->_getLanguageByMatcher($matcher);
            $this->_setCurrentLanguage($language);
        } else {
            $this->_defineLocale();
        }
    }

    /**
     * @param string $url URL used as the external request target.
     */
    public function checkUriLng(string $url): ?array
    {
        $regExp = '/^((\~?\/)(' . implode('|', array_keys($this->availableLng)) . '))(?:\/|$)/';
        $matches = null;
        if (preg_match($regExp, $url, $matches)) {
            return $matches;
        }
        return null;
    }

    public function onAppChange(): void
    {
        $this->_setBasicProp();
        if (!in_array($this->currentLanguage, $this->availableLng)) {
            $this->_defineLanguage();
        }
    }

    // ======== Private/Protected methods ======== \\

    protected function _getSession(bool $forse = true): ?\fan\core\service\session
    {
        if (empty($this->session) && (class_exists('\fan\core\service\session', false) || $forse)) {
            $this->session = $this->containerService('session', 'locale', 'service');
        }
        return $this->session;
    }

    protected function _setBasicProp(): static
    {
        $this->characterSet = (string)$this->getConfig('CHARACTER_SET', 'utf-8');
        $this->defaultLng   = (string)$this->getConfig('DEFAULT_LANGUAGE', 'en');

        // Define Available languages and Curren Language
        $availableLng = $this->getConfig('AVAILABLE_LANGUAGE');
        if (!$this->isEnabled() || empty($availableLng)) {
            $this->config['ENABLED'] = false;
            $this->availableLng      = $this->_getDefultLanguages($availableLng);
            $this->currentLanguage   = $this->defaultLng;
        } else {
            $this->availableLng = is_object($availableLng) && method_exists($availableLng, 'toArray') ? $availableLng->toArray() : adduceToArray($availableLng);
        }
        return $this;
    }

    protected function _defineLocale(): static
    {
        if (!$this->isDefined) {
            if ($this->isEnabled()) {
                $this->_defineLanguage(true);
            } else {
                $this->currentLanguage = $this->defaultLng;
            }
            // Dedine country, time-zone and currency-code
            $this->_defineExtraData();
            $this->isDefined = true;
        }
        return $this;
    }

    protected function _defineLanguage(bool $forse = false): int
    {
        // Define by request in the matcher
        if (class_exists('\fan\core\service\matcher', false)) {
            if ($this->_setCurrentLanguage($this->_getLanguageByMatcher(), $forse)) {
                return 1;
            }
        }

        // Define by GET or POST key
        $req    = $this->containerService('request');
        $lngKey = $this->getConfig('LANGUAGE_KEY', 'lng');
        if ($this->_setCurrentLanguage($req->get($lngKey, 'GP'), $forse)) {
            return 2;
        }

        // Define by SESSION
        if ($this->getConfig('USE_SESSION4LNG', false)) {
            $ses = $this->_getSession(true);
            if (!empty($ses) && $this->_setCurrentLanguage($ses->get('current_language'), $forse)) {
                return 3;
            }
        }

        // Define by COOKIES
        if ($this->_setCurrentLanguage($req->get($lngKey, 'C'), $forse)) {
            return 4;
        }

        // Define by HTTP_ACCEPT_LANGUAGE
        $acceptLng = $req->get('HTTP_ACCEPT_LANGUAGE', 'S');
        if (!empty($acceptLng)) {
            $lng = explode(',', $acceptLng);
            foreach ($lng as $v) {
                if (preg_match('/^(\w+)(?:[\-_](\w+))?/', $v, $matches)) {
                    if ($this->_setCurrentLanguage(strtolower($matches[0]), $forse)) {
                        if (!empty($matches[1])) {
                            $this->setCountry($matches[1]);
                        }
                        return 5;
                    }
                }
            }
        }

        $this->currentLanguage = $this->defaultLng;
        return 0;
    }

    public function _setCurrentLanguage(?string $language, bool $forse = false): bool
    {
        if (!empty($language) && $this->isEnabled() && isset($this->availableLng[$language])) {
            $isNew = (string)$this->currentLanguage !== $language;
            if ($isNew || $forse) {
                if ($this->getConfig('USE_SESSION4LNG', false)) {
                    $this->_getSession()->set('current_language', $language);
                }

                \fan\project\service\cookie::instance('/')->setByTime(
                        $this->getConfig('LANGUAGE_KEY', 'lng'),
                        $language,
                        (int)$this->getConfig('COOKIE_TIME', 2592000)
                );
            }

            if ($isNew) {
                $this->currentLanguage = $language;
                $this->_broadcastMessage('setNewLanguage', $this);
            }
            return true;
        }
        return false;
    }

    public function _getDefultLanguages(mixed $availableLng): array
    {
        $availableLng = adduceToArray($availableLng);
        $k = $this->defaultLng;
        return isset($availableLng[$k]) ?
                [$k => $availableLng[$k]] :
                [$k => $k];
    }

    public function _defineExtraData(): static
    {
        $ses = $this->_getSession(false);
        if (!empty($ses)) {
            $map = [
                'current_country'   => 'currentCountry',
                'current_time_zone' => 'currentTimeZone',
                'currency_code'     => 'currencyCode',
            ];
            foreach ($map as $k => $v) {
                if (empty($this->$v)) {
                    $this->$v = $ses->get($k);
                }
            }
        }
        return $this;
    }

    public function _getLanguageByMatcher(?\fan\core\service\matcher $matcher = null): ?string
    {
        if (empty($matcher)) {
            $matcher = \fan\project\service\matcher::instance();
        }
        $language = $matcher->getLastItem()->parsed->language;
        return empty($language) ? null : $language;
    }

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\


}
