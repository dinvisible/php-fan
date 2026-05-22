<?php

declare(strict_types=1);

namespace fan\core\adapter;

class pear_http_session
{
    public static function ensureAvailable(): void
    {
        if (!class_exists('HTTP_Session')) {
            throw new \RuntimeException('PEAR HTTP_Session is not available through Composer autoload.');
        }
    }
}
