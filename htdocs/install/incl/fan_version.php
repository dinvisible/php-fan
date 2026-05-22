<?php

declare(strict_types=1);

/**
 * Show fan-version
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
class fan_version extends base
{
    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\
    public function runCheck(): bool
    {
        return $this->_showFanVersion();
    }

    // ======== Private/Protected methods ======== \\
    protected function _showFanVersion(): bool
    {
        $fanVer  = 'Unknown';
        $servApp = FAN_CORE_DIR . '/service/application.php';
        $matches = null;
        if (file_exists($servApp)) {
            $app = file_get_contents($servApp);
            if (preg_match('/^\s*return\s*\'([^\']+)\'\;\s*$/m', $app, $matches)) {
                $fanVer = $matches[1];
            }
        }
        $this->view['fanVer'] = $fanVer;

        if (preg_match('/^(\/.*?)install\//', $_SERVER['REQUEST_URI'], $matches)) {
            $this->view['logViewer'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $matches[1] . '__log_viewer/';
        }

        $this->_parseTemplate('fan_version');
        return true;
    }
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
}
