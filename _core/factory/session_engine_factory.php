<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\session\inbuilt as core_inbuilt_session_engine;
use fan\core\service\session\pear as core_pear_session_engine;

final class session_engine_factory
{
    private \Closure $configuredServiceFactory;

    public function __construct(
        callable $configuredServiceFactory,
        private object $nativeSession,
        private ?object $pearHttpSession = null
    )
    {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
    }

    public function __invoke(
        string $class,
        ?string $sid,
        object $config,
        ?object $databaseConfig,
        ?object $requestInput,
        mixed $errorFactory,
        ?object $requestService,
        ?object $pearSessionSupportLoader
    ): object {
        if ($class === core_pear_session_engine::class) {
            return new core_pear_session_engine($config->toArray(), $databaseConfig, $requestService, $pearSessionSupportLoader, $this->pearHttpSession());
        }
        if ($class === core_inbuilt_session_engine::class) {
            return new core_inbuilt_session_engine($sid, $requestInput, $errorFactory, $this->nativeSession);
        }

        if (is_a($class, core_pear_session_engine::class, true)) {
            return ($this->configuredServiceFactory)($class, [$config->toArray(), $databaseConfig, $requestService, $pearSessionSupportLoader]);
        }

        if (is_a($class, core_inbuilt_session_engine::class, true)) {
            return ($this->configuredServiceFactory)($class, [$sid, $requestInput, $errorFactory, $this->nativeSession]);
        }

        return ($this->configuredServiceFactory)($class, [$sid, $requestInput, $errorFactory]);
    }

    private function pearHttpSession(): object
    {
        if ($this->pearHttpSession === null) {
            throw new \RuntimeException('PEAR HTTP session adapter is not configured for session engine factory.');
        }

        return $this->pearHttpSession;
    }

}
