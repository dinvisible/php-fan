<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\error as core_error_service;

final class error_service_factory
{
    private \Closure $configuredServiceFactory;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
    }

    public function __invoke(
        string $className,
        bool $allowIni,
        object $input,
        object $runtime,
        ?callable $logFactory,
        ?callable $emailFactory,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory,
        callable $phpArrayFileLoader,
        object $errorLogWriter,
        object $fileStorage
    ): mixed {
        $existingService = $this->existingSingleton($className, $serviceBootstrapRuntime);
        if ($existingService !== null) {
            return $existingService;
        }

        $arguments = [
            $allowIni,
            $input,
            $runtime,
            $logFactory,
            $emailFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $phpArrayFileLoader,
            $errorLogWriter,
            $fileStorage
        ];

        if ($className === core_error_service::class) {
            return new core_error_service(...$arguments);
        }

        return ($this->configuredServiceFactory)($className, $arguments);
    }

    private function existingSingleton(string $className, object $serviceBootstrapRuntime): ?object
    {
        if (!method_exists($serviceBootstrapRuntime, 'serviceSingleState')) {
            return null;
        }

        $state = $serviceBootstrapRuntime->serviceSingleState();
        if (!method_exists($state, 'getInstance')) {
            return null;
        }

        return $state->getInstance(core_error_service::checkName(ltrim($className, '\\')));
    }

}
