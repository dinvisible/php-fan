<?php
declare(strict_types=1);

namespace fan\core\service\log;
/**
 * Parser of log bootstrap-file
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
class parser_bootstrap extends parser_base
{
    /**
     * Key of dir by bootstrap
     * @var string
     */
    protected ?string $logDirKey = 'bootstrap_log';

    /**
     * Key of dir by Apache
     * @var string
     */
    protected string $globalLogDirKey = 'apache_log';

    /**
     * Type of record is available
     * @var boolean
     */
    protected bool $isType = false;

    /**
     * Data is serialized
     * @var boolean
     */
    protected bool $isSerialized = false;

    public function setFilePath(string $variety, string $file): void
    {
        $file = (string)$file;
        $srcLogFile  = \bootstrap::getGlobalPath($this->globalLogDirKey) . '/error_' . substr($file, 0, 10) . '.log';
        $destLogFile = \bootstrap::getGlobalPath($this->logDirKey) . '/' . $file . '.log';
        if (is_file($srcLogFile) && (!is_file($destLogFile) || is_writable($destLogFile))) {
            if (preg_match_all("/\[\S+\s+(\d{2}\:\d{2}\:\d{2})\](.*?)(?=\[\S+\s+(\d{2}\:\d{2}\:\d{2})\]|$)/s", (string)file_get_contents($srcLogFile), $matches)) {
                foreach ($matches[1] as $k => $v) {
                    $row = $v . "\t" . addcslashes((string)preg_replace("/\s*(\n*\r+|\r*\n+)+\s*/s", "\n", trim($matches[2][$k])), "\\\t\r\n\0") . "\n";
                    error_log($row, 3, $destLogFile);
                }
                unlink($srcLogFile);
            }
        }
        parent::setFilePath($variety, $file);
    }

}
