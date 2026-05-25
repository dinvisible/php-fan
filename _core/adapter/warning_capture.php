<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class warning_capture
{
    public function run(callable $operation, ?callable $onWarning = null): mixed
    {
        set_error_handler(static function (int $severity, string $message) use ($onWarning): bool {
            if ($onWarning !== null) {
                $onWarning($message, $severity);
            }

            return true;
        });
        try {
            return $operation();
        } finally {
            restore_error_handler();
        }
    }
}
