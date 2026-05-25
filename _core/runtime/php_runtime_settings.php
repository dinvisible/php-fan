<?php

declare(strict_types=1);

namespace fan\core\runtime;

final class php_runtime_settings
{
    public function version(): string
    {
        return PHP_VERSION;
    }

    public function sapiName(): string
    {
        return php_sapi_name();
    }

    public function terminate(string $message): void
    {
        exit($message);
    }

    public function get(string $name): string|false
    {
        return ini_get($name);
    }

    public function set(string $name, string $value): string|false
    {
        return ini_set($name, $value);
    }
}
