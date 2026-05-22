<?php

declare(strict_types=1);

namespace fan\app\__log_viewer\main;
/**
 * index block
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
 * @version of file: 05.02.001 (10.03.2014)
 */
class get_trace extends \fan\project\block\loader\base
{

    public function init(): void
    {
        $data = $this->getData();
        $json = [];

        $parser = service('log')->getLogParser($data['vr'], $data['date']);
        $trace  = $parser->getTrace($data['idRecord']);

        if ($trace) {
            $json['trace'] = $trace;
        }

        $json['idHtml'] = $data['idHtml'];

        $this->setJson($json);
        $this->setText('ok');
    }

}
