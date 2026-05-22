<?php

declare(strict_types=1);

namespace fan\core\service\form\validator;
/**
 * Base class of validators
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
 */
abstract class base
{
    /**
     * Form config
     * @var \fan\core\service\form
     */
    protected ?object $facade = null;

    /**
     * Form config
     * @var \fan\core\service\config\row
     */
    protected ?object $config = null;

    public function setFacade(\fan\core\service\form $facade): static
    {
        if (empty($this->facade)) {
            $this->facade = $facade;
            $this->config = $facade->getConfig();
        }
        return $this;
    }
}
