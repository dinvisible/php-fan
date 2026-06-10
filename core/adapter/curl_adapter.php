<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class curl_adapter
{
    public function init(string $url): object
    {
        $handle = curl_init($url);
        if (!is_object($handle)) {
            throw new \RuntimeException('Unable to initialize cURL handle.');
        }

        return $handle;
    }

    public function setOption(object $handle, int $key, mixed $value): bool
    {
        return curl_setopt($handle, $key, $value);
    }

    public function close(object $handle): void
    {
        if (PHP_VERSION_ID < 80000) {
            curl_close($handle);
        }
    }

    public function getInfo(object $handle, int|float|null $option = null): mixed
    {
        return $option === null ? curl_getinfo($handle) : curl_getinfo($handle, (int)$option);
    }

    public function error(object $handle): string
    {
        return curl_error($handle);
    }

    public function exec(object $handle): string|bool
    {
        return curl_exec($handle);
    }
}
