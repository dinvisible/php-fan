<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class request_input_native_environment
{
    public function globalArray(string $name): array
    {
        $value = $GLOBALS[$name] ?? [];

        return is_array($value) ? $value : [];
    }

    public function globalValue(string $name, mixed $default = null): mixed
    {
        return $GLOBALS[$name] ?? $default;
    }

    public function unsetGlobalValue(string $name, mixed $key): void
    {
        if (isset($GLOBALS[$name]) && is_array($GLOBALS[$name])) {
            unset($GLOBALS[$name][$key]);
        }
    }

    public function serverValue(string $key, mixed $default = null): mixed
    {
        return $_SERVER[$key] ?? $default;
    }

    public function &sessionRoot(): array
    {
        if (!isset($GLOBALS['_SESSION']) || !is_array($GLOBALS['_SESSION'])) {
            $GLOBALS['_SESSION'] = [];
        }

        return $GLOBALS['_SESSION'];
    }

    public function apacheHeaders(): ?array
    {
        return function_exists('apache_request_headers') ? apache_request_headers() : null;
    }

    public function rawPost(): string
    {
        return (string)file_get_contents('php://input');
    }
}
