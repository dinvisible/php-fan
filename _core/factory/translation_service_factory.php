<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\translation as core_translation_service;

final class translation_service_factory
{
    private service_factory_map $serviceFactoryMap;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->serviceFactoryMap = new service_factory_map($configuredServiceFactory);
    }

    public function __invoke(
        string $className,
        bool $allowIni,
        object $locale,
        object $runtime,
        callable $tabFactory,
        array $messageTagFactories,
        object $errorLogger,
        object $blockContext,
        object $matcher,
        object $requestInput,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory,
        callable $phpArrayLoader,
        object $fileStorage
    ): mixed {
        $arguments = [
            $allowIni,
            $locale,
            $runtime,
            $tabFactory,
            $messageTagFactories,
            $errorLogger,
            $blockContext,
            $matcher,
            $requestInput,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $phpArrayLoader,
            $fileStorage
        ];

        return $this->serviceFactoryMap->create(
            $className,
            core_translation_service::class,
            $arguments,
            $arguments
        );
    }

}
