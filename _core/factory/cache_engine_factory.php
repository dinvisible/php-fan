<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\cache\file as core_file_cache_engine;
use fan\core\service\cache\memcache as core_memcache_cache_engine;

final class cache_engine_factory
{
    private \Closure $configuredServiceFactory;
    private \Closure $memcacheKeeperFactory;
    private \Closure $memcacheAvailabilityChecker;

    public function __construct(
        private object $serializerOperations,
        callable $configuredServiceFactory,
        private object $fileStorage,
        ?callable $memcacheKeeperFactory = null,
        ?callable $memcacheAvailabilityChecker = null
    ) {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
        $this->memcacheKeeperFactory = \Closure::fromCallable($memcacheKeeperFactory ?? static fn(): object => new \Memcache());
        $this->memcacheAvailabilityChecker = \Closure::fromCallable($memcacheAvailabilityChecker ?? static fn(): bool => class_exists('\Memcache'));
    }

    public function __invoke(
        string $class,
        object $facade,
        string $type,
        string $key,
        array $config,
        object $errorLogger,
        object $runtime,
        ?object $memcacheState,
        ?callable $configFatalExceptionFactory = null,
        ?callable $arrayValueReader = null
    ): object {
        $baseArguments = [$facade, $type, $key, $config, $errorLogger, $runtime];
        $serializerArguments = [
            $this->serializerOperations->jsonPayloadEncoder(),
            $this->serializerOperations->externalPayloadDecoder(),
            $this->serializerOperations->jsonPayloadChecker(),
        ];

        if ($class === core_file_cache_engine::class) {
            return new core_file_cache_engine(...array_merge($baseArguments, $serializerArguments, [$this->fileStorage]));
        }

        if ($class === core_memcache_cache_engine::class) {
            return new core_memcache_cache_engine(...array_merge(
                $baseArguments,
                [$memcacheState],
                $serializerArguments,
                [
                    $configFatalExceptionFactory,
                    $arrayValueReader ?? static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => \array_val($array, $key, $default),
                    $this->memcacheKeeperFactory,
                    $this->memcacheAvailabilityChecker
                ]
            ));
        }

        $arguments = $baseArguments;
        if (is_a($class, core_memcache_cache_engine::class, true) && $memcacheState !== null) {
            $arguments[] = $memcacheState;
        }
        $arguments = array_merge($arguments, $serializerArguments);
        if (is_a($class, core_file_cache_engine::class, true)) {
            $arguments[] = $this->fileStorage;
        }

        return ($this->configuredServiceFactory)($class, $arguments);
    }

}
