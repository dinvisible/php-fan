<?php
declare(strict_types=1);

namespace fan\core\service\matcher\item;
/**
 * Description of source
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
 *
 * @property string $request
 * @property string $host
 */
class source extends base
{
    /**
     * Allowed property
     * @var array
     */
    protected array $data = [
        'request' => null,
        'host'    => null,
        'file'    => null,
        'path'    => null,
    ];

    /**
     * Implements PHP magic behavior for this current component.
     *
     * @return string String representation of the matched source request.
     */
    public function __toString(): string {
        return empty($this->data['request']) ?
            (string)$this->data['path'] . '/' . (string)$this->data['file'] :
            (string)$this->data['host'] . (string)$this->data['request'];
    }
}
