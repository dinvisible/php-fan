<?php
declare(strict_types=1);

namespace fan\core\view\parser;
use fan\core\block\base;
use fan\core\view\parser;
use fan\core\view\router\json as router_json;

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
class json extends parser
{
    // ======== Static methods ======== \\

    final static public function getFormat(?callable $exceptionFactory = null): string {
        return 'json';
    }

    static public function getRouter(
        base $block,
        mixed $loaderStateOrFactory = null,
        ?callable $viewRouterFactory = null
    ): router_json {
        if (is_callable($loaderStateOrFactory) && $viewRouterFactory === null) {
            $viewRouterFactory = $loaderStateOrFactory;
        }
        $factory = static::viewRouterFactory($viewRouterFactory);
        $router = $factory(static::class, $block);
        if (!$router instanceof router_json) {
            throw new \UnexpectedValueException('View router factory must return a JSON view router.');
        }

        return $router;
    }

    // ======== Main Interface methods ======== \\
    public function getFinalContent(): string
    {
        $view = $this->rootBlock->getView();
        $useBase64 = method_exists($view, 'isUseBase64') && $view->isUseBase64();
        $result = $this->getJsonEncoder($useBase64)->encode($this->result);

        $this->_setHeaders($result, 'application/json');
        return $result;
    }

    // ======== Protected methods ======== \\
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\

}
