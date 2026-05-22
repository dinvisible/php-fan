<?php

declare(strict_types=1);

namespace fan\project\service {
    class database
    {
        public static array $calls = [];

        public static function reset(): void
        {
            self::$calls = [];
        }

        public static function fixAll($dbOper, $makeException = true): void
        {
            self::$calls[] = [$dbOper, $makeException];
        }
    }
}
