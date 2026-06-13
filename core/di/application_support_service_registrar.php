<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\adapter\reflection_class_factory;
use fan\core\base\meta\maker;
use fan\core\base\meta\row;
use fan\core\base\service_dependencies;
use fan\core\block\admin\upload_size_limit_provider;
use fan\core\runtime\error_demonstrator_factory;
use fan\core\runtime\php_runtime_settings;
use fan\core\service\block_context;
use fan\core\service\plain_file_context;
use fan\core\service\transfer;
use fan\project\base\meta\row as meta_row;


final class application_support_service_registrar
{
    public function __construct(
        private ?\Closure $serviceDependenciesFactory = null,
        private ?\Closure $dependenciesFactory = null
    )
    {
    }

    public function register(
        container $container,
        object $serializerOperations,
        callable $requestInputFactory,
        callable $bootstrapOperationsFactory,
        callable $bootstrapRuntimeServiceFactory,
        callable $serviceEngineFactory,
        callable $blockExceptionFactory
    ): void {
        $serviceDependenciesFactory = $this->serviceDependenciesFactory();
        $dependenciesFactory = $this->dependenciesFactory();

        $container
            ->factory(service_id::PHP_RUNTIME_SETTINGS, static fn(container_interface $container): object => new php_runtime_settings())
            ->factory(service_id::SERIALIZER_OPERATIONS, static fn(container_interface $container): object => $serializerOperations)
            ->factory(service_id::ARRAY_ADDUCER, static fn(container_interface $container): callable => static fn(mixed $value): array => \adduceToArray($value))
            ->factory(service_id::RECURSIVE_MERGER, static fn(container_interface $container): callable => static fn(mixed ...$values): mixed => \array_merge_recursive_alt(...$values))
            ->factory(service_id::ARRAY_VALUE_READER, static fn(container_interface $container): callable => static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => \array_val($array, $key, $default))
            ->factory(service_id::ARRAY_LIKE_CHECKER, static fn(container_interface $container): callable => static fn(mixed $value): bool => \is_array_alt($value))
            ->factory(service_id::CLASS_NAME_RESOLVER, static fn(container_interface $container): callable => static fn(object $object): string => \get_class_alt($object) ?? get_class($object))
            ->factory(service_id::SHORT_CLASS_NAME_RESOLVER, static fn(container_interface $container): callable => static fn(object|string $object): string => \get_class_name($object) ?? (is_object($object) ? get_class($object) : $object))
            ->factory(service_id::NAMESPACE_RESOLVER, static fn(container_interface $container): callable => static fn(object|string $object, int $depth = 1): string => \get_ns_name($object, $depth) ?? '')
            ->factory(service_id::REFLECTION_CLASS_FACTORY, static fn(container_interface $container): object => new reflection_class_factory())
            ->factory(service_id::CORE_FATAL_EXCEPTION_FACTORY, static fn(container_interface $container): callable => new core_fatal_exception_factory())
            ->factory(
                service_id::SERVICE_DEPENDENCIES,
                static function (container_interface $container) use ($serviceDependenciesFactory): object {
                    $dependencies = $serviceDependenciesFactory($container);

                    return new service_dependencies(
                        $dependencies->bootstrapRuntime(),
                        $dependencies->config(),
                        $dependencies->cacheFactory(),
                        null,
                        null,
                        $dependencies->serviceEngineFactory(),
                        $dependencies->serviceExceptionFactory(),
                        $dependencies->classNameResolver(),
                        $dependencies->arrayValueReader()
                    );
                }
            )
            ->factory(
                service_id::REQUEST_INPUT,
                static function (container_interface $container) use ($requestInputFactory): object {
                    $requestInput = $requestInputFactory();
                    if (!is_object($requestInput)) {
                        throw new \RuntimeException('Request input factory must return an object.');
                    }

                    return $requestInput;
                }
            )
            ->factory(
                service_id::ERROR_DEMONSTRATOR_FACTORY,
                static function (container_interface $container) use ($dependenciesFactory): object {
                    $dependencies = $dependenciesFactory($container);

                    return new error_demonstrator_factory(
                        $dependencies->headerWriter(),
                        $dependencies->errorLogWriter(),
                        $dependencies->errorDemonstratorFileStorage(),
                        $dependencies->errorDemonstratorLoader()
                    );
                }
            )
            ->factory(
                service_id::BLOCK_EXCEPTION_FACTORY,
                static function (container_interface $container) use ($blockExceptionFactory, $dependenciesFactory): callable {
                    if (method_exists($blockExceptionFactory, 'setExceptionDependencies')) {
                        $dependencies = $dependenciesFactory($container);
                        $blockExceptionFactory->setExceptionDependencies(
                            null,
                            $dependencies->bootstrapRuntime(),
                            $dependencies->request(),
                            $dependencies->error(),
                            $dependencies->headerWriter()
                        );
                    }

                    return $blockExceptionFactory;
                }
            )
            ->factory(
                service_id::ERROR500_EXCEPTION_FACTORY,
                static function (container_interface $container) use ($dependenciesFactory): callable {
                    $dependencies = $dependenciesFactory($container);

                    return (new error500_exception_factory())->setExceptionDependencies(
                        null,
                        $dependencies->bootstrapRuntime(),
                        $dependencies->request(),
                        $dependencies->error(),
                        $dependencies->headerWriter()
                    );
                }
            )
            ->factory(
                service_id::META_ROW_FACTORY,
                static fn(container_interface $container): callable => static fn(
                    maker $maker,
                    array $data,
                    ?row $parent = null,
                    int|string|null $keyName = null,
                    ?callable $rowFactory = null
                ): object => new meta_row($maker, $data, $parent, $keyName, $rowFactory)
            )
            ->factory(service_id::DELAYED_META_FACTORY, static fn(container_interface $container): callable => new delayed_meta_factory())
            ->factory(
                service_id::META_MAKER_FACTORY,
                static function (container_interface $container) use ($dependenciesFactory): callable {
                    $dependencies = $dependenciesFactory($container);

                    return new meta_maker_factory(
                        $dependencies->delayedMetaFactory(),
                        $dependencies->recursiveMerger(),
                        $dependencies->arrayAdducer(),
                        $dependencies->classNameResolver()
                    );
                }
            )
            ->factory(service_id::VIEW_KEEPER_FACTORY, static fn(container_interface $container): callable => new view_keeper_factory())
            ->factory(service_id::VIEW_LOADER_JSON_KEEPER_FACTORY, static fn(container_interface $container): callable => new view_loader_json_keeper_factory())
            ->factory(service_id::VIEW_LOADER_TEXT_KEEPER_FACTORY, static fn(container_interface $container): callable => new view_loader_text_keeper_factory())
            ->factory(
                service_id::VIEW_LOADER_STATE_FACTORY,
                static function (container_interface $container) use ($dependenciesFactory): callable {
                    $dependencies = $dependenciesFactory($container);

                    return new view_loader_state_factory(
                        $dependencies->viewLoaderJsonKeeperFactory(),
                        $dependencies->viewLoaderTextKeeperFactory()
                    );
                }
            )
            ->factory(
                service_id::VIEW_ROUTER_FACTORY,
                static function (container_interface $container) use ($dependenciesFactory): callable {
                    $dependencies = $dependenciesFactory($container);

                    return new view_router_factory(
                        $dependencies->viewKeeperFactory(),
                        $dependencies->arrayAdducer()
                    );
                }
            )
            ->factory(service_id::TRANSFER_EXCEPTION_FACTORY, static fn(container_interface $container): callable => new transfer_exception_factory())
            ->factory(
                service_id::PLAIN_EXCEPTION_FACTORY,
                static function (container_interface $container) use ($dependenciesFactory): callable {
                    $dependencies = $dependenciesFactory($container);

                    return (new plain_exception_factory())->setExceptionDependencies(
                        null,
                        $dependencies->bootstrapRuntime(),
                        $dependencies->request(),
                        $dependencies->error(),
                        $dependencies->headerWriter()
                    );
                }
            )
            ->factory(
                service_id::UPLOAD_SIZE_LIMIT_PROVIDER,
                static function (container_interface $container) use ($dependenciesFactory): object {
                    $dependencies = $dependenciesFactory($container);

                    return new upload_size_limit_provider($dependencies->phpRuntimeSettings());
                }
            )
            ->factory(
                service_id::BOOTSTRAP_RUNTIME,
                static function (container_interface $container) use ($bootstrapOperationsFactory, $bootstrapRuntimeServiceFactory, $dependenciesFactory, $serviceEngineFactory): object {
                    $bootstrapOperations = $bootstrapOperationsFactory();
                    if (!is_array($bootstrapOperations)) {
                        throw new \RuntimeException('Bootstrap operations factory must return an array.');
                    }
                    $dependencies = $dependenciesFactory($container);

                    return $bootstrapRuntimeServiceFactory(
                        $dependencies->serviceListenerState(),
                        $dependencies->serviceSingleState(),
                        $dependencies->viewLoaderState(),
                        $dependencies->metaMakerState(),
                        $dependencies->specFileImageRowState(),
                        $serviceEngineFactory,
                        $bootstrapOperations
                    );
                }
            )
            ->factory(
                service_id::BLOCK_CONTEXT,
                static function (container_interface $container) use ($dependenciesFactory): object {
                    $dependencies = $dependenciesFactory($container);

                    return new block_context(
                        $dependencies->tabResolver(),
                        $dependencies->bootstrapRuntimeResolver(),
                        $dependencies->reflectionClassFactory()
                    );
                }
            )
            ->factory(
                service_id::PLAIN_FILE_CONTEXT,
                static function (container_interface $container) use ($dependenciesFactory): object {
                    $dependencies = $dependenciesFactory($container);

                    return new plain_file_context(
                        $dependencies->request(),
                        $dependencies->application(),
                        null,
                        $dependencies->translation(),
                        $dependencies->cacheFactory(),
                        $dependencies->bootstrapRuntime(),
                        $dependencies->entity(),
                        $dependencies->imageModifyFactory(),
                        $dependencies->imageMetadataReader(),
                        $dependencies->plainFileStorage(),
                        $dependencies->plainExceptionFactory()
                    );
                }
            )
            ->factory(
                service_id::TRANSFER,
                static function (container_interface $container) use ($dependenciesFactory): object {
                    $dependencies = $dependenciesFactory($container);

                    return new transfer(null, $dependencies->transferExceptionFactory());
                }
            );
    }

    private function serviceDependenciesFactory(): \Closure
    {
        return $this->serviceDependenciesFactory
            ?? static fn(container_interface $container): application_support_service_dependencies_registrar_dependencies => new application_support_service_dependencies_registrar_dependencies($container);
    }

    private function dependenciesFactory(): \Closure
    {
        return $this->dependenciesFactory
            ?? static fn(container_interface $container): application_support_service_registrar_dependencies => new application_support_service_registrar_dependencies($container);
    }
}
