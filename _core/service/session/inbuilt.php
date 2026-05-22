<?php

declare(strict_types=1);

namespace fan\core\service\session;
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
    use \fan\core\di\container_aware_trait;

    /**
     * Facade of service
     * @var fan\core\service\session
     */
    protected ?object $facade = null;

    public function __construct(mixed $sid)
    {
        if (!empty($sid)) {
            $this->setSessionId((string)$sid);
        }
        session_start();
    }

    public function setFacade(\fan\core\service\session $facade): static
    {
        if (empty($this->facade)) {
            $this->facade = $facade;
        }
        return $this;
    }

    public function getSessionId(): string
    {
        return session_id();
    } // getSessionId

    public function setSessionId(string $sid): static
    {
        session_id($sid);
        return $this;
    } // setSessionId

    public function getSessionName(): string
    {
        return session_name();
    } // getSessionName

    public function &getData(string $group, string $sesName): mixed
    {
        if (!isset($_SESSION[$group])) {
            $_SESSION[$group] = [$sesName => null];
        } elseif (!is_array($_SESSION[$group]) || !array_key_exists($sesName, $_SESSION[$group])) {
            $_SESSION[$group][$sesName] = null;
        }
        return $_SESSION[$group][$sesName];
    }

    public function &getRoot(): array
    {
        return $_SESSION;
    }

    public function destroy(): static
    {
        if (session_status() === PHP_SESSION_ACTIVE && !session_destroy()) {
            $this->containerService('error')->logErrorMessage('Failed to destroy native PHP session.', 'Session error', '', true, false);
        }
        return $this;
    }
}
