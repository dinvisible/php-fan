<?php

declare(strict_types=1);

namespace fan\core\plain;
use fan\core\service\plain;

/**
 * Respond for request of obfuscate CSS or JS-file
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
 * @version of file: 05.02.008 (15.09.2015)
 */

class obfuscator
{
    /**
     * Handler object
     * @var \fan\core\service\plain
     */
    protected ?object $handler = null;
    /**
     * Plain config object
     * @var \fan\core\service\obfuscator
     */
    protected ?object $obfuscator = null;
    protected ?object $request = null;

    public function __construct(plain $handler, $key, ?callable $obfuscatorFactory = null, ?object $request = null)
    {
        $this->handler = $handler;
        $this->request = $request;

        $handle = $handler->getHandleData();
        $this->obfuscator = $this->createObfuscator((string)$handle['reqKey'], $obfuscatorFactory);
    }

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\

    public function getCss(): string|false
    {
        return $this->_getContent();
    } // getCss
    public function getJs(): string|false
    {
        return $this->_getContent();
    } // getJs

    // ======== Private/Protected methods ======== \\

    protected function _getContent(): string|false
    {
        $name = (string)$this->request()->get(1, 'A');
        $content = $this->obfuscator->getFileData($name);
        $headers = $this->obfuscator->getHeaders($name, strlen((string)$content));
        $this->handler->setHeaders($headers);
        return $content;
    }

    private function createObfuscator(string $type, ?callable $obfuscatorFactory): object
    {
        if (!is_callable($obfuscatorFactory)) {
            throw new \RuntimeException('Obfuscator service factory is not configured for plain obfuscator controller.');
        }

        return $obfuscatorFactory($type);
    }

    private function request(): object
    {
        if ($this->request !== null) {
            return $this->request;
        }

        throw new \RuntimeException('Request service is not configured for plain obfuscator controller.');
    }

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\

}
