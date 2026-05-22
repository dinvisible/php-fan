<?php

declare(strict_types=1);

namespace fan\core\block\admin;
/**
 * Block admin select dependent
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
class select_dependent extends base
{
    public function init(): void
    {
        $this->containerService('role')->setSessionRoles('admin', $this->getMeta('login_timeout'));

        $data = $this->getData();

        $method = 'do_' . $data['op'];
        $this->setJson([
            'op'   => $data['op'],
            'data' => $this->$method($data['data'])
        ]);

        $this->setText('ok');
    }


    public function do_load_next_list(mixed $data): array
    {
        $level = $data['level'];
        $meta = $this->getMeta(['level_data', $level]);
        return [
            'hash'  => ge((string)$meta['entity'])->getRowsetByParam([$meta['param_key'] => $data['cval']])->getArrayHash($meta['key'], $meta['val']),
            'level' => $level,
            'cval'  => $data['cval'],
        ];
    }

}
