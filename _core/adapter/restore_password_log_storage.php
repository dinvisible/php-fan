<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class restore_password_log_storage
{
    /**
     * @return list<string>
     */
    public function logFiles(string $directory): array
    {
        $files = scandir($directory);
        if ($files === false) {
            return [];
        }

        $logs = array_values(array_filter(
            $files,
            static fn(mixed $file): bool => is_string($file) && preg_match('/.+\.log$/i', $file) === 1
        ));
        rsort($logs);

        return $logs;
    }

    /**
     * @return list<string>|false
     */
    public function readLines(string $path): array|false
    {
        return file($path);
    }
}
