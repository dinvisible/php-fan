<?php

declare(strict_types=1);

namespace fan\core\base\service;
use fan\core\base\service;

/**
 * Base abstract service
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
 * @abstract
 */
abstract class single extends service
{
    // ======== Main Interface methods ======== \\

    final public function isSingleton(): bool
    {
        return true;
    }

    // ======== Private/Protected methods ======== \\

    protected function _saveInstance(): static
    {
        $className = self::checkName(get_class($this));
        $state = $this->_singleState();
        if ($state->hasInstance($className)) {
            throw $this->createServiceFatalException('Dublicate of service init "' . $className . '"');
        }
        $state->setInstance($className, $this);

        return $this;
    }

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\

}
