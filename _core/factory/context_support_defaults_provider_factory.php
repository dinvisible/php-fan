<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\di\context_support_defaults_factory;

final class context_support_defaults_provider_factory
{
    public function __invoke(): context_support_defaults_factory
    {

        return new context_support_defaults_factory(
            static function (): array {
                $contextAutoloaderDefaults = (new bootstrap_autoloader_defaults_provider_factory())();
                $contextLoaderDefaults = (new bootstrap_loader_defaults_provider_factory())();
                $bootstrapLoaderFileStorage = $contextLoaderDefaults->fileStorage();
                $contextConfigDefaults = new bootstrap_config_defaults_factory();
                $contextObjectDefaults = (new bootstrap_object_defaults_provider_factory())();
                $bootstrapObjectFactory = $contextObjectDefaults->objectFactory();

                return [
                    'zendAutoloaderLoaderFactory' => static fn(): object => $contextAutoloaderDefaults->zendAutoloaderLoader(),
                    'bootstrapLoaderFileStorageFactory' => static fn(): object => $bootstrapLoaderFileStorage,
                    'bootstrapObjectFactory' => $bootstrapObjectFactory,
                    'bootstrapConfigLoader' => $contextConfigDefaults->configLoader(),
                ];
            }
        );
    }
}
