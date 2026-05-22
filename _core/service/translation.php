<?php

declare(strict_types=1);

namespace fan\core\service;
use project\exception\service\fatal as fatalException;
/**
 * Description of translation
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
 * @version of file: 05.02.006 (20.04.2015)
 */
class translation extends \fan\core\base\service\single
{
    /**
     * Combi-message buffer
     * @var array
     */
    private array $combiArr = [];
    /**
     * Combi-message language
     * @var string
     */
    private ?string $combiLng = null;

    /**
     * @var \fan\core\service\locale
     */
    private ?object $locale = null;
    protected array $editableLng  = [];

    protected array $messages  = [];

    protected array $msgUseTag = [];
    protected array $tags      = [];
    protected array $referers  = [];

    protected array $forCall  = [];

    protected function __construct(bool $allowIni = true)
    {
        parent::__construct($allowIni);
        $this->locale      = $this->containerService('locale');
        $this->editableLng = array_keys((array)$this->locale->getAvailableLanguages());
    }

    public function __destruct()
    {
        foreach ($this->forCall as $m => $v) {
            $this->$m();
        }
    }

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\

    public function getCombiPart(): ?string
    {
        if (!$this->combiArr) {
            return null;
        }
        $key = array_shift($this->combiArr);
        return $this->getMessage($key, $this->combiLng);
    }

    public function getCombiMessage(array|string $keyList, ?string $lng = null): ?string
    {
        if (empty($lng)) {
            $lng = (string)$this->locale->getLanguage();
        }
        $keyList = is_array($keyList) ? $keyList : preg_split('/\s*,\s*/', $keyList, -1, PREG_SPLIT_NO_EMPTY);
        $this->combiLng = $lng;
        $key = array_shift($keyList);
        $this->combiArr = empty($keyList) ? [] : $keyList;
        return $this->getMessage((string)$key, $lng);
    }

    public function getCombiMessageAlt(array|string $phrases): string
    {
        $phrases = is_array($phrases) ? $phrases : preg_split('/\s*,\s*/', $phrases, -1, PREG_SPLIT_NO_EMPTY);
        $result = array_shift($phrases);
        while (strstr((string)$result, '{combi_part}') && !empty($phrases)) {
            $result = preg_replace('/\{combi_part\}/i', (string)array_shift($phrases), (string)$result, 1);
        }
        return (string)$result;
    }

    public function setEditableLng(array|string $editableLng): void
    {
        $editableLng = is_array($editableLng) ? $editableLng : [$editableLng];
        foreach ($editableLng as $lng) {
            $this->getMessageArr((string)$lng);
        }
        $this->editableLng = $editableLng;
    }

    public function getMessage(string $key, ?string $language = null, bool $enableML = true): ?string
    {
        if (!$key) {
            return $this->isEnabled() ? null : '';
        }

        $enableML = $enableML && $this->isEnabled();
        if ($enableML) {
            $keyF = $this->_formatKey($key);
            if (empty($keyF)) {
                throw new fatalException($this, 'Incorrect Key. You can\'t create message with key "' . $key . '"');
            }

            $availableLng = $this->locale->getAvailableLanguages();
            if (!$language || !isset($availableLng[$language])) {
                $language = $this->locale->isEnabled() ? $this->locale->getLanguage() : $this->locale->getDefaultLanguage();
            }

            if (!isset($this->messages[$language])) {
                $this->getMessageArr($language);
            }
            if (!isset($this->messages[$language][$keyF])) {
                $this->_setNewMessage($keyF, $key);
                $isNewMsg = true;
            }

            $ret  = isset($this->messages[$language][$keyF]) ? $this->messages[$language][$keyF] : null;
            $isTag = !empty($this->msgUseTag[$keyF]);
        } else {
            $ret = $key;
            $isTag = strstr($ret, '{') !== false;
        }

        if ($isTag) {
            $tags = $this->getTagArr();
            $matches = null;
            if (preg_match_all('/\{([^\}]+)\}/', $ret, $matches)) {
                foreach ($matches[1] as $k => $v) {
                    if (isset($tags[$v])) {
                        $ret = substr_replace($ret, $this->_getTag($v), strpos($ret, $matches[0][$k]), strlen($matches[0][$k]));
                    }
                }
            }
        }

        if ($enableML && class_exists('\fan\core\service\tab', false) && $this->containerService('tab')->isDebugAllowed()) {
            $this->_setReferer($keyF);
            $len = strpos($keyF, '_');
            if ($len > 0) {
                $pref = substr($keyF, 0, $len);
                if (!$this->getConfig(['MSG_PREFIX', $pref], false)) {
                    throw new \UnexpectedValueException('Incorrect prefix "' . $keyF . '" of message key.');
                }
            } else {
                throw new \UnexpectedValueException('Prefix is\'t set for message key "' . $keyF . '".');
            }
        }
        return $ret;
    }

    public function getAllMessages(): array
    {
        foreach ($this->editableLng as $lng) {
            $this->getMessageArr($lng);
        }
        return $this->messages;
    }

    public function getMessageArr(string $lng): array
    {
        $lng = (string)$lng;
        if (empty($this->messages[$lng])) {
            $path = $this->_getFilePath('MESSAGES_PATH', ['{LNG}' => $lng]);
            if (is_readable($path)) {
                $this->messages[$lng] = (array)\fan\project\adapter\php_array_file::load($path);
            } else {
                throw new \RuntimeException('Undefined message file "' . $path . '".');
            }
        }
        $ret = $this->messages[$lng];
        foreach ($this->getMsgUseTag() as $k => $v) {
            unset($ret[$k]);
        }
        return $ret;
    }

    public function getMsgUseTag(): array
    {
        if (empty($this->msgUseTag)) {
            $path = $this->_getFilePath('USE_TAGS_PATH');
            if (is_readable($path)) {
                $this->msgUseTag = \fan\project\adapter\php_array_file::load($path);
            } else {
                throw new \RuntimeException('Undefined message file "' . $path . '".');
            }
        }
        return $this->msgUseTag;
    }

    public function editMessageArr(string $key, array $data, bool $save = true): void
    {
        $this->getAllMessages();

        $isTag = false;
        $key = (string)$key;
        foreach ((array)$data as $k => $v) {
            $this->messages[$k][$key] = $v;
            if (strchr((string)$v, '{')) {
                $this->msgUseTag[$key] = true;
                $isTag = true;
                $this->forCall['_saveMsgUseTag'] = 1;
            }
        }
        if (!$isTag && isset($this->msgUseTag[$key])) {
            unset($this->msgUseTag[$key]);
            $this->forCall['_saveMsgUseTag'] = 1;
        }
        if ($save) {
            $this->forCall['_saveMessageArr'] = 1;
        }
    }

    public function deleteMessage(string $key): void
    {
        $isDel = false;
        $keyF = $this->_formatKey($key);
        foreach ($this->editableLng as $lng) {
            $this->getMessageArr($lng);
            if (isset($this->messages[$lng][$keyF])) {
                unset($this->messages[$lng][$keyF]);
                $isDel = true;
            }
        }
        if ($isDel) {
            $this->forCall['_saveMessageArr'] = 1;
            if (isset($this->msgUseTag[$keyF])) {
                unset($this->msgUseTag[$keyF]);
                $this->forCall['_saveMsgUseTag'] = 1;
            }
            $ref = $this->getRefererArr($key);
            if ($ref) {
                unset($this->referers[$keyF]);
                $this->forCall['_saveRefererArr'] = 1;
            }
        }
    }

    public function getTagArr(): array
    {
        if (!$this->tags) {
            $path = $this->_getFilePath('TAGS_PATH');
            $this->tags = \fan\project\adapter\php_array_file::load($path, []);
        }
        return $this->tags;
    }

    public function getRefererArr(?string $key = null): ?array
    {
        if (!$this->referers) {
            $path = $this->_getFilePath('REFERERS_PATH');
            $this->referers = \fan\project\adapter\php_array_file::load($path, []);
            $lng  = $this->locale->getAvailableLanguages();
            if ((string)$lng === (string)$this->locale->getDefaultLanguage()) {
                $this->getMessageArr($lng);
                foreach ($this->referers as $k => $v) {
                    if (!isset($this->messages[$lng][$k])) {
                        unset($this->referers[$k]);
                        $this->forCall['_saveRefererArr'] = 1;
                    }
                }
            }
        }
        $key = is_scalar($key) ? (string)$key : null;
        return $key ? (isset($this->referers[$key]) ? $this->referers[$key] : null) : $this->referers;
    }

    public function editTagArr(string $key, array $data, bool $save = true): void
    {
        $this->getTagArr();
        $key = (string)$key;
        $data = (array)$data;
        if (isset($data['tag'])) {
            $this->tags[$key]['tag'] = $data['tag'];
            if (strchr((string)$data['tag'], '{')) {
                $this->tags[$key]['isFunc'] = true;
            } elseif (isset($this->tags[$key]['isFunc'])) {
                unset($this->tags[$key]['isFunc']);
            }
        }
        if (isset($data['link'])) {
            if ($data['link']) {
                $this->tags[$key]['link'] = $data['link'];
            } elseif (isset($this->tags[$key]['link'])) {
                unset($this->tags[$key]['link']);
            }
        }
        if ($save) {
            $this->forCall['_saveTagArr'] = 1;
        }
    }

    /**
     * @param string $url URL used as the external request target.
     */
    public function checkUrlLng(string $url): ?array
    {
        $regExp = '/^((\/\/?)(' . implode('|', array_keys((array)$this->locale->getAvailableLanguages())) . '))\//';
        if (preg_match($regExp, $url, $matches)) {
            return $matches;
        }
        return null;
    }

    // ======== Private/Protected methods ======== \\

    protected function _formatKey(string $key): string
    {
        if (preg_match('/^\w+$/', $key)) {
            $key = strtoupper($key);
        } else {
            $key = (string)preg_replace('/\<[^\>]+\>/', ' ', $key);
            $key = (string)preg_replace('/[^a-zа-я0-9]+/iu', ' ', $key);
            $key = (string)preg_replace('/\s+/', '_', trim($key));
            $key = mb_strtoupper($key);
            if ($this->getConfig('MSG_KEY_ENGL_ONLY', true)) {
                $key = strtr($key, [
                    'А' => 'A',  'Б' => 'B',  'В' => 'V',
                    'Г' => 'G',  'Д' => 'D',  'Е' => 'E', 'Є' => 'Ye',
                    'Ё' => 'Yo', 'Ж' => 'Zh', 'З' => 'Z', 'І' => 'I',
                    'И' => 'I',  'Й' => 'J',  'К' => 'K', 'Ї' => 'Yi',
                    'Л' => 'L',  'М' => 'M',  'Н' => 'N',
                    'О' => 'O',  'П' => 'P',  'Р' => 'R',
                    'С' => 'S',  'Т' => 'T',  'У' => 'U',
                    'Ф' => 'F',  'Х' => 'Kh', 'Ц' => 'Ts',
                    'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Shch',
                    'Ь' => '\'', 'Ы' => 'Y',  'Ъ' => '"',
                    'Э' => 'E',  'Ю' => 'Yu', 'Я' => 'Ya',
                ]);
                $key = (string)iconv('UTF-8', 'ISO-8859-1//IGNORE', $key);
            }
        }
        return $key;
    }

    protected function _getFilePath(string $key, ?array $repl = null): string
    {
        $path = (string)$this->getConfig($key);
        if (empty($path)) {
            throw new fatalException($this, 'Incorrect Key. Key for path "' . $key . '" doesn\'t set');
        }
        if ($repl) {
            $path = strtr($path, (array)$repl);
        }
        return \bootstrap::parsePath($path);
    }

    protected function _getTag(string $key): string
    {
        $ret = (string)$this->tags[$key]['tag'];
        $matches1 = $matches2 = null;
        if (!empty($this->tags[$key]['isFunc']) && preg_match_all('/\{([^\}]+)\}/', $ret, $matches1)) {
            foreach ($matches1[1] as $k => $v) {
                [$class, $method, $arg] = array_pad(explode(':', (string)$v, 3), 3, '');
                if (preg_match('/^service\|(\w+)$/', $class, $matches2) && class_exists('\fan\project\service\\' . $matches2[1])) {
                    $callback = [service($matches2[1]), $method];
                } elseif (class_exists($class)) {
                    $callback = [$class, $method];
                }
                if (!empty($callback) && is_callable($callback)) {
                    $ret = str_replace($matches1[0][$k], (string)call_user_func($callback, $arg), $ret);
                } else {
                    $this->containerService('error')->logErrorMessage('Message tag "' . $key . '" is not callable.', 'Incorect message tag', '', true, false);
                }
            }
        }
        return $ret;
    }

    protected function _setReferer(string $key): void
    {
        $this->getRefererArr();

        list($stage, $path) = getCurBlockInfo();
        if (!$path) {
            $path = 'Unknown!';
        }
        if (!isset($this->referers[$key][$path][$stage])) {
            $source = service('matcher')->getItem(0)->source;
            $this->referers[$key][$path][$stage] = (string)($_SERVER['REQUEST_METHOD'] ?? '') . ': ' . (string)$source;
            $this->forCall['_saveRefererArr'] = 1;
        }
    }

    protected function _setNewMessage(string $keyF, string $key): void
    {
        foreach ($this->editableLng as $lng) {
            $this->getMessageArr($lng);
            $this->messages[$lng][$keyF] = '[' . $key . ']';
        }
        $this->forCall['_saveMessageArr'] = 1;
    }

    protected function _saveMessageArr(): void
    {
        foreach ($this->editableLng as $lng) {
            if (isset ($this->messages[$lng])) {
                ksort($this->messages[$lng]);
                file_put_contents($this->_getFilePath('MESSAGES_PATH', ['{LNG}' => $lng]), '<?php
/*
 * Short messages array for language "' . $lng . '"
 */
return ' . var_export($this->messages[$lng], true) . ';
?>');
            } else {
                throw new \UnexpectedValueException('Unavailable message array for save. Language = "' . $lng . '"');
            }
        }
    }

    protected function _saveMsgUseTag(): void
    {
        ksort($this->msgUseTag);
        file_put_contents($this->_getFilePath('USE_TAGS_PATH'), '<?php
/*
 * Array of messages used tags
 */
return ' . var_export($this->msgUseTag, true) . ';
?>');
    }

    protected function _saveTagArr(): void
    {
        ksort($this->tags);
        file_put_contents($this->_getFilePath('TAGS_PATH'), '<?php
/*
 * Tags array
 */
return ' . var_export($this->tags, true) . ';
?>');
    }

    protected function _saveRefererArr(): void
    {
        ksort($this->referers);
        file_put_contents($this->_getFilePath('REFERERS_PATH'), '<?php
/*
 * Referer array
 */
return ' . var_export($this->referers, true) . ';
?>');
    }

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\

}
