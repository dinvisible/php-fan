<?php

declare(strict_types=1);

namespace fan\app\__log_viewer\design;
/**
 * header_main block
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
class header extends \fan\project\block\base
{
    public function init(): void
    {
        $embeded = $this->getEmbeddedBlocks();
        $vrts    = $embeded['nav']->getVarieties();
        $curDate = date('Y-m-d');
        $dates   = [];

        foreach ($vrts as $k) {
            $dates[$k] = [];

            $path = (string)$k === 'bootstrap' ? \bootstrap::getGlobalPath('bootstrap_log') : \bootstrap::parsePath(service('log')->getConfig(['LOG_DIR', $k]));

            $this->setFileList($dates[$k], $path, '/^(\d{4}\-\d{2}\-\d{2})\_(\d{3})\.log$/');

            if (!isset($dates[$k][$curDate])) {
                $dates[$k][$curDate] = ['000'];
            }
        }
        $this->setFileList($dates['bootstrap'], \bootstrap::getGlobalPath('apache_log'), '/^error_(\d{4}\-\d{2}\-\d{2})\.log$/');

        reset($dates);
        $firstKey = key($dates);
        $isDelete = role('allow_delete');

        $this->_setViewVar('aCurSel', [$curDate,  $dates[$firstKey][$curDate][0]]);
        $this->_setViewVar('aDate', $dates[$firstKey]);
        $this->_setViewVar('isDelete', $isDelete);

        $ses = $this->containerService('session');
        $js  = $this->containerService('json')->encode($dates);
        $js .= ',\'' . $curDate . '\'';
        $js .= ',' . ($isDelete ? 1 : 0);
        $js .= ',\'' . $ses->getSessionName() . '=' . $ses->getSessionId() . '\'';
        $this->_getBlock('root')->setEmbedJs('logCtrl.init(' . $js . ');');
    }

    public function setFileList(&$dt, $path, $regexp): void
    {
        if (is_dir($path)) {
            $files = scandir($path);
            foreach ($files as $v) {
                if (preg_match($regexp, $v, $matches)) {
                    $date = $matches[1];
                    if (!isset($dt[$date])) {
                        $dt[$date] = [];
                    }
                    if (isset($matches[2])) {
                        $dt[$date][] = $matches[2];
                    } elseif (!isset($dt[$date])) {
                        $dt[$date] = ['000'];
                    }
                    sort($dt[$date]);
                }
            }
        } else {
            throw new \RuntimeException('Directory <b>' . $path . '</b> isn\'t found.');
        }
        ksort($dt);
    }
}
