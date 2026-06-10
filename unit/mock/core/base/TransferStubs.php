<?php

declare(strict_types=1);

namespace {
    require_once __DIR__ . '/../../../../core/di/container_interface.php';
    require_once __DIR__ . '/../../../../core/di/container.php';
}

namespace FanTest\core\base {
    class DatabaseConnectionsStub
    {
        public static array $calls = [];

        public static function reset(): void
        {
            self::$calls = [];
        }

        public function fixAll($dbOper, $makeException = true): void
        {
            self::$calls[] = [$dbOper, $makeException];
        }
    }

    class TransferServiceContainer implements \fan\core\di\container_interface
    {
        public function has(string $id): bool
        {
            return $id === 'database_connections';
        }

        public function get(string $id, mixed ...$arguments): mixed
        {
            return new DatabaseConnectionsStub();
        }
    }
}
