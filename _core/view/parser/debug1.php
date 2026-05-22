<?php
declare(strict_types=1);

namespace fan\core\view\parser;
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
 * @version of file: 05.02.001 (10.03.2014)
 */
class debug1 extends html
{
    /**
     * @var \fan\core\service\debug Root block
     */
    protected ?object $debug = null;

    public function __construct(\fan\core\block\base $mainBlock)
    {
        parent::__construct($mainBlock);
        $this->debug = \fan\project\service\debug::instance();
    }

    // ======== Static methods ======== \\
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    public function getResultData(\fan\core\block\base $rootBlock): array
    {
        $this->debug->setExtFiles($rootBlock, true);

        $tplVar = $rootBlock->getViewData();

        $isWrap = false;
        foreach ($rootBlock->getEmbeddedBlocks() as $embeddedBlock) {
            $tmp = $this->_getInternalResultData($embeddedBlock);
            if ($isWrap) {
                $tplVar[key($tmp)] = reset($tmp);
            } else {
                $tplVar[key($tmp)] = $this->debug->wrapHtmlCode(reset($tmp), $rootBlock);
                $isWrap = true;
            }
        }

        return [$rootBlock->getBlockName() => $this->_parseTemplate($rootBlock, $tplVar)];
    }

    // ======== Protected methods ======== \\
    public function _getInternalResultData(\fan\core\block\base $block): array
    {
        $tplVar = $block->getViewData();

        foreach ($block->getEmbeddedBlocks() as $embeddedBlock) {
            $tmp = $this->_getInternalResultData($embeddedBlock);
            $tplVar[key($tmp)] = reset($tmp);
        }

        return [$block->getBlockName() => $this->debug->wrapHtmlCode($this->_parseTemplate($block, $tplVar), $block)];
    }
}
