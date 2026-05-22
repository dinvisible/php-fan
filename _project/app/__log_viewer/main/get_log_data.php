<?php

declare(strict_types=1);

namespace fan\app\__log_viewer\main;
/**
 * Get log data block
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
 * @version of file: 05.02.006 (20.04.2015)
 */
class get_log_data extends \fan\project\block\loader\base
{

    public function init(): void
    {
        $data = $this->getData();
        $json = [];

        list($date, $num) = explode_alt('_', $data['date'], 2);
        $isUnique = $data['gr_idt'];

        $pageQtt = $curPage = 1;
        $parser = service('log')->getLogParser($data['vr'], $data['date']);
        if ($parser->isData()) {
            do {
                if (!empty($data['del']) && role('allow_delete')) {
                    $parser->deleteRows($data['del'], $isUnique);
                    if (!$parser->isData()) {
                        $json['oper'] = 'redraw';
                        break;
                    }
                    $data['redraw'] = 1;
                }

                $totalQtt = $parser->getQtt($isUnique);
                $elmPerPage = $this->getMeta('elmPerPage', 10);
                $pageQtt = ceil($totalQtt / $elmPerPage);
                if ($pageQtt < 1) {
                    $pageQtt = 1;
                }
                $curPage = !empty($data['curPage']) ? $data['curPage'] : 1;
                if ($curPage < 1) {
                    $curPage = 1;
                } elseif ($curPage > $pageQtt) {
                    $curPage = $pageQtt;
                }


                if (!empty($data['redraw'])) {
                    $offset = ($curPage - 1) * $elmPerPage;
                    $qtt    = $elmPerPage;
                    $json['oper'] = 'redraw';
                } else {
                    $afterLast = $parser->checkAfterLast($data['lastRecId'] ?? null, $isUnique);
                    if (is_null($afterLast)) {
                        $json['oper'] = 'none';
                        break;
                    }
                    if ($afterLast >= $curPage * $elmPerPage) {
                        $json['oper'] = 'page_only';
                        break;
                    }
                    if ($afterLast) {
                        $offset = $afterLast;
                        $qtt    = $elmPerPage - ($afterLast) % $elmPerPage; // ToDo: Check do not skip any elements
                        $json['oper'] = 'add';
                    } else {
                        $offset = 0;
                        $qtt    = $elmPerPage;
                        $json['oper'] = 'redraw';
                        $curPage = 1;
                    }
                }
                $records = $parser->getDataArr($offset, $qtt, $isUnique);
                if ($records) {
                    $json['records'] = $records;
                }
            } while (false);
        }

        $json['curPage'] = $curPage;
        $json['pageQtt'] = $pageQtt;

        $json['vr']      = $data['vr'];
        $json['curDate'] = dateM2L($date);
        $json['date']    = $data['date'];

        $this->setJson($json);
        $this->setText('ok');
    }
}
