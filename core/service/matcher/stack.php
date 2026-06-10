<?php
declare(strict_types=1);

namespace fan\core\service\matcher;
use fan\core\base\service;

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
    protected ?object $input = null;
    protected ?object $runtime = null;
    protected ?object $locale = null;
    protected ?object $application = null;
    protected ?object $routeFileStorage = null;
    protected mixed $itemFactory = null;
    protected mixed $itemComponentFactory = null;
    protected mixed $serviceExceptionFactory = null;

    public function setFacade(service $facade): static
    {
        $this->facade = $facade;

        return $this;
    }

    public function setItemDependencies(
        ?object $input = null,
        ?object $runtime = null,
        ?object $locale = null,
        ?object $application = null,
        ?object $routeFileStorage = null,
        ?callable $itemFactory = null,
        ?callable $itemComponentFactory = null,
        ?callable $serviceExceptionFactory = null
    ): static
    {
        $this->input = $input;
        $this->runtime = $runtime;
        $this->locale = $locale;
        $this->application = $application;
        $this->routeFileStorage = $routeFileStorage;
        $this->itemFactory = $itemFactory;
        $this->itemComponentFactory = $itemComponentFactory;
        $this->serviceExceptionFactory = $serviceExceptionFactory;

        return $this;
    }

    public function setNewItem(string $request, ?string $position = null, bool $shiftCurrent = true): static
    {
        $index = count($this);
        if ($shiftCurrent) {
            $this->current = $index;
        }

        $item = $this->matcherItem($index);
        $this[$index] = $item;
        $item->setFacade($this->facade);

        if ($this->runtime()->isCli()) {
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

    private function matcherItem(int $index): object
    {
        if (!is_callable($this->itemFactory)) {
            throw new \RuntimeException('Matcher item factory is not configured for matcher stack.');
        }

        return ($this->itemFactory)(
            $index,
            $this->input,
            $this->runtime,
            $this->locale,
            $this->application,
            $this->routeFileStorage,
            $this->itemComponentFactory,
            $this->serviceExceptionFactory
        );
    }

    protected function runtime(): object
    {
        if ($this->runtime !== null) {
            return $this->runtime;
        }

        throw new \RuntimeException('Bootstrap runtime service is not configured for matcher stack.');
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
