<?php

declare(strict_types=1);

namespace fan\core\service {
    class tab
    {
    }
}

namespace fan\core\base\meta {
    if (!class_exists(__NAMESPACE__ . '\\delayed', false)) {
        class delayed
        {
            private mixed $value = null;

            /**
             * @param mixed $value Value that should be applied or transformed.
             */
            public function __construct($value)
            {
                $this->value = $value;
            }

            public function getValue(): mixed
            {
                return $this->value;
            }
        }
    }

    if (!class_exists(__NAMESPACE__ . '\\row', false)) {
        class row implements \ArrayAccess, \IteratorAggregate, \Countable
        {
            private array $data = [];

            public function __construct($data = [])
            {
                foreach ($data as $key => $value) {
                    $this->data[$key] = is_array($value) ? new self($value) : $value;
                }
            }

            public function offsetExists(mixed $key): bool
            {
                return array_key_exists($key, $this->data);
            }

            public function offsetGet(mixed $key): mixed
            {
                return array_key_exists($key, $this->data) ? $this->data[$key] : null;
            }

            public function offsetSet(mixed $key, mixed $value): void
            {
                if ($key === null) {
                    $this->data[] = $value;
                } else {
                    $this->data[$key] = is_array($value) ? new self($value) : $value;
                }
            }

            public function offsetUnset(mixed $key): void
            {
                unset($this->data[$key]);
            }

            public function getIterator(): \Traversable
            {
                return new \ArrayIterator($this->data);
            }

            /**
             * @return int Returns the numeric result produced by the operation.
             */
            public function count(): int
            {
                return count($this->data);
            }

            public function mergeData(array $data): static
            {
                foreach ($data as $key => $value) {
                    $this->offsetSet($key, $value);
                }
                return $this;
            }

            public function toArray(): array
            {
                $result = [];
                foreach ($this->data as $key => $value) {
                    $result[$key] = $value instanceof self ? $value->toArray() : $value;
                }
                return $result;
            }
        }
    }
}

namespace fan\project\exception\block {
    class fatal extends \Exception
    {
        private ?object $block = null;

        public function __construct($block, $message = '', $code = 0, ?\Exception $previous = null)
        {
            $this->block = $block;
            parent::__construct($message, $code, $previous);
        }

        public function getBlock(): ?object
        {
            return $this->block;
        }
    }

    class local extends \Exception
    {
        private ?object $block = null;

        public function __construct($block, $message = '', $code = 0, ?\Exception $previous = null)
        {
            $this->block = $block;
            parent::__construct($message, $code, $previous);
        }

        public function getBlock(): ?object
        {
            return $this->block;
        }
    }
}
