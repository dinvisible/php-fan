<?php

declare(strict_types=1);

if (!class_exists('bootstrap', false)) {
    class bootstrap
    {
        public static array $log = [];
        private static ?object $initializer = null;

        public static function getInitializer(): object
        {
            if (self::$initializer === null) {
                self::$initializer = new class {
                    public array $serviceParams = [];

                    public function setServiceParam($class): static
                    {
                        $this->serviceParams[] = $class;
                        return $this;
                    }
                };
            }
            return self::$initializer;
        }

        public static function logError($message): void
        {
            self::$log[] = $message;
        }
    }
}
