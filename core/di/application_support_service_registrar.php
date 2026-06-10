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
            ->factory('php_runtime_settings', static fn(container_interface $container): object => new php_runtime_settings())
            ->factory('serializer_operations', static fn(container_interface $container): object => $serializerOperations)
            ->factory('array_adducer', static fn(container_interface $container): callable => static fn(mixed $value): array => \adduceToArray($value))
            ->factory('recursive_merger', static fn(container_interface $container): callable => static fn(mixed ...$values): mixed => \array_merge_recursive_alt(...$values))
            ->factory('array_value_reader', static fn(container_interface $container): callable => static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => \array_val($array, $key, $default))
            ->factory('array_like_checker', static fn(container_interface $container): callable => static fn(mixed $value): bool => \is_array_alt($value))
            ->factory('class_name_resolver', static fn(container_interface $container): callable => static fn(object $object): string => \get_class_alt($object) ?? get_class($object))
            ->factory('short_class_name_resolver', static fn(container_interface $container): callable => static fn(object|string $object): string => \get_class_name($object) ?? (is_object($object) ? get_class($object) : $object))
            ->factory('namespace_resolver', static fn(container_interface $container): callable => static fn(object|string $object, int $depth = 1): string => \get_ns_name($object, $depth) ?? '')
            ->factory('reflection_class_factory', static fn(container_interface $container): object => new reflection_class_factory())
            ->factory('core_fatal_exception_factory', static fn(container_interface $container): callable => new core_fatal_exception_factory())
            ->factory(
                'service_dependencies',
                static fn(container_interface $container): object => new service_dependencies(
                    $container->get('bootstrap_runtime'),
                    $container->get('config'),
                    static fn(string $type): mixed => $container->get('cache', $type),
                    null,
                    null,
                    $container->get('bootstrap_runtime')->serviceEngineFactory(),
                    $container->get('bootstrap_runtime')->serviceExceptionFactory(),
                    $container->get('class_name_resolver'),
                    $container->get('array_value_reader')
                )
            )
            ->factory(
                'request_input',
                static function (container_interface $container) use ($requestInputFactory): object {
                    $requestInput = $requestInputFactory();
                    if (!is_object($requestInput)) {
                        throw new \RuntimeException('Request input factory must return an object.');
                    }

                    return $requestInput;
                }
            )
            ->factory('error_demonstrator_factory', static fn(container_interface $container): object => new error_demonstrator_factory($container->get('header_writer'), $container->get('error_log_writer'), $container->get('error_demonstrator_file_storage'), $container->get('error_demonstrator_loader')))
            ->factory(
                'block_exception_factory',
                static function (container_interface $container) use ($blockExceptionFactory): callable {
                    if (method_exists($blockExceptionFactory, 'setExceptionDependencies')) {
                        $blockExceptionFactory->setExceptionDependencies(
                            null,
                            $container->get('bootstrap_runtime'),
                            $container->get('request'),
                            $container->get('error'),
                            $container->get('header_writer')
                        );
                    }

                    return $blockExceptionFactory;
                }
            )
            ->factory(
                'error500_exception_factory',
                static function (container_interface $container): callable {
                    return (new error500_exception_factory())->setExceptionDependencies(
                        null,
                        $container->get('bootstrap_runtime'),
                        $container->get('request'),
                        $container->get('error'),
                        $container->get('header_writer')
                    );
                }
            )
            ->factory(
                'meta_row_factory',
                static fn(container_interface $container): callable => static fn(
                    maker $maker,
                    array $data,
                    ?row $parent = null,
                    int|string|null $keyName = null,
                    ?callable $rowFactory = null
                ): object => new meta_row($maker, $data, $parent, $keyName, $rowFactory)
            )
            ->factory('delayed_meta_factory', static fn(container_interface $container): callable => new delayed_meta_factory())
            ->factory(
                'meta_maker_factory',
                static fn(container_interface $container): callable => new meta_maker_factory(
                    $container->get('delayed_meta_factory'),
                    $container->get('recursive_merger'),
                    $container->get('array_adducer'),
                    $container->get('class_name_resolver')
                )
            )
            ->factory('view_keeper_factory', static fn(container_interface $container): callable => new view_keeper_factory())
            ->factory('view_loader_json_keeper_factory', static fn(container_interface $container): callable => new view_loader_json_keeper_factory())
            ->factory('view_loader_text_keeper_factory', static fn(container_interface $container): callable => new view_loader_text_keeper_factory())
            ->factory(
                'view_loader_state_factory',
                static fn(container_interface $container): callable => new view_loader_state_factory(
                    $container->get('view_loader_json_keeper_factory'),
                    $container->get('view_loader_text_keeper_factory')
                )
            )
            ->factory('view_router_factory', static fn(container_interface $container): callable => new view_router_factory($container->get('view_keeper_factory'), $container->get('array_adducer')))
            ->factory('transfer_exception_factory', static fn(container_interface $container): callable => new transfer_exception_factory())
            ->factory(
                'plain_exception_factory',
                static function (container_interface $container): callable {
                    return (new plain_exception_factory())->setExceptionDependencies(
                        null,
                        $container->get('bootstrap_runtime'),
                        $container->get('request'),
                        $container->get('error'),
                        $container->get('header_writer')
                    );
                }
            )
            ->factory('upload_size_limit_provider', static fn(container_interface $container): object => new upload_size_limit_provider($container->get('php_runtime_settings')))
            ->factory(
                'bootstrap_runtime',
                static function (container_interface $container) use ($bootstrapOperationsFactory, $bootstrapRuntimeServiceFactory, $serviceEngineFactory): object {
                    $bootstrapOperations = $bootstrapOperationsFactory();
                    if (!is_array($bootstrapOperations)) {
                        throw new \RuntimeException('Bootstrap operations factory must return an array.');
                    }

                    return $bootstrapRuntimeServiceFactory(
                        $container->get('service_listener_state'),
                        $container->get('service_single_state'),
                        $container->get('view_loader_state'),
                        $container->get('meta_maker_state'),
                        $container->get('spec_file_image_row_state'),
                        $serviceEngineFactory,
                        $bootstrapOperations
                    );
                }
            )
            ->factory(
                'block_context',
                static fn(container_interface $container): object => new block_context(
                    $container,
                    $container->get('reflection_class_factory')
                )
            )
            ->factory(
                'plain_file_context',
                static fn(container_interface $container): object => new plain_file_context(
                    $container->get('request'),
                    $container->get('application'),
                    null,
                    $container->get('translation'),
                    static fn(string $type): mixed => $container->get('cache', $type),
                    $container->get('bootstrap_runtime'),
                    $container->get('entity'),
                    static fn(string $sourcePath): mixed => $container->get('image_modify', $sourcePath),
                    $container->get('image_metadata_reader'),
                    $container->get('plain_file_storage'),
                    $container->get('plain_exception_factory')
                )
            )
            ->factory(
                'transfer',
                static fn(container_interface $container): object => new transfer(
                    null,
                    $container->get('transfer_exception_factory')
                )
            );
    }
}
