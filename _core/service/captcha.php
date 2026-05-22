<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
/**
 * Captcha manager service
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
 * @version of file: 05.02.004 (25.12.2014)
 */
class captcha extends \fan\core\base\service\multi
{
    /**
     * Service's Instances
     * @var \fan\core\service\captcha[]
     */
    private static array $instances = [];

    /**
     * Engine object of text generator
     * @var \fan\core\service\captcha\base
     */
    private ?object $textGenerator = null;
    /**
     * Engine object of binary File Maker
     * @var \fan\core\service\captcha\base
     */
    private ?object $fileMaker = null;

    /**
     * Form Id
     * @var string
     */
    private ?string $formId = null;

    protected function __construct(string $formId)
    {
        parent::__construct(true);
        $this->formId = (string)$formId;
        self::$instances[$this->formId] = $this;
    }

    // ======== Static methods ======== \\
    public static function instance(string|\fan\core\block\form\usual $formId): static
    {
        if (is_object($formId) && $formId instanceof \fan\core\block\form\usual) {
            $formId = (string)$formId->getMeta(['form', 'form_id']);
        } elseif (!is_string($formId)) {
            $formId = 'main';
        }
        if (!isset(self::$instances[$formId])) {
            new self($formId);
        }
        return self::$instances[$formId];
    }

    // ======== Main Interface methods ======== \\
    public function makeNewText(mixed $length = null, mixed $type = null): static
    {
        if (is_null($length)) {
            $length = $this->getConfig('TEXT_LENGTH', 5);
        }
        if (is_null($type)) {
            $type = $this->getConfig('TEXT_TYPE', 'char');
        }
        $text = $this->_getTextGenerator()->makeNewText((int)$length, (string)$type);
        $this->_getSession()->set($this->formId, $text);
        return $this;
    }

    public function getText(): mixed
    {
        return $this->_getSession()->get($this->formId);
    }

    public function clearText(): mixed
    {
        return $this->_getSession()->remove($this->formId);
    }

    public function checkCaptcha(string $text, bool $del = true): bool
    {
        $ret = strlen($text) > 0 && strtolower($text) === strtolower((string)$this->getText());
        if ($del) {
            $this->clearText();
        }
        return $ret;
    }

    public function getUrn(): string
    {
        return $this->getConfig('URN_PREFIX' ,  '/captcha/') . $this->formId;
    }

    public function getHeaders(): array
    {
        return $this->_getFileMaker()->getHeaders();
    }

    public function getBinaryData(): string
    {
        return $this->_getFileMaker()->getData();
    }

    // ======== Private/Protected methods ======== \\
    protected function _getTextGenerator(): \fan\core\service\captcha\base
    {
        if (empty($this->textGenerator)) {
            $engine = $this->getConfig('TEXT_GENERATOR', 'simple');
            $this->textGenerator = $this->_getEngine('text_generator\\' . $engine);
            $this->textGenerator->setConfig($this->config);
        }
        return $this->textGenerator;
    }

    protected function _getFileMaker(): \fan\core\service\captcha\base
    {
        if (empty($this->fileMaker)) {
            $engine = $this->getConfig('FILE_MAKER', 'picture_1');
            $this->fileMaker = $this->_getEngine('file_maker\\' . $engine);
            $this->fileMaker->setConfig($this->config);
        }
        return $this->fileMaker;
    }

    protected function _getSession(): mixed
    {
        return $this->containerService('session', 'captcha', 'service');
    }

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\
}
