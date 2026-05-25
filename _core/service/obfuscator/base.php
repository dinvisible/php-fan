<?php
declare(strict_types=1);

namespace fan\core\service\obfuscator;
use fan\core\service\obfuscator;

/**
 * Description of obfuscator-engine base
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
abstract class base
{
    /**
     * Facade of service
     * @var \fan\core\service\obfuscator
     */
    protected ?object $facade = null;

    /**
     * Drop comments like "/ * ... * /" and "// ..."
     * @var boolean
     */
    protected bool $dropComments = true;
    /**
     * Drop "end of row" like "\n" and "\r". If this option is set comments like "// ..." will be dropped anyway
     * @var boolean
     */
    protected bool $dropEndRow = true;
    /**
     * Replace several spaces to one
     * @var boolean
     */
    protected bool $spacesToOne = true;

    public function __construct()
    {
    }

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\

    public function setFacade(obfuscator $facade): static
    {
        if (empty($this->facade)) {
            $this->facade = $facade;

            $config = $facade->getConfig('option', []);
            $keys = [
                'dropComments' => 'DROP_COMMENTS',
                'dropEndRow'   => 'DROP_END_ROW',
                'spacesToOne'  => 'SPACES_TO_ONE',
            ];
            foreach ($keys as $k => $v) {
                $this->$k = isset($config[$v]) ? (bool)$config[$v] : true;
            }
        }
        return $this;
    }

    abstract public function obfuscate(string $text): string;

    // ======== Private/Protected methods ======== \\

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\

}
