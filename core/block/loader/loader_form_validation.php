<?php

declare(strict_types=1);

namespace fan\core\block\loader;

/**
 * Base class for loader form validation block
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
 * @version of file: 05.02.006 (20.04.2015)
 * @abstract
 */
abstract class loader_form_validation extends base
{
    public function init(): void
    {
        $data = $this->getData();
        $this->setJson($data);
        if (empty($data['field'])) {
            $this->_makeBlockException('Method name for check field isn\'t set.', 'fatal');
        } elseif (method_exists($this, 'check_' . $data['field'])) {
            $ret = $this->{'check_' . $data['field']}($data['value'] ?? null, $this->getMeta(['err_message', $data['field']], ''));
            $this->setText(is_null($ret) ? 'ok' : $ret);
        } else {
            $this->_makeBlockException('Method "check_' .  $data['field'] . '" isn\'t found.', 'fatal');
        }
    }

}
