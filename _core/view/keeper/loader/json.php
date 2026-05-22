<?php
declare(strict_types=1);

namespace fan\core\view\keeper\loader;
/**
 * View-data keeper of Block data for loader JSON-data
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
class json extends \fan\core\view\keeper
{
    public function __construct(\fan\core\view\router $router)
    {
        parent::__construct($router);
        $this->fullRewrite = true;
    }

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\
    public function addRouter(\fan\core\view\router\loader $router): void
    {
        $this->_setSetter($router);
        $this->_setSetter($router->getBlock());
    }

    // ======== Private/Protected methods ======== \\

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\

}
