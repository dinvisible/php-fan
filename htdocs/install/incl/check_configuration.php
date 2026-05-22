<?php

declare(strict_types=1);

/**
 * Check configuration of PHP
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
class check_configuration extends base
{
    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\
    public function runCheck(): bool
    {
        if (!$this->_checkPhpVersion()) {
            return false;
        }

        return $this->_checkPhpModules();
    }

    // ======== Private/Protected methods ======== \\
    protected function _checkPhpVersion(): bool
    {
        $verValue = phpversion();
        //$verType  = version_compare($verValue, '5.4') > 0 ? 1 : (version_compare($verValue, '5.3') < 0 ? -1 : 0);
        $verType  = version_compare($verValue, '5.3') >= 0 ? 1 : -1;
        $this->view['verType']  = $verType;
        $this->view['verValue'] = $verValue;
        $this->_parseTemplate('php_version');
        return $verType >= 0;
    }
    protected function _checkPhpModules(): bool
    {
        $required    = ['SPL', 'Reflection', 'pcre', 'standard', 'json', 'session', 'iconv', 'filter', 'date'];
        $recommended = ['memcache', 'mbstring', 'mysql', 'mysqli', 'libxml', 'dom', 'SimpleXML', 'xml', 'xmlreader', 'xmlwriter', 'gd', 'exif', 'curl', 'soap'];

        $modules = get_loaded_extensions();

        $useRequired    = [];
        $allRequired    = true;
        $useRecommended = [];
        $allRecommended = true;

        foreach ($required as $v) {
            if (in_array($v, $modules)) {
                $useRequired[$v] = 'correct';
            } else {
                $useRequired[$v] = 'incorrect';
                $allRequired = false;
            }
        }
        foreach ($recommended as $v) {
            if (in_array($v, $modules)) {
                $useRecommended[$v] = 'correct';
            } else {
                $useRecommended[$v] = 'need';
                $allRecommended = false;
            }
        }

        $this->view['useRequired']    = $useRequired;
        $this->view['allRequired']    = $allRequired;
        $this->view['useRecommended'] = $useRecommended;
        $this->view['allRecommended'] = $allRecommended;
        $this->_parseTemplate('php_modules');

        return $allRequired;
    }
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
}
