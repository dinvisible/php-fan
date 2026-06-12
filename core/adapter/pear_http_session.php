<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class pear_http_session
{
    private \Closure $staticCall;

    public function __construct(?callable $staticCall = null)
    {
        $this->staticCall = \Closure::fromCallable(
            $staticCall
                ?? static function (string $method, mixed ...$arguments): mixed {
                    $className = 'HTTP_Session';
                    if (!class_exists($className)) {
                        throw new \RuntimeException('PEAR HTTP_Session class is not available.');
                    }

                    return $className::$method(...$arguments);
                }
        );
    }

    public function setContainer(string $type, array $param): void
    {
        $this->call('setContainer', $type, $param);
    }

    public function useCookies(bool $useCookies): void
    {
        $this->call('useCookies', $useCookies);
    }

    public function start(string $sessionName, mixed $sid = null): void
    {
        $this->call('start', $sessionName, $sid);
    }

    public function id(): mixed
    {
        return $this->call('id');
    }

    public function get(string $key, ?string $defaultValue = null): mixed
    {
        return $this->call('get', $key, $defaultValue);
    }

    public function set(string $key, mixed $value): void
    {
        $this->call('set', $key, $value);
    }

    public function clear(): void
    {
        $this->call('clear');
    }

    public function destroy(): void
    {
        $this->call('destroy');
    }

    private function call(string $method, mixed ...$arguments): mixed
    {
        return ($this->staticCall)($method, ...$arguments);
    }
}
