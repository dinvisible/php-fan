<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class image_metadata_reader
{
    public function __construct(private object $warningCapture)
    {
    }

    public function size(string $path, ?callable $onWarning = null): array|false
    {
        return $this->warningCapture->run(
            static fn(): array|false => getimagesize($path),
            $onWarning
        );
    }
}
