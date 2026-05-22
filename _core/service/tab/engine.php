<?php
declare(strict_types=1);

namespace fan\core\service\tab;

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
abstract class engine
{
    use \fan\core\di\container_aware_trait;

    /**
     * Facade of service
     * @var fan\core\base\service
     */
    protected ?object $facade = null;

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\

    public function setFacade(\fan\core\base\service $facade): static
    {
        if (empty($this->facade)) {
            $this->facade = $facade;
        }
        return $this;
    }

    // ======== Private/Protected methods ======== \\
    /**
     * @throws \fan\project\exception\service\fatal
     */
    protected function _makeException(mixed $message): never
    {
        throw new \fan\project\exception\service\fatal($this->facade, (string)$message);
    }

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
}
