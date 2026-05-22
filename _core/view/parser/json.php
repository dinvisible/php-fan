<?php
declare(strict_types=1);

namespace fan\core\view\parser;
/**
 * View parser JSON-type
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
 * @version of file: 05.02.007 (31.08.2015)
 */
class json extends \fan\core\view\parser
{
    // ======== Static methods ======== \\

    final static public function getFormat(): string {
        return 'json';
    }

    static public function getRouter(\fan\core\block\base $block): \fan\core\view\router\json {
        return new \fan\project\view\router\json($block);
    }

    // ======== Main Interface methods ======== \\
    public function getFinalContent(): string
    {
        $view = $this->rootBlock->getView();
        $useBase64 = method_exists($view, 'isUseBase64') && $view->isUseBase64();
        $result = $this->containerService('json', $useBase64)->encode($this->result);

        $this->_setHeaders($result, 'application/json');
        return $result;
    }

    // ======== Protected methods ======== \\
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\

}
