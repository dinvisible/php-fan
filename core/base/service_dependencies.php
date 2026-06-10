<?php

declare(strict_types=1);

namespace fan\core\base;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;


final class service_dependencies
{
    /**
     * @var callable|null
     */
    private $serviceCacheFactory;
    /**
     * @var callable|null
     */
    private $serviceEngineFactory;
    /**
     * @var callable|null
     */
    private $serviceExceptionFactory;
    /**
     * @var callable
     */
    private $classNameResolver;
    /**
     * @var callable
     */
    private $arrayValueReader;

    public function __construct(
        private ?object $serviceBootstrapRuntime = null,
        private ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null,
        private ?service_listener_state $serviceListenerState = null,
        private ?service_single_state $serviceSingleState = null,
        ?callable $serviceEngineFactory = null,
        ?callable $serviceExceptionFactory = null,
        ?callable $classNameResolver = null,
        ?callable $arrayValueReader = null
    ) {
        $this->serviceCacheFactory = $serviceCacheFactory;
        $this->serviceEngineFactory = $serviceEngineFactory;
        $this->serviceExceptionFactory = $serviceExceptionFactory;
        $this->classNameResolver = $classNameResolver ?? self::defaultClassNameResolver();
        $this->arrayValueReader = $arrayValueReader ?? self::defaultArrayValueReader();
    }

    public static function fromLegacy(
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null,
        ?service_listener_state $serviceListenerState = null,
        ?service_single_state $serviceSingleState = null,
        ?callable $serviceEngineFactory = null,
        ?callable $serviceExceptionFactory = null,
        ?callable $classNameResolver = null,
        ?callable $arrayValueReader = null
    ): self {
        if ($serviceEngineFactory === null && $serviceBootstrapRuntime !== null && method_exists($serviceBootstrapRuntime, 'serviceEngineFactory')) {
            $serviceEngineFactory = $serviceBootstrapRuntime->serviceEngineFactory();
        }
        if ($serviceExceptionFactory === null && $serviceBootstrapRuntime !== null && method_exists($serviceBootstrapRuntime, 'serviceExceptionFactory')) {
            $serviceExceptionFactory = $serviceBootstrapRuntime->serviceExceptionFactory();
        }
        if ($classNameResolver === null && $serviceBootstrapRuntime !== null && method_exists($serviceBootstrapRuntime, 'classNameResolver')) {
            $classNameResolver = $serviceBootstrapRuntime->classNameResolver();
        }
        if ($arrayValueReader === null && $serviceBootstrapRuntime !== null && method_exists($serviceBootstrapRuntime, 'arrayValueReader')) {
            $arrayValueReader = $serviceBootstrapRuntime->arrayValueReader();
        }
        if ($serviceListenerState === null && $serviceBootstrapRuntime !== null && method_exists($serviceBootstrapRuntime, 'serviceListenerState')) {
            $serviceListenerState = $serviceBootstrapRuntime->serviceListenerState();
        }
        if ($serviceSingleState === null && $serviceBootstrapRuntime !== null && method_exists($serviceBootstrapRuntime, 'serviceSingleState')) {
            $serviceSingleState = $serviceBootstrapRuntime->serviceSingleState();
        }

        return new self(
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $serviceListenerState,
            $serviceSingleState,
            $serviceEngineFactory,
            $serviceExceptionFactory,
            $classNameResolver,
            $arrayValueReader
        );
    }

    public function serviceBootstrapRuntime(): ?object
    {
        return $this->serviceBootstrapRuntime;
    }

    public function serviceConfigurator(): ?object
    {
        return $this->serviceConfigurator;
    }

    public function serviceCacheFactory(): ?callable
    {
        return $this->serviceCacheFactory;
    }

    public function serviceListenerState(): ?service_listener_state
    {
        return $this->serviceListenerState;
    }

    public function serviceSingleState(): ?service_single_state
    {
        return $this->serviceSingleState;
    }

    public function serviceEngineFactory(): ?callable
    {
        return $this->serviceEngineFactory;
    }

    public function serviceExceptionFactory(): ?callable
    {
        return $this->serviceExceptionFactory;
    }

    public function classNameResolver(): callable
    {
        return $this->classNameResolver;
    }

    public function arrayValueReader(): callable
    {
        return $this->arrayValueReader;
    }

    private static function defaultClassNameResolver(): callable
    {
        return static function (string|object $object): string {
            if (is_object($object)) {
                $object = get_class($object);
            }
            $parts = explode('\\', $object);

            return (string)end($parts);
        };
    }

    private static function defaultArrayValueReader(): callable
    {
        $reader = null;
        $reader = static function (array|\ArrayAccess $array, mixed $key, mixed $default = null) use (&$reader): mixed {
            if ($key === null) {
                return $default;
            }
            if (is_array($key)) {
                if ($key === []) {
                    return $default;
                }
                $firstKey = array_shift($key);
                if ($key !== []) {
                    return isset($array[$firstKey]) && (is_array($array[$firstKey]) || $array[$firstKey] instanceof \ArrayAccess)
                        ? $reader($array[$firstKey], $key, $default)
                        : $default;
                }
                $key = $firstKey;
            }

            return $array[$key] ?? $default;
        };

        return $reader;
    }
}
