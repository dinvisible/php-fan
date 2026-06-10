<?php

declare(strict_types=1);

namespace {
    if (!function_exists('get_class_alt')) {
        /**
         * @param mixed $value Value that should be applied or transformed.
         */
        function get_class_alt(mixed $value): string
        {
            return is_object($value) ? get_class($value) : (string)$value;
        }
    }

    if (!function_exists('array_get_element')) {
        function &array_get_element(&$data, $path, $create = false): mixed
        {
            $null = null;
            if (is_scalar($path)) {
                if (!is_array($data) || !array_key_exists($path, $data)) {
                    return $null;
                }
                return $data[$path];
            }
            $current =& $data;
            foreach ($path as $key) {
                if (!is_array($current)) {
                    return $null;
                }
                if (!array_key_exists($key, $current)) {
                    if ($create) {
                        $current[$key] = [];
                    } else {
                        return $null;
                    }
                }
                $current =& $current[$key];
            }
            return $current;
        }
    }

    if (!function_exists('is_array_alt')) {
        function is_array_alt(mixed $arr): bool
        {
            return is_array($arr) || is_object($arr) && $arr instanceof \ArrayAccess;
        }
    }

    if (!function_exists('array_merge_recursive_alt')) {
        function array_merge_recursive_alt(mixed $arrFirst): array
        {
            if (!is_array_alt($arrFirst)) {
                $arrFirst = is_null($arrFirst) ? [] : [$arrFirst];
            }
            foreach (array_slice(func_get_args(), 1) as $arrNext) {
                if (is_null($arrNext)) {
                    continue;
                }
                $arrNext = is_array_alt($arrNext) ? $arrNext : [$arrNext];
                foreach ($arrNext as $key => $value) {
                    $arrFirst[$key] = isset($arrFirst[$key]) && (is_array_alt($arrFirst[$key]) || is_array_alt($value))
                        ? array_merge_recursive_alt($arrFirst[$key], $value)
                        : $value;
                }
            }
            return $arrFirst;
        }
    }

    if (!function_exists('adduceToArray')) {
        function adduceToArray(mixed $src): array
        {
            if (!empty($src)) {
                return match (gettype($src)) {
                    'object' => method_exists($src, 'toArray') ? $src->toArray() : (array)$src,
                    'array' => $src,
                    'integer', 'double', 'string' => [$src],
                    default => [],
                };
            }
            return [];
        }
    }
}

namespace fan\project\service {
    class error
    {
        public static ?object $instance = null;
        public array $messages = [];
        public array $exceptionMessages = [];
        public array $databaseErrors = [];

        public static function instance(): self
        {
            if (empty(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public static function reset(): void
        {
            self::$instance = new self();
            if (class_exists('\FanTest\core\block\FakeServiceRegistry', false)) {
                \FanTest\core\block\FakeServiceRegistry::set('error', self::$instance);
            }
            if (class_exists('\FanTest\core\block\FakeServiceContainer', false)) {
                \FanTest\core\block\FakeServiceRegistry::installContainer(new \FanTest\core\block\FakeServiceContainer());
            } elseif (class_exists('\fan\core\di\container', true)) {
                $container = new \fan\core\di\container();
                $container->set('error', self::$instance);
            }
        }

        public function logErrorMessage($message, $title = '', $note = '', $fixPosition = false): void
        {
            $this->messages[] = [$message, $title, $note, $fixPosition];
        }

        public function logExceptionMessage(string $message, string $header = '', string $note = ''): void
        {
            $this->exceptionMessages[] = [$message, $header, $note];
        }

        public function logDatabaseError($connectionName, string $operation, $errorMessage, int|float $errorNum, $parsedSql): void
        {
            $this->databaseErrors[] = [$connectionName, $operation, $errorMessage, $errorNum, $parsedSql];
        }
    }
}
