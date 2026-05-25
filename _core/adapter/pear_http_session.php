<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class pear_http_session
{
    public function setContainer(string $type, array $param): void
    {
        \HTTP_Session::setContainer($type, $param);
    }

    public function useCookies(bool $useCookies): void
    {
        \HTTP_Session::useCookies($useCookies);
    }

    public function start(string $sessionName, mixed $sid = null): void
    {
        \HTTP_Session::start($sessionName, $sid);
    }

    public function id(): mixed
    {
        return \HTTP_Session::id();
    }

    public function get(string $key, ?string $defaultValue = null): mixed
    {
        return \HTTP_Session::get($key, $defaultValue);
    }

    public function set(string $key, mixed $value): void
    {
        \HTTP_Session::set($key, $value);
    }

    public function clear(): void
    {
        \HTTP_Session::clear();
    }

    public function destroy(): void
    {
        \HTTP_Session::destroy();
    }
}
