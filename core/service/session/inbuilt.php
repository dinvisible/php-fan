<?php

declare(strict_types=1);

namespace fan\core\service\session;
use fan\core\service\session;

/**
 * PHP native session engine
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
class inbuilt
{
    /**
     * Facade of service
     * @var fan\core\service\session
     */
    protected ?object $facade = null;

    private ?object $input = null;

    private mixed $errorFactory = null;

    private ?object $nativeSession = null;

    public function __construct(mixed $sid, ?object $input = null, ?callable $errorFactory = null, ?object $nativeSession = null)
    {
        $this->input = $input;
        $this->errorFactory = $errorFactory;
        $this->nativeSession = $nativeSession;
        if (!empty($sid)) {
            $this->setSessionId((string)$sid);
        }
        $this->nativeSession()->start();
    }

    public function setFacade(session $facade): static
    {
        if (empty($this->facade)) {
            $this->facade = $facade;
        }
        return $this;
    }

    public function getSessionId(): string
    {
        return (string)$this->nativeSession()->id();
    } // getSessionId

    public function setSessionId(string $sid): static
    {
        $this->nativeSession()->id($sid);
        return $this;
    } // setSessionId

    public function getSessionName(): string
    {
        return (string)$this->nativeSession()->name();
    } // getSessionName

    public function &getData(string $group, string $sesName): mixed
    {
        return $this->input()->sessionValue($group, $sesName);
    }

    public function &getRoot(): array
    {
        return $this->input()->sessionRoot();
    }

    public function destroy(): static
    {
        if ($this->nativeSession()->status() === PHP_SESSION_ACTIVE && !$this->nativeSession()->destroy()) {
            $this->errorLogger()->logErrorMessage('Failed to destroy native PHP session.', 'Session error', '', true, false);
        }
        return $this;
    }

    private function input(): object
    {
        if ($this->input === null) {
            throw new \RuntimeException('Request input service is not configured for native session engine.');
        }

        return $this->input;
    }

    private function errorLogger(): object
    {
        if (!is_callable($this->errorFactory)) {
            throw new \RuntimeException('Error service factory is not configured for native session engine.');
        }

        return ($this->errorFactory)();
    }

    private function nativeSession(): object
    {
        return $this->nativeSession ?? throw new \RuntimeException('Native session adapter is not configured for native session engine.');
    }
}
