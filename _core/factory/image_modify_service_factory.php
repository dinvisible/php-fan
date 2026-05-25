<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\service\image_draw as core_image_draw_service;
use fan\core\service\image_modify as core_image_modify_service;

final class image_modify_service_factory
{
    private service_factory_map $serviceFactoryMap;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->serviceFactoryMap = new service_factory_map($configuredServiceFactory);
    }

    public function __invoke(
        string $className,
        ?string $sourcePath,
        array $createParam,
        object $state,
        object $runtime,
        object $serviceBootstrapRuntime,
        object $serviceConfigurator,
        callable $serviceCacheFactory,
        object $imageMetadataReader,
        object $imageResourceFactory,
        object $imageCanvasOperations,
        object $imageOutputWriter,
        object $imageSourceFileStorage,
        ?callable $arrayValueReader = null
    ): object {
        $arguments = [
            $sourcePath,
            $createParam,
            $state,
            $runtime,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $imageMetadataReader,
            $imageResourceFactory,
            $imageCanvasOperations,
            $imageOutputWriter,
            $imageSourceFileStorage,
            $arrayValueReader ?? static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => \array_val($array, $key, $default)
        ];

        if ($className === core_image_draw_service::class) {
            return $this->serviceFactoryMap->create($className, core_image_draw_service::class, $arguments, $arguments);
        }

        return $this->serviceFactoryMap->create(
            $className,
            core_image_modify_service::class,
            $arguments,
            $arguments
        );
    }

}
