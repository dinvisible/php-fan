<?php
declare(strict_types=1);

namespace fan\core\view\router;
use fan\core\view\router\simple;

/**
 * View router of JSON-block
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
class json extends simple
{
    /**
     * Use Base64
     * @var boolean
     */
    protected bool $useBase64 = false;
    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\
    public function useBase64(bool $useBase64 = true): static
    {
        $this->useBase64 = $useBase64;
        return $this;
    }
    public function isUseBase64(): bool
    {
        return $this->useBase64;
    }

 }
