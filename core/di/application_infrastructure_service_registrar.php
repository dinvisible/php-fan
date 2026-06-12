<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_service_registrar
{
    public function register(container $container, application_service_graph_registration_context $context): container
    {
        $infrastructureServiceCreator = $context->infrastructureServiceCreator;

        return $container
            ->factory(
                service_id::CONFIG,
                static fn(container_interface $container, string $configType = 'service', string $sourceType = 'arr'): mixed => $infrastructureServiceCreator->createConfigService(
                    $container,
                    $container->get(service_id::CONFIG_STATE),
                    $context->configServiceFactory,
                    $configType,
                    $sourceType,
                    $container->get(service_id::BOOTSTRAP_RUNTIME),
                    static fn(string $type): mixed => $container->get(service_id::CACHE, $type)
                )
            )
            ->factory(service_id::CONFIG_CACHE, static fn(container_interface $container): mixed => $infrastructureServiceCreator->createConfigCache($container, $container->get(service_id::CACHE_STATE), $container->get(service_id::CACHE_MEMCACHE_STATE), $context->cacheEngineFactory, $context->cacheServiceFactory))
            ->factory(service_id::CACHE, static fn(container_interface $container, mixed $type = null): mixed => $infrastructureServiceCreator->createCacheService($container, $container->get(service_id::CACHE_STATE), $container->get(service_id::CACHE_MEMCACHE_STATE), $context->cacheEngineFactory, $context->cacheServiceFactory, $type), false)
            ->factory(
                service_id::JSON,
                static fn(container_interface $container, bool $useBase64 = false): mixed => $infrastructureServiceCreator->createJsonService(
                    $container,
                    $container->get(service_id::JSON_STATE),
                    $context->jsonServiceFactory,
                    $useBase64
                ),
                false
            )
            ->factory(
                service_id::FILE_SYSTEM,
                static fn(container_interface $container, ?string $srcPath = null): mixed => $infrastructureServiceCreator->createFileSystemService(
                    $container,
                    $container->get(service_id::FILE_SYSTEM_STATE),
                    $context->fileSystemServiceFactory,
                    $srcPath,
                    $container->get(service_id::BOOTSTRAP_RUNTIME),
                    $container->get(service_id::CONFIG),
                    static fn(string $type): mixed => $container->get(service_id::CACHE, $type)
                ),
                false
            )
            ->factory(
                service_id::ELOQUENT,
                static fn(container_interface $container): object => (new eloquent_manager_factory())(
                    $container->get(service_id::CONFIG)->get('eloquent', [])
                )
            );
    }
}
