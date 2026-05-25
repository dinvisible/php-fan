<?php

declare(strict_types=1);

namespace fan\core\adapter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

final class error_log_writer
{
    private ?LoggerInterface $logger = null;

    private array $config;

    public function __construct(array|object|null $config = null, ?object $runtime = null, ?LoggerInterface $logger = null)
    {
        $this->config = $this->normalizeConfig($config);
        $this->logger = $logger ?? $this->createLogger($this->config, $runtime);
    }

    public function write(string $message, int $messageType = 0, ?string $destination = null): bool
    {
        if ($this->logger !== null && $messageType === 0 && $destination === null) {
            try {
                $this->logger->log($this->level($this->config['LEVEL'] ?? Level::Error), $message);

                return true;
            } catch (\Throwable) {
                return error_log($message, 0);
            }
        }

        if ($destination === null) {
            return error_log($message, $messageType);
        }

        return error_log($message, $messageType, $destination);
    }

    public function logError(string $message): void
    {
        $this->write($message, 0);
    }

    private function createLogger(array $config, ?object $runtime): ?LoggerInterface
    {
        if (!$this->enabled($config['ENABLED'] ?? false)) {
            return null;
        }

        $logger = new Logger((string)($config['CHANNEL'] ?? 'php-fan'));
        $handler = strtolower((string)($config['HANDLER'] ?? 'stream')) === 'error_log'
            ? new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $this->level($config['LEVEL'] ?? Level::Error), $this->enabled($config['BUBBLE'] ?? true))
            : new StreamHandler(
                $this->path((string)($config['PATH'] ?? ''), $runtime),
                $this->level($config['LEVEL'] ?? Level::Error),
                $this->enabled($config['BUBBLE'] ?? true),
                $this->filePermission($config['FILE_PERMISSION'] ?? null)
            );

        if (!empty($config['FORMAT'])) {
            $handler->setFormatter(new LineFormatter((string)$config['FORMAT'], null, true, true));
        }

        $logger->pushHandler($handler);

        return $logger;
    }

    private function normalizeConfig(array|object|null $config): array
    {
        if (is_object($config) && method_exists($config, 'toArray')) {
            $config = $config->toArray();
        }

        return is_array($config) ? $config : [];
    }

    private function path(string $path, ?object $runtime): string
    {
        if ($path === '') {
            throw new \RuntimeException('Monolog stream path is not configured.');
        }
        if ($runtime !== null && method_exists($runtime, 'parsePath')) {
            $path = (string)$runtime->parsePath($path);
        }

        $dir = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException('Cannot create Monolog log directory "' . $dir . '".');
        }

        return $path;
    }

    private function level(mixed $level): Level
    {
        return Logger::toMonologLevel($level);
    }

    private function filePermission(mixed $permission): ?int
    {
        if ($permission === null || $permission === '') {
            return null;
        }

        return is_int($permission) ? $permission : intval((string)$permission, 8);
    }

    private function enabled(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string)$value), ['1', 'true', 'yes', 'on'], true);
    }
}
