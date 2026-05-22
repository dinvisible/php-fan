<?php
declare(strict_types=1);

namespace fan\core\service\matcher;
/**
 * Description of stack
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
class stack extends \ArrayIterator
{
    /**
     * Facade of service
     * @var fan\core\base\service
     */
    protected ?object $facade = null;
    protected int $current = 0;

    public function setFacade(\fan\core\base\service $facade): static
    {
        $this->facade = $facade;

        return $this;
    }

    public function setNewItem(string $request, ?string $position = null, bool $shiftCurrent = true): static
    {
        $index = count($this);
        if ($shiftCurrent) {
            $this->current = $index;
        }

        $item = new \fan\project\service\matcher\item($index);
        $this[$index] = $item;
        $item->setFacade($this->facade);

        if (\bootstrap::isCli()) {
            $item->initCli($request, (string)$position);
            // Pre-Parse Request
            //$item->preParseRequest($shiftCurrent);
        } else {
            $item->initOut($request, (string)$position);
            // Pre-Parse Request
            $item->preParseRequest($shiftCurrent);
        }

        return $this;
    }

    public function getLastIndex(): int
    {
        return count($this) - 1;
    }

    public function getCurrentIndex(): int
    {
        return $this->current;
    }

}
