<?php
declare(strict_types=1);

namespace fan\core\service\tab;
use fan\core\base\service;


/**
 * Description of delegate
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
abstract class delegate extends engine
{
    /**
     * Facade of service
     * @var \fan\core\service\config\base
     */
    protected ?object $config = null;

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\

    public function setFacade(service $facade): static
    {
        parent::setFacade($facade);
        $this->config = $facade->getConfig();
        return $this;
    }

    public function getConfig(mixed $key = null, mixed $default = null): mixed
    {
        return is_null($key) || !is_object($this->config) ? $this->config : $this->config->get($key, $default);
    }

    // ======== Private/Protected methods ======== \\

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
}
