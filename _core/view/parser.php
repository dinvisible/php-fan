<?php
declare(strict_types=1);

namespace fan\core\view;
/**
 * Base abstract html type of block
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
 * @abstract
 */
abstract class parser
{
    use \fan\core\di\container_aware_trait;

    /**
     * @var \fan\core\block\base Root block
     */
    protected ?object $rootBlock = null;
    /**
     * @var \fan\core\block\base Main block
     */
    protected ?object $mainBlock = null;

    /**
     * Array - result of parsing process
     * @var array
     */
    protected ?array $result = null;


    public function __construct(\fan\core\block\base $mainBlock)
    {
        $this->mainBlock = $mainBlock;
    }

    // ======== Static methods ======== \\
    static public function getFormat(): string {
        throw new \fan\project\exception\error500('Class "' . get_called_class() . '" can\'t be use for define View-type');
    }

    static public function getRouter(\fan\core\block\base $block): \fan\core\view\router {
        return new \fan\project\view\router\simple($block);
    }

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    public function startParsing(\fan\core\block\base $rootBlock): static
    {
        $this->rootBlock = $rootBlock;
        $this->result = $this->getResultData($this->rootBlock);
        return $this;
    }

    public function getFinalContent(): mixed
    {
        $result = end($this->result);
        $this->_setHeaders($result);
        return $result;
    }

    public function getResultData(\fan\core\block\base $block): array
    {
        return $this->_assembleToArray($block);
    }

    // ======== Protected methods ======== \\
    protected function _assembleToArray(\fan\core\block\base $block): array
    {
        $viewData = $block->getViewData();

        foreach ($block->getEmbeddedBlocks() as $embeddedBlock) {
            $embData = $this->getResultData($embeddedBlock);
            if (!empty($embData)) {
                $viewData[$embeddedBlock->getBlockName()] = $embData;
            }
        }

        return $viewData;
    }

    protected function _mixEmbededData(array $blockData, array $embededData): array
    {
        $mixedData = [];
        foreach ($embededData as $v) {
            $mixedData = array_merge($mixedData, $v);
        }
        return array_merge($mixedData, $blockData);
    }

    protected function _parseTemplate(\fan\core\block\base $block, $tplVar): string
    {
        $cond = $block->getRoleCondition();
        if (!empty($cond)) {
            return '';
        }
        $template = $block->getTemplate();
        if ($template) {
            // If template exists - assign variables and parse template
            $tplParentClass = $block->getMeta('tpl_parent_class');

            $template = $this->containerService('template')->get($template, $tplParentClass, $block);
            foreach ($tplVar as $k => $v) {
                $template->assign($k, $v);
            }
            $tplResult = $template->fetch();
        } else {
            // else - concatenate variables
            $tplResult = '';
            foreach ($tplVar as $v) {
                if (is_scalar($v) || is_object($v) && method_exists($v, '__toString')) {
                    $tplResult .= (string)$v;
                }
            }
        }

        return $tplResult;
    }

    protected function _formatResultData(\fan\core\block\base $block, array $srcData, string $tplResult): array
    {
        return [$block->getBlockName() => $tplResult];
    }

    protected function _setHeaders($result, $contentType = 'text/plain', $encoding = null): \fan\core\service\header
    {
        $header = $this->containerService('header');
        $header->addHeader('length', strlen((string)$result));

        if (!empty($contentType)) {
            if (is_null($encoding)) {
                $encoding = $this->containerService('locale')->getCharacterSet();
            }
            $header->addHeader('contentType', (string)$contentType);
            if (!empty($encoding)) {
                $header->addHeader('encoding', 'charset=' . $encoding);
            }
        }

         // ToDo: Add another header there. For example - cache headers
        return $header;
    }

}
