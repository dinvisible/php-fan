<?php

declare(strict_types=1);

namespace fan\core\block\common;
use fan\core\block\base;

/**
 * Pager base class
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
 * @version of file: 05.02.004 (25.12.2014)
 */
class html_pager extends base
{
    /**
     * Name of block
     * @var \fan\core\service\pager
     */
    protected mixed $pager = '';

    public function init(): void
    {
    }


    public function getPageUri(int|string $page): string
    {
        return $this->pager->getPageUri($page);
    }

    public function getEmbeddedForm(): string
    {
        return '';
    }

    public function _getPageGroup(int|float|string $pageQtt, int|float|string $curPage): array
    {
        $pageQtt = (int)$pageQtt;
        $curPage = (int)$curPage;
        $qttLimit = (array)$this->getMeta('qttLimit', ['startEnd' => 2, 'middle' => 5], true);
        $midlHalf = floor($qttLimit['middle'] / 2);
        $stEnd    = (int)$qttLimit['startEnd'];
        if ($pageQtt <= $stEnd * 2 + $qttLimit['middle']) {
            $pages = [
               range(1, $pageQtt),
           ];
        } elseif ($curPage <= $stEnd + $midlHalf + 1) {
            $pages = [
                range(1, $curPage + $midlHalf),
                range($pageQtt - $stEnd + 1, $pageQtt)
            ];
        } elseif ($pageQtt - $curPage <= $stEnd + $midlHalf) {
            $pages = [
                range(1, $stEnd),
                range($curPage - $midlHalf, $pageQtt)
            ];
        } else {
            $pages = [
                range(1, $stEnd),
                range($curPage - $midlHalf, $curPage + $midlHalf),
                range($pageQtt - $stEnd + 1, $pageQtt)
            ];
        }
        $start = $curPage - $midlHalf;
        $end = $curPage + $midlHalf;

        return [
            'pagesNL' => range(
                $start <= 0 ? 1 : $start,
                $end >= $pageQtt ? $pageQtt : $end
            ),
            'pages'   => $pages,
        ];
    }

    protected function _postCreate(): void
    {
        $this->pager = $this->pagerService($this->getContainer());
        // ToDo: Define quntifire there
    }

    protected function _preOutput(): void
    {
        $pageQtt   = $this->pager->getPageQtt();
        $curPage   = $this->pager->getPageNum();
        $pageGroup = $this->_getPageGroup($pageQtt, $curPage);

        $this->view->pageQtt      = $pageQtt;
        $this->view->currentPage  = $curPage;
        $this->view->pages        = $pageGroup['pages'];
        $this->view->pagesNL      = $pageGroup['pagesNL'];
        $this->view->showIfOnePage = $this->getMeta(['quantifier', 'allow']) ? true : false;
        $this->view->tplType       = $this->getMeta('tplType', 'references');
    }

}
