<?php

declare(strict_types=1);

namespace fan\core\runtime;

final class error_logger
{
    public function __construct(
        private object $errorLogWriter,
        private object $fileStorage
    ) {
    }

    public function __invoke(string $message, string $logDir): void
    {
        if ($message === '') {
            return;
        }

        if ($this->fileStorage->isDirectory($logDir) && $this->fileStorage->isWritable($logDir)) {
            $logPath = $logDir . '/' . date('Y-m-d') . '_000.log';
            if (!$this->fileStorage->exists($logPath) || $this->fileStorage->isWritable($logPath)) {
                $row = date('H:i:s') . "\t" . addcslashes($message, "\\\t\r\n\0") . "\n";
                $this->errorLogWriter->write($row, 3, $logPath);
                return;
            }
        }

        $this->errorLogWriter->write($message, 0);
    }
}
