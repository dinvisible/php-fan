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
                'config',
                static fn(container_interface $container, string $configType = 'service', string $sourceType = 'arr'): mixed => $infrastructureServiceCreator->createConfigService(
                    $container,
                    $container->get('config_state'),
                    $context->configServiceFactory,
                    $configType,
                    $sourceType,
                    $container->get('bootstrap_runtime'),
                    static fn(string $type): mixed => $container->get('cache', $type)
                )
            )
            ->factory('config_cache', static fn(container_interface $container): mixed => $infrastructureServiceCreator->createConfigCache($container, $container->get('cache_state'), $container->get('cache_memcache_state'), $context->cacheEngineFactory, $context->cacheServiceFactory))
            ->factory('cache', static fn(container_interface $container, mixed $type = null): mixed => $infrastructureServiceCreator->createCacheService($container, $container->get('cache_state'), $container->get('cache_memcache_state'), $context->cacheEngineFactory, $context->cacheServiceFactory, $type), false)
            ->factory(
                'json',
                static fn(container_interface $container, bool $useBase64 = false): mixed => $infrastructureServiceCreator->createJsonService(
                    $container,
                    $container->get('json_state'),
                    $context->jsonServiceFactory,
                    $useBase64
                ),
                false
            )
            ->factory(
                'file_system',
                static fn(container_interface $container, ?string $srcPath = null): mixed => $infrastructureServiceCreator->createFileSystemService(
                    $container,
                    $container->get('file_system_state'),
                    $context->fileSystemServiceFactory,
                    $srcPath,
                    $container->get('bootstrap_runtime'),
                    $container->get('config'),
                    static fn(string $type): mixed => $container->get('cache', $type)
                ),
                false
            )
            ->factory(
                'eloquent',
                static fn(container_interface $container): object => (new eloquent_manager_factory())(
                    $container->get('config')->get('eloquent', [])
                )
            );
    }
}
