<?php
declare(strict_types=1);

namespace fan\core\view\parser;
use fan\core\block\base;
use fan\core\service\header;
use fan\core\view\parser;

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
class debug2 extends parser
{
    /**
     * @var \fan\core\service\debug Root block
     */
    protected ?object $debug = null;

    public function __construct(
        base $mainBlock,
        ?callable $jsonFactory,
        object $debug,
        ?callable $templateFactory = null,
        ?object $header = null,
        ?object $locale = null
    ) {
        parent::__construct($mainBlock, $jsonFactory, $templateFactory, $header, $locale);
        $this->debug = $debug;
    }

    // ======== Static methods ======== \\
    /**
     * @throws \fan\project\exception\error500
     */
    final static public function getFormat(?callable $exceptionFactory = null): string {
        throw static::createUnsupportedParserException(
            'Class "\fan\core\view\parser\debug2" can\'t be use for define View-type',
            $exceptionFactory
        );
    }

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    public function getResultData(base $block): array
    {
        $blockInfo = $this->_getInternalResultData($block, false);
        return [
            $block->getBlockName() => $this->debug->getSecondDebugCode(
                    $blockInfo, method_exists($block, 'getTitle') ?
                    $block->getTitle() :
                    'Debug Info'
                )
            ];
    }

    // ======== Protected methods ======== \\
    public function _getInternalResultData(base $block, $isView): string
    {
        $incl = [];
        foreach ($block->getEmbeddedBlocks() as $embeddedBlock) {
            $incl[] = $this->_getInternalResultData($embeddedBlock, true);
        }

        return $this->debug->getSecondDebugRow($block, $incl, $isView);
    }
    protected function _setHeaders($result, $contentType = 'text/html', $encoding = null): header
    {
        return parent::_setHeaders($result, $contentType, $encoding);
    }
}
