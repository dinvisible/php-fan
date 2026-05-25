<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\core\base\model\entity as model_entity;
use fan\core\base\model\rowset;
use fan\core\base\service\multi;
use fan\core\block\base;


/**
 * Pager service
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
class pager extends multi
{
    /**
     * Form Id
     * @var \fan\core\block\base
     */
    private ?object $block = null;

    /**
     * Page nummber
     * @var numeric
     */
    protected int|float|null $pageNum = null;
    /**
     * Total quantity of pages
     * @var numeric
     */
    protected int|float|null $pageQtt = null;

    /**
     * Quantity items per page
     * @var numeric
     */
    protected int|float|null $itemPerPage = null;
    /**
     * Total quantity of items
     * @var numeric
     */
    protected int|float|null $itemQtt = null;

    /**
     * @var callable|null
     */
    private $pagerEntityFactory = null;

    /**
     * @var callable|null
     */
    private $pagerTabFactory = null;

    /**
     * @var callable|null
     */
    private $pagerRequestFactory = null;

    public function __construct(
        base $block,
        ?callable $entityFactory = null,
        ?callable $tabFactory = null,
        ?callable $requestFactory = null,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null
    )
    {
        parent::__construct(true, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory);
        $this->setPagerDependencies($entityFactory, $tabFactory, $requestFactory);
        $this->block = $block;
    }

    // ======== Main Interface methods ======== \\

    public function setPagerDependencies(
        ?callable $entityFactory = null,
        ?callable $tabFactory = null,
        ?callable $requestFactory = null
    ): static
    {
        $this->pagerEntityFactory = $entityFactory;
        $this->pagerTabFactory = $tabFactory;
        $this->pagerRequestFactory = $requestFactory;

        return $this;
    }

    public function setPageNum(int|float $pageNum, bool $force = false): static
    {
        $pageNum = round($pageNum);
        if ($pageNum < 1) {
            $pageNum = 1;
        }
        if (!is_null($this->pageQtt) && $pageNum > $this->pageQtt) {
            if ($force) {
                $this->pageQtt = $pageNum;
            } else {
                $pageNum = $this->pageQtt;
            }
        }
        $this->pageNum = $pageNum;
        return $this;
    }
    public function getPageNum(): int|float|null
    {
        return $this->pageNum;
    }

    public function setPageQtt(int|float $pageQtt, bool $force = true): static
    {
        $pageQtt = round($pageQtt);
        if ($pageQtt < 1) {
            $pageQtt = 1;
        }
        if (!is_null($this->pageNum) && $pageQtt < $this->pageNum) {
            if ($force) {
                $this->pageNum = $pageQtt;
            } else {
                $pageQtt = $this->pageNum;
            }
        }
        $this->pageQtt = $pageQtt;
        return $this;
    }
    public function getPageQtt(): int|float|null
    {
        return $this->pageQtt;
    }

    public function setItemPerPage(int|float $itemPerPage): static
    {
        $itemPerPage = round($itemPerPage);
        if ($itemPerPage < 1) {
            $itemPerPage = $this->getConfig('DEFAULT_ITEM_PER_PAGE', 10);
        }
        $this->itemPerPage = $itemPerPage;
        return $this;
    }
    public function getItemPerPage(): mixed
    {
        return is_null($this->itemPerPage) ? $this->getConfig('DEFAULT_ITEM_PER_PAGE', 10) : $this->itemPerPage;
    }

    public function setItemQtt(int|float|string $itemQtt, bool $definePageNum = true): static
    {
        $this->itemQtt = round((float)$itemQtt);
        if ($definePageNum) {
            $this->_definePageNum()
                    ->_defineItemPerPage()
                    ->_definePageQtt();
        }
        return $this;
    }
    public function getItemQtt(): int|float|null
    {
        return $this->itemQtt;
    }

    public function getOffset(): int|float
    {
        return ($this->getPageNum() - 1) * $this->getItemPerPage();
    }

    /**
     * @throws \fan\project\exception\service\fatal
     */
    public function getItemsByParam(mixed $param = [], string $orderBy = ''): rowset
    {
        $meta = $this->block->getMeta('pager');
        if (!is_object($meta) || (string)$meta['entity_key'] === '') {
            throw $this->createServiceFatalException('Entity key is not set.');
        }
        $ett    = $this->pagerEntity()->get((string)$meta['entity_key']);
        $sqlKey = $meta['sql_key'];
        return $this->getItemsByKey($ett, $sqlKey, $param, $orderBy);
    }

    public function getItemsByKey(string|model_entity $ett, string $sqlKey = '', mixed $param = [], string $orderBy = ''): rowset
    {
        $ett = is_object($ett) && $ett instanceof model_entity ? $ett : $this->pagerEntity()->get($ett);

        $this->_definePageNum()
                ->_defineItemPerPage()
                ->_countItemByEtt($param, $ett, $sqlKey)
                ->_definePageQtt();

        $qtt    = $this->getItemPerPage();
        $offset = $this->getOffset();
        $items  = empty($sqlKey) ?
                $ett->getRowsetByParam($param, $qtt, $offset, $orderBy) :
                $ett->getRowsetByKey($sqlKey, $param, $qtt, $offset, $orderBy);
        return $items;
    }

    public function getPageUri(int|string $page, mixed $addExt = true, mixed $addSid = null, mixed $protocol = null): string
    {
        $key = $this->getConfig('PAGE_REQUEST_KEY', 'page');
        $modifier = [
            'exclude' => [
                'A' => [$key],
                'G' => [$key],
            ]
        ];
        $by = $this->block->getMeta(['pager', 'paging_by']);
        if (empty($by)) {
            $by = $this->getConfig('PAGING_BY', 'GET');
        }
        $modifier['include'][strtolower((string)$by) === 'add' ? 'A' : 'G'][$key] = $page;

        return $this->pagerTab()->getModifiedCurrentURI($modifier, $addExt, $addSid, $protocol);
    }

    // ======== Private/Protected methods ======== \\
    protected function _countItemByEtt(mixed $param, string|model_entity $ett, ?string $sqlKey = null): static
    {
        $ett = is_object($ett) ? $ett : $this->pagerEntity()->get($ett);
        $this->itemQtt = empty($sqlKey) ?
                $ett->getCountByParam($param) :
                $ett->getCountByKey($sqlKey, $param);
        return $this;
    }

    protected function _definePageNum(bool $force = false): static
    {
        if (is_null($this->pageNum) || $force) {
            $key  = $this->getConfig('PAGE_REQUEST_KEY', 'page');
            $src  = $this->getConfig('PAGE_REQUEST_SRC', 'AG');
            $page = (int)$this->pagerRequest()->get($key, $src, 1);
            $this->setPageNum($page);
        }
        return $this;
    }

    protected function _defineItemPerPage(bool $force = false): static
    {
        if (is_null($this->itemPerPage) || $force) {
            $qtt = (int)$this->block->getMeta(['pager', 'item_per_page']);
            //ToDo: Pay attantion on quatifyer
            $this->setItemPerPage($qtt);
        }
        return $this;
    }

    protected function _definePageQtt(bool $force = false): static
    {
        if (is_null($this->pageQtt) || $force) {
            $pageQtt = ceil($this->getItemQtt() / $this->getItemPerPage());
            $this->setPageQtt($pageQtt);
        }
        return $this;
    }

    private function pagerEntity(): object
    {
        if ($this->pagerEntityFactory !== null) {
            return ($this->pagerEntityFactory)();
        }

        throw new \RuntimeException('Entity service is not configured for pager service.');
    }

    private function pagerTab(): object
    {
        if ($this->pagerTabFactory !== null) {
            return ($this->pagerTabFactory)();
        }

        throw new \RuntimeException('Tab service is not configured for pager service.');
    }

    private function pagerRequest(): object
    {
        if ($this->pagerRequestFactory !== null) {
            return ($this->pagerRequestFactory)();
        }

        throw new \RuntimeException('Request service is not configured for pager service.');
    }

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\
}
