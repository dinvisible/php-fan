<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_service_registrar
{
    public function __construct(private ?\Closure $dependenciesFactory = null)
    {
    }

    public function register(container $container, application_service_graph_registration_context $context): container
    {
        $infrastructureServiceCreator = $context->infrastructureServiceCreator;
        $dependenciesFactory = $this->dependenciesFactory();

        return $container
            ->factory(
                service_id::CONFIG,
                static function (container_interface $container, string $configType = 'service', string $sourceType = 'arr') use ($context, $dependenciesFactory, $infrastructureServiceCreator): mixed {
                    $dependencies = $dependenciesFactory($container);

                    return $infrastructureServiceCreator->createConfigService(
                        $container,
                        $dependencies->configState(),
                        $context->configServiceFactory,
                        $configType,
                        $sourceType,
                        $dependencies->bootstrapRuntime(),
                        $dependencies->cacheFactory()
                    );
                }
            )
            ->factory(
                service_id::CONFIG_CACHE,
                static function (container_interface $container) use ($context, $dependenciesFactory, $infrastructureServiceCreator): mixed {
                    $dependencies = $dependenciesFactory($container);

                    return $infrastructureServiceCreator->createConfigCache($container, $dependencies->cacheState(), $dependencies->cacheMemcacheState(), $context->cacheEngineFactory, $context->cacheServiceFactory);
                }
            )
            ->factory(service_id::CACHE,
                static function (container_interface $container, mixed $type = null) use ($context, $dependenciesFactory, $infrastructureServiceCreator): mixed {
                    $dependencies = $dependenciesFactory($container);

                    return $infrastructureServiceCreator->createCacheService($container, $dependencies->cacheState(), $dependencies->cacheMemcacheState(), $context->cacheEngineFactory, $context->cacheServiceFactory, $type);
                },
                false
            )
            ->factory(
                service_id::JSON,
                static fn(container_interface $container, bool $useBase64 = false): mixed => $infrastructureServiceCreator->createJsonService(
                    $container,
                    $dependenciesFactory($container)->jsonState(),
                    $context->jsonServiceFactory,
                    $useBase64
                ),
                false
            )
            ->factory(
                service_id::FILE_SYSTEM,
                static function (container_interface $container, ?string $srcPath = null) use ($context, $dependenciesFactory, $infrastructureServiceCreator): mixed {
                    $dependencies = $dependenciesFactory($container);

                    return $infrastructureServiceCreator->createFileSystemService(
                        $container,
                        $dependencies->fileSystemState(),
                        $context->fileSystemServiceFactory,
                        $srcPath,
                        $dependencies->bootstrapRuntime(),
                        $dependencies->config(),
                        $dependencies->cacheFactory()
                    );
                },
                false
            )
            ->factory(
                service_id::ELOQUENT,
                static fn(container_interface $container): object => (new eloquent_manager_factory())(
                    $dependenciesFactory($container)->config()->get('eloquent', [])
                )
            );
    }

    private function dependenciesFactory(): \Closure
    {
        return $this->dependenciesFactory
            ?? static fn(container_interface $container): application_infrastructure_service_registrar_dependencies => new application_infrastructure_service_registrar_dependencies($container);
    }
}
