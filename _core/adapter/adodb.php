<?php

declare(strict_types=1);

namespace fan\core\adapter;

class adodb
{
    public static function defineErrorHandler(): void
    {
        if (!defined('ADODB_ERROR_HANDLER')) {
            define('ADODB_ERROR_HANDLER', 'adodb_error_handler');
        }
    }

    public static function newConnection(string $driver): mixed
    {
        if (!function_exists('ADONewConnection')) {
            throw new \RuntimeException('ADOdb is not available. Install adodb/adodb-php with Composer.');
        }

        return ADONewConnection($driver);
    }

    public static function ensureSessionSupport(): void
    {
        if (class_exists('ADODB_Session', false)) {
            return;
        }

        $installPath = self::getInstallPath();
        $sessionPath = $installPath === null ? null : $installPath . '/session/adodb-session2.php';
        if (is_string($sessionPath) && is_readable($sessionPath)) {
            require_once $sessionPath;
        }

        if (!class_exists('ADODB_Session', false)) {
            throw new \RuntimeException('ADOdb session support is not available through Composer package adodb/adodb-php.');
        }
    }

    private static function getInstallPath(): ?string
    {
        if (class_exists(\Composer\InstalledVersions::class)) {
            $path = \Composer\InstalledVersions::getInstallPath('adodb/adodb-php');
            return is_string($path) ? $path : null;
        }

        $path = dirname(__DIR__, 2) . '/vendor/adodb/adodb-php';
        return is_dir($path) ? $path : null;
    }
}
