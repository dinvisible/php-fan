<?php
declare(strict_types=1);

namespace fan\core\view\parser;
use fan\core\block\base;
use fan\core\service\header;
use fan\core\view\parser;
use fan\core\view\router\html as router_html;

/**
 * View parser HTML-type
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
class html extends parser
{
    // ======== Static methods ======== \\
    final static public function getFormat(?callable $exceptionFactory = null): string {
        return 'html';
    }

    static public function getRouter(
        base $block,
        mixed $loaderStateOrFactory = null,
        ?callable $viewRouterFactory = null
    ): router_html {
        if (is_callable($loaderStateOrFactory) && $viewRouterFactory === null) {
            $viewRouterFactory = $loaderStateOrFactory;
        }
        $factory = static::viewRouterFactory($viewRouterFactory);
        $router = $factory(static::class, $block);
        if (!$router instanceof router_html) {
            throw new \UnexpectedValueException('View router factory must return an HTML view router.');
        }

        return $router;
    }

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    public function getResultData(base $block): array
    {
        $tplVar = $block->getViewData();

        foreach ($block->getEmbeddedBlocks() as $embeddedBlock) {
            $tmp = $this->getResultData($embeddedBlock);
            $tplVar[key($tmp)] = reset($tmp);
        }

        return [$block->getBlockName() => $this->_parseTemplate($block, $tplVar)];
    }

    // ======== Protected methods ======== \\
    protected function _setHeaders($result, $contentType = 'text/html', $encoding = null): header
    {
        return parent::_setHeaders($result, $contentType, $encoding);
    }
}
