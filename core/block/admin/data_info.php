<?php

declare(strict_types=1);

namespace fan\core\block\admin;
/**
 * Admin info data class for loader block
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
class data_info extends data
{

    protected function getMainData(mixed $data, mixed $force = []): array
    {
        if (!isset($force['template'])) {
            $force['template'] = 1;
        }
        return parent::getMainData($data, $force);
    }

    public function getExtraData(): array
    {
        $ret = parent::getExtraData();
        $ps = $this->getMeta('parsingScript');
        if ($ps) {
            $ret['parsingScript'] = $ps;
        }
        return $ret;
    }
}
