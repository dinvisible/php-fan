<?php

declare(strict_types=1);

/**
 * Run install-process
 *
 * This file is part PHP-FAN (php-framework from Alexandr Nosov)
 * Copyright (C) 2005-2007 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *     http://www.opensource.org/licenses/lgpl-license.php
 *
 * Do not remove this comment if you want to use script!
 * �� �������� ������ �����������, ���� �� ������ ������������ ������!
 *
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.02.007 (31.08.2015)
 */
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../../vendor/autoload.php';

echo \fan\project\adapter\php_template_file::render(__DIR__ . '/incl/header.php');
if (!\fan\project\adapter\project_tool_loader::load('base', __DIR__ . '/incl/base.php')) {
    throw new \RuntimeException('Installer base class is not available.');
}

$classes = [
    'check_configuration',
    'check_directories',
    'fan_version',
];
foreach ($classes as $v) {
    if (!\fan\project\adapter\project_tool_loader::load($v, __DIR__ . '/incl/' . $v . '.php')) {
        throw new \RuntimeException('Installer step class "' . $v . '" is not available.');
    }
    if (!call_user_func([$v, 'run'])) {
        break;
    }
}

echo \fan\project\adapter\php_template_file::render(__DIR__ . '/incl/footer.php');
