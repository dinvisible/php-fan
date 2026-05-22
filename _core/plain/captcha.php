<?php

declare(strict_types=1);

namespace fan\core\plain;
//use fan\project\exception\plain\fatal as fatalException;
/**
 * Base access for plain files (uploaded to the server) class
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

class captcha
{
    /**
     * Handler object
     * @var \fan\core\service\plain
     */
    protected ?object $handler = null;

    /**
     * Plain config object
     * @var \fan\core\service\config\row
     */
    protected ?object $config = null;

    /**
     * Plain config object
     * @var \fan\core\service\captcha
     */
    protected ?object $captcha = null;

    /**
     * Key of plain controller
     * @var string
     */
    protected ?string $key = null;

    /**
     * @var
     */
    protected ?string $app = null;

    public function __construct(\fan\core\service\plain $handler, $key)
    {
        $this->handler = $handler;
        $this->key     = (string)$key;

        $handle = $handler->getHandleData();
        $this->captcha = service('captcha', (string)$handle['reqKey']);
    }

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\

    public function getCaptcha(): string
    {
        //return 'getCaptcha-' . $this->captcha->getText() . '!!!!';
        //return $this->_prepare()->_init()->_getContent();
        return $this->_init()->_getContent();
    } // getCaptcha

    public function getKey(): ?string
    {
        return $this->key;
    } // getKey

    // ======== Private/Protected methods ======== \\

    protected function _init(): static
    {
        //$this->handler->setErrorMessage(msg('ERROR_REQUESTED_FILE_IS_NOT_FOUND'));
        $headers = $this->captcha->getHeaders();
        foreach ($headers as $k => $v) {
            $this->handler->addHeader($k, $v);
        }
        return $this;
    }

    protected function _getContent(): string
    {
        return $this->captcha->getBinaryData();
    }

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\

    public function setConfig(\fan\core\service\config\row $config): static
    {
        if (empty($this->config)) {
            $this->config = $config;
        }
        return $this;
    } // setConfig

}
