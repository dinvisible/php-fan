<?php

declare(strict_types=1);

namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
/**
 * Email manager service
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
 * @version of file: 05.02.006 (20.04.2015)
 */
class email extends \fan\core\base\service\multi
{

    private static ?array $instances = null;

    private ?object $engine = null;

    private ?string $instName = null;

    protected function __construct(?string $instName)
    {
        parent::__construct(true);

        $this->instName = (string)$instName;

        if ($this->isEnabled()) {
            $config = $this->config;
            $this->engine = $this->_getEngine((string)$config->get('ENGINE', 'phpmailer'));

            if (!empty($config->FROM_EMAIL)) {
                $lng = $this->containerService('locale');
                /* @var $lng \fan\core\service\locale */
                $key = 'FROM_NAME' . ($lng->isEnabled() ? '_' . $lng->getLanguage() : '');
                if (empty($config->$key)) {
                    $key = 'FROM_NAME';
                }
                $this->setFrom((string)$config->FROM_EMAIL, (string)$config->get($key, ''));
            }

        }
    }

    public static function instance(?string $instName = 'default'): static
    {
        $className = __CLASS__;
        if (is_null($instName)) {
            $config = self::staticContainerService('config')->get(get_class_name($className));
            $instName = empty($config['DEFAULT_NAME']) ? 'DEFAULT_EMAIL_NAME' : $config['DEFAULT_NAME'];
        }
        if (!isset(self::$instances[$instName])) {
            self::$instances[$instName] = new $className($instName);
        }
        return self::$instances[$instName];
    }

    public function setFrom(string $emailFrom, string $nameFrom = ''): static
    {
        if ($this->isEnabled()) {
            $this->engine->setFrom($emailFrom, $this->_recodingText($nameFrom, 'NAME_RECODING'));
        }
        return $this;
    }

    public function clearAllRecipients(): static
    {
        if ($this->isEnabled()) {
            $this->engine->ClearAllRecipients();
        }
        return $this;
    }

    public function addCc(string $address, string $name = ''): static
    {
        if ($this->isEnabled()) {
            $this->engine->AddCC($address, $name);
        }
        return $this;
    }

    public function addBcc(string $address, string $name = ''): static
    {
         if ($this->isEnabled()) {
            $this->engine->addBCC($address, $name);
        }
        return $this;
    }

    public function addReplyTo(string $address, string $name = ''): static
    {
        if ($this->isEnabled()) {
            $this->engine->AddReplyTo($address, $name);
        }
        return $this;
    }

    public function addAttachment(string $path, string $name = '', string $encoding = 'base64', string $type = 'application/octet-stream'): static
    {
        if ($this->isEnabled()) {
            $this->engine->AddAttachment($path, $name, $encoding, $type);
        }
        return $this;
    }

    public function send(string $subj, string $body, string $emailTo, string $nameTo = '', bool $isHtml = false): ?bool
    {
        return $this->isEnabled() ? $this->engine->send($this->_recodingText($subj, 'SUBJECT_RECODING'), $this->_recodingText($body, 'BODY_RECODING'), $emailTo, $this->_recodingText($nameTo, 'NAME_RECODING'), $isHtml) : null;
    }

    public function sendTemplate(string $templateName, mixed $placeholders, string $emailTo, string $nameTo = '', bool $isHtml = true): ?bool
    {
        $fullPath = $this->_checkFilename($templateName);
        if (!$fullPath) {
            throw new fatalException($this, 'Incorrect path for email template "' . $templateName . '"');
        }
        $st = $this->containerService('template');
        //$st->disableStrip();
        $tplObj = $st->get($fullPath);

        if (!is_array($placeholders)) {
            $placeholders = [];
        }
        if (!isset($placeholders['SUBJECT_SEPARATOR'])) {
            $placeholders['SUBJECT_SEPARATOR'] = md5(microtime());
        }
        foreach ($placeholders as $key => $value) {
            $tplObj->assign((string)$key, $value);
        }

        $content = $tplObj->fetch();

        if (strpos($content, $placeholders['SUBJECT_SEPARATOR']) === false) {
            throw new \UnexpectedValueException('Use variable {$SUBJECT_SEPARATOR} in the email-template for separate "Subject and Body"');
        } else {
            list($subj, $body) = explode((string)$placeholders['SUBJECT_SEPARATOR'], $content, 2);
        }

        return $this->send($subj, trim($body), $emailTo, $nameTo, $isHtml);
    }


    public function sendTemplatePlain(string $templateName, array $placeholders, string $emailTo, string $nameTo = '', bool $isHtml = false): ?bool
    {
        $fullPath = $this->_checkFilename($templateName);
        if (!$fullPath) {
            return null;
        }
        $content = is_readable($fullPath) ? file_get_contents($fullPath) : '';
        if (!$content) {
            return null;
        }

        foreach ((array) $placeholders as $key => $value) {
            $content = str_replace((string)$key, (string)$value, $content);
        }

        list($subj, $body) = explode("\n", $content, 2);
        return $this->send($subj, $body, $emailTo, $nameTo, $isHtml);
    }

    public function getInstanceName(): ?string
    {
        return $this->instName;
    }

    protected function _checkFilename(string &$templateName): ?string
    {
        $dir = [''];
        if ($this->config['EMAIL_DIR']) {
            $dir[] = \bootstrap::parsePath((string)$this->config['EMAIL_DIR']);
        }

        $emailExt = (string)$this->config['EMAIL_TPL_EXT'];
        if (substr($templateName, -strlen($emailExt)) === $emailExt) {
            $templateName = substr($templateName, 0, -strlen($emailExt));
        }
        $language = $this->containerService('locale')->get();
        foreach ($dir as $dir) {
            if ($language && is_file($dir . $templateName . '.' . $language . $emailExt)) {
                $templateName .= '.' . $language . $emailExt;
                return $dir . $templateName;
            } elseif (is_file($dir . $templateName . $emailExt)) {
                $templateName .= $emailExt;
                return $dir . $templateName;
            }
        }
        return null;
    }

    protected function _validEmail(string $email): int|false {
        return preg_match('/^[a-z][a-z_0-9.-]+@([a-z0-9-]+\.)+[a-z]{2,4}$/i', $email);
    }

    protected function _recodingText(string $src, string $code): string {
        $tmp = '';
        if ($this->config->get($code)) {
            [$fromC, $toC] = array_pad(explode('=>', (string)$this->config->get($code), 2), 2, '');
            $toC = trim($toC);
            $charset = $toC ? $toC : (string)$this->config->get('CHARSET');
            if (!preg_match('/\/\/\w+$/', $charset)) {
                $charset .= '//IGNORE';
            }
            $tmp = iconv(trim($fromC), $charset, $src);
        }
        return empty($tmp) ? $src : $tmp;
    }

}
