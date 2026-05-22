<?php

declare(strict_types=1);

if (!function_exists('service')) {
    function service($name, $args = []): mixed
    {
        return \FanTest\_core\block\FakeServiceRegistry::get($name, $args);
    }
}

if (!function_exists('role')) {
    function role($condition): mixed
    {
        return \FanTest\_core\block\FakeRoleRegistry::check($condition);
    }
}

if (!function_exists('get_class_name')) {
    function get_class_name($class): string
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        $pos = strrpos($class, '\\');
        return $pos === false ? $class : substr($class, $pos + 1);
    }
}

if (!function_exists('get_class_alt')) {
    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    function get_class_alt(mixed $value): string
    {
        return is_object($value) ? get_class($value) : (string)$value;
    }
}

if (!class_exists('bootstrap', false)) {
    class bootstrap
    {
        public static array $log = [];
        private static ?object $initializer = null;

        /**
         * Transforms path between supported representations.
         */
        public static function parsePath($path): mixed
        {
            return $path;
        }

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
