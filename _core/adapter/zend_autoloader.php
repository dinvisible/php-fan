<?php

declare(strict_types=1);

namespace fan\core\adapter;

class zend_autoloader
{
    public static function load(string $zendPath): void
    {
        if (class_exists('Zend_Loader_Autoloader', false)) {
            return;
        }

        $loaderPath = rtrim($zendPath, '/\\') . '/Loader/Autoloader.php';
        if (!is_readable($loaderPath)) {
            throw new \RuntimeException('Zend autoloader is not available at "' . $loaderPath . '".');
        }

        require_once $loaderPath;
    }
}
