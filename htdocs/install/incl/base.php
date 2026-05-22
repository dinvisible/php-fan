<?php

declare(strict_types=1);

/**
 * Base abstract class for all parts of install
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
abstract class base
{
    protected static array $instances = [];
    /**
     * Global static data for localise teplates
     * @var array
     */
    protected static ?array $locale = null;
    /**
     * View-data for template
     * @var array
     */
    protected array $view = [];

    public function __construct()
    {
        $this->_setLocale();
    }
    // ======== Static methods ======== \\
    public static function run(string $method = 'runCheck'): mixed
    {
        $class = function_exists('get_called_class') ? get_called_class() : 'check_configuration';
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new $class();
        }
        return self::$instances[$class]->$method();
    }
    // ======== Main Interface methods ======== \\
    // ======== Private/Protected methods ======== \\
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    public function _setLocale(bool $forse = false): static
    {
        if (is_null(self::$locale) || $forse) {

            $lng = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            if (!empty($lng)) {
                $weight  = 0;
                $matches = [];
                foreach ($lng as $v) {
                    preg_match('/(\w{2})(?:\-\w{2})?(?:\;q\=([\d\.]+))?/', $v, $matches);
                    $tmp = empty($matches[2]) ? 1 : floatval($matches[2]);
                    if ($tmp > $weight && file_exists(__DIR__ . '/../locale/' . $matches[1] . '.php')) {
                        $locale = $matches[1];
                        $weight = $tmp;
                    }
                }
            }

            if (empty($locale)) {
                $locale = 'en';
            }

            self::$locale = \fan\project\adapter\php_array_file::load(__DIR__ . '/../locale/' . $locale . '.php', []);
        }
        return $this;
    }
    public function _parseTemplate(string $tplName): static
    {
        extract($this->view);

        $content = \fan\project\adapter\php_template_file::render(__DIR__ . '/../tpl/' . $tplName . '.php', $this->view);

        $matches = [];
        preg_match_all('/\{\#[A-Z_]+\}/', $content, $matches);
        foreach ($matches[0] as $v) {
            $key = substr($v, 2, -1);
            if (isset(self::$locale[$key])) {
                $content = str_replace($v, self::$locale[$key], $content);
            }
        }

        echo $content;
        return $this;
    }
}
