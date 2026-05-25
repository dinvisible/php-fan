<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class native_session
{
    public function start(): bool
    {
        return session_start();
    }

    public function id(?string $id = null): string|false
    {
        return $id === null ? session_id() : session_id($id);
    }

    public function name(?string $name = null): string|false
    {
        return $name === null ? session_name() : session_name($name);
    }

    public function setCookieParams(int $lifetime, string $path, ?string $domain = null): bool
    {
        return $domain === null
            ? session_set_cookie_params($lifetime, $path)
            : session_set_cookie_params($lifetime, $path, $domain);
    }

    public function cacheLimiter(string $value): string|false
    {
        return session_cache_limiter($value);
    }

    public function status(): int
    {
        return session_status();
    }

    public function destroy(): bool
    {
        return session_destroy();
    }
}
