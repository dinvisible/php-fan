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
    public function register(
        container $container,
        object $serializerOperations,
        callable $requestInputFactory,
        callable $bootstrapOperationsFactory,
        callable $bootstrapRuntimeServiceFactory,
        callable $serviceEngineFactory,
        callable $blockExceptionFactory
    ): void {
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
                static fn(container_interface $container): object => new service_dependencies(
                    $container->get(service_id::BOOTSTRAP_RUNTIME),
                    $container->get(service_id::CONFIG),
                    static fn(string $type): mixed => $container->get(service_id::CACHE, $type),
                    null,
                    null,
                    $container->get(service_id::BOOTSTRAP_RUNTIME)->serviceEngineFactory(),
                    $container->get(service_id::BOOTSTRAP_RUNTIME)->serviceExceptionFactory(),
                    $container->get(service_id::CLASS_NAME_RESOLVER),
                    $container->get(service_id::ARRAY_VALUE_READER)
                )
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
            ->factory(service_id::ERROR_DEMONSTRATOR_FACTORY, static fn(container_interface $container): object => new error_demonstrator_factory($container->get(service_id::HEADER_WRITER), $container->get(service_id::ERROR_LOG_WRITER), $container->get(service_id::ERROR_DEMONSTRATOR_FILE_STORAGE), $container->get(service_id::ERROR_DEMONSTRATOR_LOADER)))
            ->factory(
                service_id::BLOCK_EXCEPTION_FACTORY,
                static function (container_interface $container) use ($blockExceptionFactory): callable {
                    if (method_exists($blockExceptionFactory, 'setExceptionDependencies')) {
                        $blockExceptionFactory->setExceptionDependencies(
                            null,
                            $container->get(service_id::BOOTSTRAP_RUNTIME),
                            $container->get(service_id::REQUEST),
                            $container->get(service_id::ERROR),
                            $container->get(service_id::HEADER_WRITER)
                        );
                    }

                    return $blockExceptionFactory;
                }
            )
            ->factory(
                service_id::ERROR500_EXCEPTION_FACTORY,
                static function (container_interface $container): callable {
                    return (new error500_exception_factory())->setExceptionDependencies(
                        null,
                        $container->get(service_id::BOOTSTRAP_RUNTIME),
                        $container->get(service_id::REQUEST),
                        $container->get(service_id::ERROR),
                        $container->get(service_id::HEADER_WRITER)
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
                static fn(container_interface $container): callable => new meta_maker_factory(
                    $container->get(service_id::DELAYED_META_FACTORY),
                    $container->get(service_id::RECURSIVE_MERGER),
                    $container->get(service_id::ARRAY_ADDUCER),
                    $container->get(service_id::CLASS_NAME_RESOLVER)
                )
            )
            ->factory(service_id::VIEW_KEEPER_FACTORY, static fn(container_interface $container): callable => new view_keeper_factory())
            ->factory(service_id::VIEW_LOADER_JSON_KEEPER_FACTORY, static fn(container_interface $container): callable => new view_loader_json_keeper_factory())
            ->factory(service_id::VIEW_LOADER_TEXT_KEEPER_FACTORY, static fn(container_interface $container): callable => new view_loader_text_keeper_factory())
            ->factory(
                service_id::VIEW_LOADER_STATE_FACTORY,
                static fn(container_interface $container): callable => new view_loader_state_factory(
                    $container->get(service_id::VIEW_LOADER_JSON_KEEPER_FACTORY),
                    $container->get(service_id::VIEW_LOADER_TEXT_KEEPER_FACTORY)
                )
            )
            ->factory(service_id::VIEW_ROUTER_FACTORY, static fn(container_interface $container): callable => new view_router_factory($container->get(service_id::VIEW_KEEPER_FACTORY), $container->get(service_id::ARRAY_ADDUCER)))
            ->factory(service_id::TRANSFER_EXCEPTION_FACTORY, static fn(container_interface $container): callable => new transfer_exception_factory())
            ->factory(
                service_id::PLAIN_EXCEPTION_FACTORY,
                static function (container_interface $container): callable {
                    return (new plain_exception_factory())->setExceptionDependencies(
                        null,
                        $container->get(service_id::BOOTSTRAP_RUNTIME),
                        $container->get(service_id::REQUEST),
                        $container->get(service_id::ERROR),
                        $container->get(service_id::HEADER_WRITER)
                    );
                }
            )
            ->factory(service_id::UPLOAD_SIZE_LIMIT_PROVIDER, static fn(container_interface $container): object => new upload_size_limit_provider($container->get(service_id::PHP_RUNTIME_SETTINGS)))
            ->factory(
                service_id::BOOTSTRAP_RUNTIME,
                static function (container_interface $container) use ($bootstrapOperationsFactory, $bootstrapRuntimeServiceFactory, $serviceEngineFactory): object {
                    $bootstrapOperations = $bootstrapOperationsFactory();
                    if (!is_array($bootstrapOperations)) {
                        throw new \RuntimeException('Bootstrap operations factory must return an array.');
                    }

                    return $bootstrapRuntimeServiceFactory(
                        $container->get(service_id::SERVICE_LISTENER_STATE),
                        $container->get(service_id::SERVICE_SINGLE_STATE),
                        $container->get(service_id::VIEW_LOADER_STATE),
                        $container->get(service_id::META_MAKER_STATE),
                        $container->get(service_id::SPEC_FILE_IMAGE_ROW_STATE),
                        $serviceEngineFactory,
                        $bootstrapOperations
                    );
                }
            )
            ->factory(
                service_id::BLOCK_CONTEXT,
                static fn(container_interface $container): object => new block_context(
                    $container,
                    $container->get(service_id::REFLECTION_CLASS_FACTORY)
                )
            )
            ->factory(
                service_id::PLAIN_FILE_CONTEXT,
                static fn(container_interface $container): object => new plain_file_context(
                    $container->get(service_id::REQUEST),
                    $container->get(service_id::APPLICATION),
                    null,
                    $container->get(service_id::TRANSLATION),
                    static fn(string $type): mixed => $container->get(service_id::CACHE, $type),
                    $container->get(service_id::BOOTSTRAP_RUNTIME),
                    $container->get(service_id::ENTITY),
                    static fn(string $sourcePath): mixed => $container->get(service_id::IMAGE_MODIFY, $sourcePath),
                    $container->get(service_id::IMAGE_METADATA_READER),
                    $container->get(service_id::PLAIN_FILE_STORAGE),
                    $container->get(service_id::PLAIN_EXCEPTION_FACTORY)
                )
            )
            ->factory(
                service_id::TRANSFER,
                static fn(container_interface $container): object => new transfer(
                    null,
                    $container->get(service_id::TRANSFER_EXCEPTION_FACTORY)
                )
            );
    }
}
