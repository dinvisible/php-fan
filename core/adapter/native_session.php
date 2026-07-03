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

    public function setCookieParams(
        int $lifetime,
        string $path,
        ?string $domain = null,
        bool $secure = true,
        bool $httpOnly = true,
        string $sameSite = 'Lax'
    ): bool
    {
        return session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => $path,
            'domain' => $domain ?? '',
            'secure' => $secure,
            'httponly' => $httpOnly,
            'samesite' => $sameSite,
        ]);
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
