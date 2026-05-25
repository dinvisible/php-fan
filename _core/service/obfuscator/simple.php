<?php
declare(strict_types=1);

namespace fan\core\service\obfuscator;
/**
 * Simple obfuscator by regexp
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
 * @version of file: 05.02.008 (15.09.2015)
 */
class simple extends base
{

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\

    public function obfuscate(string $text): string
    {
        if ($this->dropComments) {
            $text = preg_replace('/\/\*.+?\*\//s', '', $text) ?? $text;
        }
        if ($this->dropComments || $this->dropEndRow) {
            $text = preg_replace('/^\s*\/\/.*$/m', '', $text) ?? $text;
        }
        if ($this->dropEndRow) {
            $text = preg_replace('/\v+/', ' ', $text) ?? $text;
        }
        if ($this->spacesToOne) {
            $text = preg_replace('/^\h+/m', '', $text) ?? $text;
            $text = preg_replace('/\h+$/m', '', $text) ?? $text;
            if (!$this->dropEndRow) {
                $text = preg_replace('/\v{2,}/', "\n", $text) ?? $text;
            }
            $text = preg_replace('/\h{2,}/', ' ', $text) ?? $text;
        }
        return $text;
    }

    // ======== Private/Protected methods ======== \\

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\


}
