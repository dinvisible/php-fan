<?php

declare(strict_types=1);

namespace fan\core\service\entity\designer;
/**
 * Designer of snippety SQL-request
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
 * @version of file: 05.02.001 (10.03.2014)
 */
class snippety extends \fan\core\service\entity\designer
{
    /**
     * SQL-request snippets
     * @var string
     */
    protected array $queryParts = [
        'snippet' => [],
        'orderBy' => null,
    ];


    // ======== Static methods ======== \\

    // ======== The magic methods ======== \\

    // ======== Main Interface methods ======== \\

    public function setSqlRequest(string $queryKey): static
    {
        $request = $this->getEntity()->getRequestLoader();
        $sourceSQL = (string)$request->get($queryKey);
        if ($sourceSQL !== '') {
            $this->queryParts['snippet'] = substr($sourceSQL, 0, 2) === '##' ?
                    $this->_parseSQL(trim(substr($sourceSQL, 2))) :
                    [$sourceSQL];
        }
        return $this;
    }

    public function setRequestSnippet(mixed $snippetValue, bool $allowException = true): static
    {
        $this->set('snippet', $snippetValue, $allowException);
        return $this;
    }

    public function addRequestSnippet(string|array $snippetValue, bool $toEnd = true, bool $allowException = true): static
    {
        $this->add('snippet', $snippetValue, (bool)$toEnd, $allowException);
        return $this;
    }

    public function setOrderPart(mixed $partValue, bool $allowException = true): static
    {
        if (!empty($partValue)) {
            $this->set('orderBy', $partValue, $allowException);
        }
        return $this;
    }

    public function addOrderPart(string|array $partValue, bool $toEnd = true, bool $allowException = true): static
    {
        $this->add('orderBy', $partValue, $toEnd, $allowException);
        return $this;
    }

    // ======== Private/Protected methods ======== \\
    public function _parseSQL(string $sourceSQL): array
    {
        $sourceSQL = (string)$sourceSQL;
        $snippets = [];

        do {
            $matches = [];
            if (preg_match('/^(.*?)[\r\n]+\#\#([^#]+)(?:\-\#\-(\w+(?:\:\w+)?))?\#\#([\r\n].+?)[\r\n]+\#\#\-\-/s', $sourceSQL, $matches)) {
                if (!empty($matches[1])) {
                    array_push($snippets, $matches[1]);
                }
                array_push($snippets, $this->getEntity()->getService()->getSnippet($this, $matches[4], $matches[2], $matches[3]));
            } else {
                break;
            }

            $sourceSQL = substr($sourceSQL, strlen($matches[0]));
        } while (!empty($sourceSQL));

        if (!empty($sourceSQL)) {
            array_push($snippets, $sourceSQL);
        }
        return $snippets;
    }

}
