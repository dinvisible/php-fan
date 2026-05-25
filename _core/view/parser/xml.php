<?php
declare(strict_types=1);

namespace fan\core\view\parser;
use fan\core\view\parser;

/**
 * View parser XML-type
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
class xml extends parser
{
    // ======== Static methods ======== \\
    final static public function getFormat(?callable $exceptionFactory = null): string {
        return 'xml';
    }

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    public function getFinalContent(): string|false
    {
        $dom = new \DOMDocument('1.0', 'iso-8859-1');
        $eelement = new \DOMElement($this->rootBlock->getBlockName());
        $dom->appendChild($eelement);
        $this->_makeDomElements($eelement, $this->result);
        $result = $dom->saveXML();
        $this->_setHeaders($result, 'text/xml', '');
        return $result;
    }

    // ======== Protected methods ======== \\
    protected function _makeDomElements(\DOMNode $parent, $data): static
    {
        foreach ($data as $k => $v) {
            if (is_numeric($k)) { // It is mend. //ToDo: Do numeric keys as several elements with the same name
                continue;
            }
            $eelement = new \DOMElement((string)$k);
            $parent->appendChild($eelement);
            if (is_scalar($v)) {
                $eelement->appendChild(new \DOMText((string)$v));
            } elseif (is_array($v)) {
                $this->_makeDomElements($eelement, $v);
            }
        }
        return $this;
    }

}
