<?php

declare(strict_types=1);

namespace fan\core\base;
/**
 * Base abstract service
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
 * @abstract
 */
abstract class transfer extends \Exception
{
    protected ?string $transferType = null;
    protected ?string $newUri = null;
    protected ?string $newQueryString = null;

    public function __construct(string $newUri, ?string $newQueryString = null, ?string $dbOper = null)
    {
        $this->newUri = $newUri;
        $this->newQueryString = $newQueryString;
        if ($dbOper) {
            \fan\project\service\database::fixAll($dbOper, false);
        }
        parent::__construct($this->transferType, E_USER_NOTICE);
    }

    public function getTransferType(): ?string
    {
        return $this->transferType;
    }

    public function getRequest(): ?string
    {
        $newUri      = $this->getNewUri();
        $queryString = $this->getNewQueryString();
        if (empty($queryString) || (string)$queryString === '?') {
            return  $newUri;
        }
        $mainUri = strstr($newUri, '?', true);
        return (empty($mainUri) ? $newUri : $mainUri) . '?' . ltrim($queryString, '?');
    }

    public function getHost(): ?string
    {
        return null;
    }

    public function isShiftCurrent(): bool
    {
        return $this->getTransferType() !== 'sham';
    }

    public function getNewUri(): ?string
    {
        return $this->newUri;
    }

    public function getNewQueryString(): ?string
    {
        return $this->newQueryString;
    }

}
