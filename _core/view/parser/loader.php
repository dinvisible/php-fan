<?php
declare(strict_types=1);

namespace fan\core\view\parser;
/**
 * View parser Loader-type
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
class loader extends \fan\core\view\parser
{
    // ======== Static methods ======== \\
    final static public function getFormat(): string {
        return 'loader';
    }

    static public function getRouter(\fan\core\block\base $block): \fan\core\view\router\loader {
        return new \fan\project\view\router\loader($block);
    }

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    public function getFinalContent(): mixed
    {
        if (method_exists($this->mainBlock, 'getDataLoader')) {
            $loader = $this->mainBlock->getDataLoader();
        } else {
            $loader = new \fan\project\adapter\data_loader();
        }
        $loader->setJson($this->result['json'], true);
        $loader->setText($this->result['text'], true);
        $loader->setHtml($this->result['html'], true);
        $result = $loader->send(false);

        $this->_setHeaders($result, $loader->getContentType([], false, false), false);
        return $result;
    }

    public function getResultData(\fan\core\block\base $block): array
    {
        $viewRouter = $block->getView();
        $tplResult  = $this->_getTplResult($block);
        return [
            'json' => $viewRouter->getJson(),
            'html' => end($tplResult),
            'text' => $viewRouter->getText(),
        ];
    }

    // ======== Protected methods ======== \\
    public function _getTplResult(\fan\core\block\base $block): array
    {
        $tplVar = $block->getView()->html->toArray();

        foreach ($block->getEmbeddedBlocks() as $embeddedBlock) {
            $tmp = $this->_getTplResult($embeddedBlock);
            $tplVar[key($tmp)] = reset($tmp);
        }

        return [$block->getBlockName() => $this->_parseTemplate($block, $tplVar)];
    }
}
