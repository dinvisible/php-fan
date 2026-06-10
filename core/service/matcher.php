<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\core\base\service\single;
use fan\core\service\matcher\item;
use fan\core\service\matcher\item\handler;
use fan\core\service\matcher\item\parsed;
use fan\core\service\matcher\item\uri;
use fan\core\service\matcher\stack;

/**
 * Description of matcher
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
 * @version of file: 05.02.007 (31.08.2015)
 */
class matcher extends single
{
    /**
     * @var \fan\core\service\matcher\stack Stack of requested URI
     */
    protected ?object $stack = null;

    // ============= Init Data ============= \\
    public function __construct(
        bool $allowIni = true,
        ?object $input = null,
        ?object $runtime = null,
        ?object $locale = null,
        ?object $application = null,
        ?object $routeFileStorage = null,
        ?callable $itemFactory = null,
        ?callable $itemComponentFactory = null,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null
    )
    {
        parent::__construct($allowIni, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory);
        $this->stack = $this->_getEngine('stack');
        if (method_exists($this->stack, 'setItemDependencies')) {
            $serviceExceptionFactory = $serviceBootstrapRuntime !== null && method_exists($serviceBootstrapRuntime, 'serviceExceptionFactory')
                ? $serviceBootstrapRuntime->serviceExceptionFactory()
                : null;
            $this->stack->setItemDependencies($input, $runtime, $locale, $application, $routeFileStorage, $itemFactory, $itemComponentFactory, $serviceExceptionFactory);
        }
    }

    /**
     * @param string $request Request object or payload handled by the operation.
     */
    public function setUri(string $request, ?string $host = null, bool $shiftCurrent = true): static
    {
        $this->stack->setNewItem($request, $host, $shiftCurrent);
        $this->_broadcastMessage('setNewUri', $this);
        return $this;
    }

    /**
     * @param mixed $file File path or file descriptor handled by the operation.
     */
    public function setCli(string $file, ?string $path = null): static
    {
        $this->stack->setNewItem($file, $path, false);
        $this->_broadcastMessage('setNewUri', $this);
        return $this;
    }

    // ============= Get Current/Last indexes ============= \\
    public function getLastIndex(): int
    {
        return $this->stack->getLastIndex();
    }

    public function getCurrentIndex(): int
    {
        return $this->stack->getCurrentIndex();
    }

    // ============= Get Common Data ============= \\
    public function getStack(): stack
    {
        return $this->stack;
    }

    /**
     * @throws \fan\project\exception\service\fatal
     */
    public function getItem(int $number): item
    {
        if (!isset($this->stack[$number])) {
            throw $this->createServiceFatalException('Requested item number "' . $number . '" isn\'t set');
        }
        return $this->stack[$number];
    }

    public function getLastItem(): item
    {
        return $this->getItem((int)$this->getLastIndex());
    }

    public function getCurrentItem(): item
    {
        return $this->getItem((int)$this->getCurrentIndex());
    }

    // ============= Get URI ============= \\
    public function getUri(int $number): uri
    {
        $item = $this->getItem($number);
        return $item['uri'];
    }

    public function getLastUri(): uri
    {
        return $this->getUri((int)$this->getLastIndex());
    }

    public function getCurrentUri(): uri
    {
        return $this->getUri((int)$this->getCurrentIndex());
    }

    // ============= Get Handler ============= \\
    public function getHandler(int $number, bool $forceDefine = false): handler
    {
        $item = $this->getItem($number);
        return $item->getHandler($forceDefine);
    }

    public function getCurrentHandler(bool $forceDefine = false): handler
    {
        return $this->getHandler((int)$this->getCurrentIndex(), $forceDefine);
    }

    // ============= Get Parsed data ============= \\
    public function getParsedData(int $number): parsed
    {
        $item = $this->getItem($number);
        return $item['parsed'];
    }

    public function getLastParsedData(): parsed
    {
        return $this->getParsedData((int)$this->getLastIndex());
    }

    public function getCurrentParsedData(): parsed
    {
        return $this->getParsedData((int)$this->getCurrentIndex());
    }

}
