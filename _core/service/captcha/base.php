<?php
declare(strict_types=1);

namespace fan\core\service\captcha;
use fan\project\exception\service\fatal as fatalException;
/**
 * Description of captcha-engine
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
 * @version of file: 05.02.004 (25.12.2014)
 */
abstract class base
{
    /**
     * Service User
     * @var \fan\core\service\captcha
     */
    protected ?object $facade = null;

    /**
     * Row of config
     * @var \fan\core\service\config\row
     */
    protected ?object $config = null;

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\

    public function setFacade(\fan\core\service\captcha $facade): static
    {
        if (empty($this->facade)) {
            $this->facade = $facade;
        }
        return $this;
    }

    public function setConfig(\fan\core\service\config\row $config): static
    {
        if (empty($this->config)) {
            if (empty($config)) {
                throw new fatalException($this->facade, 'Captcha Engine has empty config!');
            }
            $this->config = $config;
        }
        return $this;
    }

    // ======== Private/Protected methods ======== \\
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\

}
