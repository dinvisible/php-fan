<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class header_writer
{
    public function sent(?string &$file = null, ?int &$line = null): bool
    {
        return headers_sent($file, $line);
    }

    public function send(string $header, bool $replace = true, int $responseCode = 0): void
    {
        header($header, $replace, $responseCode);
    }
}
