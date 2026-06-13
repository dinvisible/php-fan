<?php

declare(strict_types=1);

use fan\core\di\application_infrastructure_service_creator;
use fan\core\di\container;
use PHPUnit\Framework\TestCase;
use fan\core\service\config\row;
use fan\project\service\cache;
use fan\project\service\config;
use fan\project\service\file_system;
use fan\project\service\json;


final class ApplicationInfrastructureServiceCreatorTest extends TestCase
{    public function testDefaultConfigCreatorFailsWhenConfigRowFactoryFactoryIsNotInjected(): void
    {
        $creator = new application_infrastructure_service_creator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Config row factory factory is not configured for infrastructure service creator.');

        $creator->createConfigService(
            $this->containerWithInfrastructureDependencies(),
            new ApplicationInfrastructureStateDouble(),
            static fn(): object => (object)['service' => 'config']
        );
    }

    public function testConfigCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = $this->containerWithInfrastructureDependencies();
        $configState = new ApplicationInfrastructureStateDouble();
        $received = [];

        $rowFactory = static fn(mixed $data): row => new row($data);
        $rowFactoryFactoryCalls = [];
        $creator = new application_infrastructure_service_creator(
            static function (object $serializerOperations, callable $serviceExceptionFactory, callable $shortClassNameResolver) use (&$rowFactoryFactoryCalls, $rowFactory): callable {
                $rowFactoryFactoryCalls[] = [$serializerOperations, $serviceExceptionFactory, $shortClassNameResolver];

                return $rowFactory;
            }
        );

        $service = $creator->createConfigService(
            $container,
            $configState,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'config'];
            },
            'service',
            'arr'
        );

        $this->assertSame('config', $service->service);
        $this->assertSame('\\' . config::class, $received[0] ?? null);
        $this->assertSame('service', $received[1] ?? null);
        $this->assertSame('arr', $received[2] ?? null);
        $this->assertSame($configState, $received[5] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[6] ?? null);
        $this->assertSame($container->get('php_array_file_loader'), $received[10] ?? null);
        $this->assertSame($rowFactory, $received[11] ?? null);
        $this->assertSame($container->get('cache_source_file_metadata'), $received[12] ?? null);
        $this->assertSame($container->get('config_source_file_storage'), $received[13] ?? null);
        $this->assertSame($container->get('short_class_name_resolver'), $received[14] ?? null);
        $this->assertSame($container->get('serializer_operations'), $rowFactoryFactoryCalls[0][0] ?? null);
        $this->assertSame($container->get('bootstrap_runtime')->serviceExceptionFactory(), $rowFactoryFactoryCalls[0][1] ?? null);
        $this->assertSame($container->get('short_class_name_resolver'), $rowFactoryFactoryCalls[0][2] ?? null);
    }

    public function testCacheJsonAndFileSystemCreatorsPassExplicitDependencies(): void
    {
        $container = $this->containerWithInfrastructureDependencies();
        $creator = new application_infrastructure_service_creator();
        $cacheState = new ApplicationInfrastructureStateDouble();
        $memcacheState = new stdClass();
        $cacheEngineFactory = static fn(): object => (object)['name' => 'cache_engine_factory'];
        $cacheCalls = [];
        $cacheServiceFactory = static function (mixed ...$arguments) use (&$cacheCalls): object {
            $cacheCalls[] = $arguments;

            return new ApplicationInfrastructureCacheDouble();
        };

        $configCache = $creator->createConfigCache($container, $cacheState, $memcacheState, $cacheEngineFactory, $cacheServiceFactory);
        $this->assertInstanceOf(ApplicationInfrastructureCacheDouble::class, $configCache);
        $this->assertSame('\\' . cache::class, $cacheCalls[0][0] ?? null);
        $this->assertSame(cache::CONFIG_TYPE, $cacheCalls[0][1] ?? null);
        $this->assertSame($container->get('cache_source_file_metadata'), $cacheCalls[0][10] ?? null);
        $this->assertIsCallable($cacheCalls[0][11] ?? null);

        $cache = $creator->createCacheService($container, $cacheState, $memcacheState, $cacheEngineFactory, $cacheServiceFactory, 'runtime');
        $this->assertInstanceOf(ApplicationInfrastructureCacheDouble::class, $cache);
        $this->assertSame('runtime', $cacheCalls[1][1] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $cacheCalls[1][2] ?? null);
        $this->assertSame($container->get('config'), $cacheCalls[1][8] ?? null);
        $this->assertIsCallable($cacheCalls[1][11] ?? null);

        $jsonState = new ApplicationInfrastructureStateDouble();
        $jsonCalls = [];
        $json = $creator->createJsonService(
            $container,
            $jsonState,
            static function (mixed ...$arguments) use (&$jsonCalls): object {
                $jsonCalls[] = $arguments;

                return (object)['service' => 'json'];
            },
            true
        );
        $this->assertSame('json', $json->service);
        $this->assertSame('\\' . json::class, $jsonCalls[0][0] ?? null);
        $this->assertTrue($jsonCalls[0][1] ?? null);
        $this->assertSame($container->get('config'), $jsonCalls[0][4] ?? null);

        $fileSystemState = new ApplicationInfrastructureStateDouble();
        $fileSystemCalls = [];
        $fileSystem = $creator->createFileSystemService(
            $container,
            $fileSystemState,
            static function (mixed ...$arguments) use (&$fileSystemCalls): object {
                $fileSystemCalls[] = $arguments;

                return (object)['service' => 'file_system'];
            },
            '{PROJECT}/tmp'
        );
        $this->assertSame('file_system', $fileSystem->service);
        $this->assertSame('\\' . file_system::class, $fileSystemCalls[0][0] ?? null);
        $this->assertSame('/parsed/{PROJECT}/tmp', $fileSystemCalls[0][1] ?? null);
        $this->assertSame($container->get('file_system_storage'), $fileSystemCalls[0][2] ?? null);
    }

    public function testProjectServiceClassAvailabilityCheckIsInjected(): void
    {
        $checkedClasses = [];
        $creator = new application_infrastructure_service_creator(
            null,
            static function (string $className) use (&$checkedClasses): bool {
                $checkedClasses[] = $className;

                return false;
            }
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Service "json" does not expose a project class.');

        try {
            $creator->createJsonService(
                $this->containerWithInfrastructureDependencies(),
                new ApplicationInfrastructureStateDouble(),
                static fn(): object => (object)['service' => 'json'],
                true
            );
        } finally {
            $this->assertSame(['\\' . json::class], $checkedClasses);
        }
    }

    public function testCacheCreatorUsesInjectedServiceExceptionFactoryWhenDefaultTypeIsMissing(): void
    {
        $calls = [];
        $config = new ApplicationInfrastructureConfigDouble('');
        $runtime = new ApplicationInfrastructureRuntimeDouble(
            static function (
                string $exceptionClass,
                object $service,
                string $message,
                int $code,
                ?Throwable $previous
            ) use (&$calls): Throwable {
                $calls[] = [$exceptionClass, $service, $message, $code, $previous];

                return new RuntimeException('factory: ' . $message, $code, $previous);
            }
        );
        $container = $this->containerWithInfrastructureDependencies($config, $runtime);
        $creator = new application_infrastructure_service_creator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('factory: Default CACHE-type doesn\'t set in config-file.');

        try {
            $creator->createCacheService(
                $container,
                new ApplicationInfrastructureStateDouble(),
                new stdClass(),
                static fn(): object => new stdClass(),
                static fn(): object => new ApplicationInfrastructureCacheDouble()
            );
        } finally {
            $this->assertCount(1, $calls);
            $this->assertSame('\fan\project\exception\service\fatal', $calls[0][0]);
            $this->assertSame($config, $calls[0][1]);
            $this->assertSame('Default CACHE-type doesn\'t set in config-file.', $calls[0][2]);
            $this->assertSame(E_USER_ERROR, $calls[0][3]);
            $this->assertNull($calls[0][4]);
        }
    }

    public function testConfigCacheUsesInjectedError500FactoryWhenCacheInstancesAlreadyExist(): void
    {
        $calls = [];
        $runtime = new ApplicationInfrastructureRuntimeDouble();
        $container = $this->containerWithInfrastructureDependencies(
            runtime: $runtime,
            error500ExceptionFactory: static function (string $message, int $code, ?Throwable $previous = null) use (&$calls): Throwable {
                $calls[] = [$message, $code, $previous];

                return new RuntimeException('factory: ' . $message, $code, $previous);
            }
        );
        $cacheState = new ApplicationInfrastructureStateDouble();
        $cacheState->setInstance('runtime', new stdClass());
        $creator = new application_infrastructure_service_creator();

        $result = $creator->createConfigCache(
            $container,
            $cacheState,
            new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new ApplicationInfrastructureCacheDouble()
        );

        $this->assertNull($result);
        $this->assertSame([
            ['It\'s inpossible to get config-Instance after make another Instances.', E_USER_ERROR, null],
        ], $calls);
        $this->assertSame([
            'factory: It\'s inpossible to get config-Instance after make another Instances.',
        ], $runtime->loggedErrors);
    }

    public function testCacheCreatorUsesInjectedError500FactoryForConfigCacheType(): void
    {
        $calls = [];
        $container = $this->containerWithInfrastructureDependencies(
            error500ExceptionFactory: static function (string $message, int $code, ?Throwable $previous = null) use (&$calls): Throwable {
                $calls[] = [$message, $code, $previous];

                return new RuntimeException('factory: ' . $message, $code, $previous);
            }
        );
        $creator = new application_infrastructure_service_creator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('factory: It\'s inpossible to get config-Instance by usual way.');

        try {
            $creator->createCacheService(
                $container,
                new ApplicationInfrastructureStateDouble(),
                new stdClass(),
                static fn(): object => new stdClass(),
                static fn(): object => new ApplicationInfrastructureCacheDouble(),
                cache::CONFIG_TYPE
            );
        } finally {
            $this->assertSame([
                ['It\'s inpossible to get config-Instance by usual way.', E_USER_ERROR, null],
            ], $calls);
        }
    }

    public function testInfrastructureCreatorUsesConfigCacheDependencyBundle(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_service_creator.php');
        $dependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_dependencies.php');
        $factorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_factory_dependencies.php');
        $configFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_config_factory_dependencies.php');
        $configInstanceSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_config_instance_dependencies.php');
        $typedConfigFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_typed_config_factory_dependencies.php');
        $cacheFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_cache_factory_dependencies.php');
        $configCacheFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_config_cache_factory_dependencies.php');
        $typeCacheFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_type_cache_factory_dependencies.php');
        $supportSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_support_dependencies.php');
        $runtimeSupportSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_runtime_support_dependencies.php');
        $bootstrapRuntimeSupportSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_bootstrap_runtime_support_dependencies.php');
        $errorFactoryRuntimeSupportSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_error_factory_runtime_support_dependencies.php');
        $loaderSerializerSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_loader_serializer_dependencies.php');
        $phpArrayFileLoaderSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_php_array_file_loader_dependencies.php');
        $serializerOperationsSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_serializer_operations_dependencies.php');
        $classHelperSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_class_helper_dependencies.php');
        $storageSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_storage_dependencies.php');
        $cacheSourceFileMetadataStorageSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_cache_source_file_metadata_storage_dependencies.php');
        $configSourceFileStorageSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_config_source_file_storage_dependencies.php');
        $exceptionSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_exception_dependencies.php');
        $exceptionFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_exception_factory_dependencies.php');
        $coreFatalExceptionFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_core_fatal_exception_factory_dependencies.php');
        $error500ExceptionFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_error500_exception_factory_dependencies.php');
        $requestHeaderSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_request_header_dependencies.php');
        $requestInputSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_request_input_dependencies.php');
        $headerWriterSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_cache_header_writer_dependencies.php');
        $serviceDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_service_dependencies.php');
        $runtimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_runtime_dependencies.php');
        $runtimeErrorSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_runtime_error_dependencies.php');
        $runtimeErrorFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_runtime_error_factory_dependencies.php');
        $runtimeBootstrapRuntimeErrorSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_runtime_bootstrap_runtime_error_dependencies.php');
        $runtimeConfigCacheSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_runtime_config_cache_dependencies.php');
        $runtimeConfigSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_runtime_config_dependencies.php');
        $runtimeCacheFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_runtime_cache_factory_dependencies.php');
        $serviceStorageSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_storage_dependencies.php');

        $this->assertIsString($source);
        $this->assertIsString($dependenciesSource);
        $this->assertIsString($factorySource);
        $this->assertIsString($configFactorySource);
        $this->assertIsString($configInstanceSource);
        $this->assertIsString($typedConfigFactorySource);
        $this->assertIsString($cacheFactorySource);
        $this->assertIsString($configCacheFactorySource);
        $this->assertIsString($typeCacheFactorySource);
        $this->assertIsString($supportSource);
        $this->assertIsString($runtimeSupportSource);
        $this->assertIsString($bootstrapRuntimeSupportSource);
        $this->assertIsString($errorFactoryRuntimeSupportSource);
        $this->assertIsString($loaderSerializerSource);
        $this->assertIsString($phpArrayFileLoaderSource);
        $this->assertIsString($serializerOperationsSource);
        $this->assertIsString($classHelperSource);
        $this->assertIsString($storageSource);
        $this->assertIsString($cacheSourceFileMetadataStorageSource);
        $this->assertIsString($configSourceFileStorageSource);
        $this->assertIsString($exceptionSource);
        $this->assertIsString($exceptionFactorySource);
        $this->assertIsString($coreFatalExceptionFactorySource);
        $this->assertIsString($error500ExceptionFactorySource);
        $this->assertIsString($requestHeaderSource);
        $this->assertIsString($requestInputSource);
        $this->assertIsString($headerWriterSource);
        $this->assertIsString($serviceDependenciesSource);
        $this->assertIsString($runtimeSource);
        $this->assertIsString($runtimeErrorSource);
        $this->assertIsString($runtimeErrorFactorySource);
        $this->assertIsString($runtimeBootstrapRuntimeErrorSource);
        $this->assertIsString($runtimeConfigCacheSource);
        $this->assertIsString($runtimeConfigSource);
        $this->assertIsString($runtimeCacheFactorySource);
        $this->assertIsString($serviceStorageSource);
        $this->assertStringContainsString('private static function serviceDependencies(container_interface $container): application_infrastructure_service_dependencies', $source);
        $this->assertStringContainsString('return new application_infrastructure_service_dependencies($container);', $source);
        $this->assertStringContainsString('private static function configCacheDependencies(container_interface $container): application_infrastructure_config_cache_dependencies', $source);
        $this->assertStringContainsString('return new application_infrastructure_config_cache_dependencies($container);', $source);
        $this->assertStringContainsString('$infrastructureDependencies = self::configCacheDependencies($container);', $source);
        $this->assertStringContainsString('$infrastructureDependencies = self::serviceDependencies($container);', $source);
        $this->assertStringContainsString('$infrastructureDependencies->configFactory()', $source);
        $this->assertStringContainsString('$infrastructureDependencies->cacheSourceFileMetadata()', $source);
        $this->assertStringContainsString('$infrastructureDependencies->fileSystemStorage()', $source);
        $this->assertStringContainsString('final class application_infrastructure_config_cache_dependencies', $dependenciesSource);
        $this->assertStringContainsString('$this->factory = new application_infrastructure_config_cache_factory_dependencies($container);', $dependenciesSource);
        $this->assertStringContainsString('$this->support = new application_infrastructure_config_cache_support_dependencies($container);', $dependenciesSource);
        $this->assertStringContainsString('$this->storage = new application_infrastructure_config_cache_storage_dependencies($container);', $dependenciesSource);
        $this->assertStringContainsString('$this->exception = new application_infrastructure_config_cache_exception_dependencies($container);', $dependenciesSource);
        $this->assertStringContainsString('new application_infrastructure_config_cache_config_factory_dependencies($container)', $factorySource);
        $this->assertStringContainsString('new application_infrastructure_config_cache_cache_factory_dependencies($container)', $factorySource);
        $this->assertStringContainsString('return $this->config->configFactory();', $factorySource);
        $this->assertStringContainsString('return $this->cache->cacheFactory();', $factorySource);
        $this->assertStringContainsString('new application_infrastructure_config_cache_config_instance_dependencies($container)', $configFactorySource);
        $this->assertStringContainsString('new application_infrastructure_config_cache_typed_config_factory_dependencies($container)', $configFactorySource);
        $this->assertStringContainsString('return $this->config->config();', $configFactorySource);
        $this->assertStringContainsString('return $this->configFactory->configFactory();', $configFactorySource);
        $this->assertStringContainsString('return $this->container->get(service_id::CONFIG);', $configInstanceSource);
        $this->assertStringContainsString('return fn(string $configType = \'service\', string $sourceType = \'arr\'): mixed => $this->container->get(service_id::CONFIG, $configType, $sourceType);', $typedConfigFactorySource);
        $this->assertStringContainsString('new application_infrastructure_config_cache_config_cache_factory_dependencies($container)', $cacheFactorySource);
        $this->assertStringContainsString('new application_infrastructure_config_cache_type_cache_factory_dependencies($container)', $cacheFactorySource);
        $this->assertStringContainsString('return $this->configCacheFactory->configCacheFactory();', $cacheFactorySource);
        $this->assertStringContainsString('return $this->cacheFactory->cacheFactory();', $cacheFactorySource);
        $this->assertStringContainsString('return fn(): mixed => $this->container->get(service_id::CONFIG_CACHE);', $configCacheFactorySource);
        $this->assertStringContainsString('return fn(string $type): mixed => $this->container->get(service_id::CACHE, $type);', $typeCacheFactorySource);
        $this->assertStringContainsString('new application_infrastructure_config_cache_runtime_support_dependencies($container)', $supportSource);
        $this->assertStringContainsString('new application_infrastructure_config_cache_loader_serializer_dependencies($container)', $supportSource);
        $this->assertStringContainsString('new application_infrastructure_config_cache_class_helper_dependencies($container)', $supportSource);
        $this->assertStringContainsString('new application_infrastructure_config_cache_bootstrap_runtime_support_dependencies($container)', $runtimeSupportSource);
        $this->assertStringContainsString('new application_infrastructure_config_cache_error_factory_runtime_support_dependencies($container)', $runtimeSupportSource);
        $this->assertStringContainsString('return $this->bootstrapRuntime->bootstrapRuntime();', $runtimeSupportSource);
        $this->assertStringContainsString('return $this->errorFactory->errorFactory();', $runtimeSupportSource);
        $this->assertStringContainsString('return $this->container->get(service_id::BOOTSTRAP_RUNTIME);', $bootstrapRuntimeSupportSource);
        $this->assertStringContainsString('return fn(): mixed => $this->container->get(service_id::ERROR);', $errorFactoryRuntimeSupportSource);
        $this->assertStringContainsString('new application_infrastructure_config_cache_php_array_file_loader_dependencies($container)', $loaderSerializerSource);
        $this->assertStringContainsString('new application_infrastructure_config_cache_serializer_operations_dependencies($container)', $loaderSerializerSource);
        $this->assertStringContainsString('return $this->phpArrayFileLoader->phpArrayFileLoader();', $loaderSerializerSource);
        $this->assertStringContainsString('return $this->serializerOperations->serializerOperations();', $loaderSerializerSource);
        $this->assertStringContainsString('return $this->container->get(service_id::PHP_ARRAY_FILE_LOADER);', $phpArrayFileLoaderSource);
        $this->assertStringContainsString('return $this->container->get(service_id::SERIALIZER_OPERATIONS);', $serializerOperationsSource);
        $this->assertStringContainsString('return $this->container->get(service_id::SHORT_CLASS_NAME_RESOLVER);', $classHelperSource);
        $this->assertStringContainsString('new application_infrastructure_config_cache_cache_source_file_metadata_storage_dependencies($container)', $storageSource);
        $this->assertStringContainsString('new application_infrastructure_config_cache_config_source_file_storage_dependencies($container)', $storageSource);
        $this->assertStringContainsString('return $this->cacheSourceFileMetadata->cacheSourceFileMetadata();', $storageSource);
        $this->assertStringContainsString('return $this->configSourceFileStorage->configSourceFileStorage();', $storageSource);
        $this->assertStringContainsString('return $this->container->get(service_id::CACHE_SOURCE_FILE_METADATA);', $cacheSourceFileMetadataStorageSource);
        $this->assertStringContainsString('return $this->container->get(service_id::CONFIG_SOURCE_FILE_STORAGE);', $configSourceFileStorageSource);
        $this->assertStringContainsString('new application_infrastructure_config_cache_exception_factory_dependencies($container)', $exceptionSource);
        $this->assertStringContainsString('new application_infrastructure_config_cache_request_header_dependencies($container)', $exceptionSource);
        $this->assertStringContainsString('return $this->exceptionFactory->coreFatalExceptionFactory();', $exceptionSource);
        $this->assertStringContainsString('return $this->requestHeader->headerWriter();', $exceptionSource);
        $this->assertStringContainsString('new application_infrastructure_config_cache_core_fatal_exception_factory_dependencies($container)', $exceptionFactorySource);
        $this->assertStringContainsString('new application_infrastructure_config_cache_error500_exception_factory_dependencies($container)', $exceptionFactorySource);
        $this->assertStringContainsString('return $this->coreFatalExceptionFactory->coreFatalExceptionFactory();', $exceptionFactorySource);
        $this->assertStringContainsString('return $this->error500ExceptionFactory->error500ExceptionFactory();', $exceptionFactorySource);
        $this->assertStringContainsString('return $this->container->get(service_id::CORE_FATAL_EXCEPTION_FACTORY);', $coreFatalExceptionFactorySource);
        $this->assertStringContainsString('return $this->container->get(service_id::ERROR500_EXCEPTION_FACTORY);', $error500ExceptionFactorySource);
        $this->assertStringContainsString('new application_infrastructure_config_cache_request_input_dependencies($container)', $requestHeaderSource);
        $this->assertStringContainsString('new application_infrastructure_config_cache_header_writer_dependencies($container)', $requestHeaderSource);
        $this->assertStringContainsString('return $this->requestInput->requestInput();', $requestHeaderSource);
        $this->assertStringContainsString('return $this->headerWriter->headerWriter();', $requestHeaderSource);
        $this->assertStringContainsString('return $this->container->get(service_id::REQUEST_INPUT);', $requestInputSource);
        $this->assertStringContainsString('return $this->container->get(service_id::HEADER_WRITER);', $headerWriterSource);
        $this->assertStringContainsString('final class application_infrastructure_service_dependencies', $serviceDependenciesSource);
        $this->assertStringContainsString('new application_infrastructure_runtime_dependencies($container)', $serviceDependenciesSource);
        $this->assertStringContainsString('new application_infrastructure_storage_dependencies($container)', $serviceDependenciesSource);
        $this->assertStringContainsString('new application_infrastructure_runtime_error_dependencies($container)', $runtimeSource);
        $this->assertStringContainsString('new application_infrastructure_runtime_config_cache_dependencies($container)', $runtimeSource);
        $this->assertStringContainsString('return $this->runtimeError->bootstrapRuntime();', $runtimeSource);
        $this->assertStringContainsString('return $this->configCache->cacheFactory();', $runtimeSource);
        $this->assertStringContainsString('new application_infrastructure_runtime_error_factory_dependencies($container)', $runtimeErrorSource);
        $this->assertStringContainsString('new application_infrastructure_runtime_bootstrap_runtime_error_dependencies($container)', $runtimeErrorSource);
        $this->assertStringContainsString('return $this->errorFactory->errorFactory();', $runtimeErrorSource);
        $this->assertStringContainsString('return $this->bootstrapRuntime->bootstrapRuntime();', $runtimeErrorSource);
        $this->assertStringContainsString('return fn(): mixed => $this->container->get(service_id::ERROR);', $runtimeErrorFactorySource);
        $this->assertStringContainsString('return $this->container->get(service_id::BOOTSTRAP_RUNTIME);', $runtimeBootstrapRuntimeErrorSource);
        $this->assertStringContainsString('new application_infrastructure_runtime_config_dependencies($container)', $runtimeConfigCacheSource);
        $this->assertStringContainsString('new application_infrastructure_runtime_cache_factory_dependencies($container)', $runtimeConfigCacheSource);
        $this->assertStringContainsString('return $this->config->config();', $runtimeConfigCacheSource);
        $this->assertStringContainsString('return $this->cacheFactory->cacheFactory();', $runtimeConfigCacheSource);
        $this->assertStringContainsString('return $this->container->get(service_id::CONFIG);', $runtimeConfigSource);
        $this->assertStringContainsString('return fn(string $type): mixed => $this->container->get(service_id::CACHE, $type);', $runtimeCacheFactorySource);
        $this->assertStringContainsString('return $this->container->get(service_id::FILE_SYSTEM_STORAGE);', $serviceStorageSource);
    }

    private function containerWithInfrastructureDependencies(
        ?object $config = null,
        ?object $runtime = null,
        ?callable $error500ExceptionFactory = null
    ): container
    {
        $container = new container();
        $config ??= new ApplicationInfrastructureConfigDouble();
        $runtime ??= new ApplicationInfrastructureRuntimeDouble();
        $container
            ->factory('bootstrap_runtime', static fn(): object => $runtime)
            ->factory('config', static fn(): object => $config)
            ->factory('config_cache', static fn(): object => (object)['name' => 'config_cache'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('error', static fn(): object => (object)['name' => 'error'])
            ->factory('request_input', static fn(): object => (object)['name' => 'request-input'])
            ->factory('header_writer', static fn(): object => (object)['name' => 'header-writer'])
            ->factory('core_fatal_exception_factory', static fn(): callable => static fn(string $message, mixed ...$arguments): Throwable => new RuntimeException($message))
            ->factory(
                'error500_exception_factory',
                static fn(): callable => $error500ExceptionFactory
                    ?? static fn(string $message, int $code = E_USER_ERROR, ?Throwable $previous = null): Throwable => new RuntimeException($message, $code, $previous)
            )
            ->factory('php_array_file_loader', static fn(): callable => static fn(string $path, mixed $default = null): mixed => $default)
            ->factory('serializer_operations', static fn(): object => new ApplicationInfrastructureSerializerOperationsDouble())
            ->factory('short_class_name_resolver', static fn(): callable => static fn(object|string $object): string => is_object($object) ? get_class($object) : $object)
            ->factory('cache_source_file_metadata', static fn(): object => (object)['name' => 'cache_source_file_metadata'])
            ->factory('config_source_file_storage', static fn(): object => (object)['name' => 'config_source_file_storage'])
            ->factory('file_system_storage', static fn(): object => (object)['name' => 'file_system_storage']);

        return $container;
    }
}

final class ApplicationInfrastructureStateDouble
{
    private array $instances = [];

    public function getInstance(mixed $key): mixed
    {
        return $this->instances[$this->stateKey($key)] ?? null;
    }

    public function setInstance(mixed $key, mixed $instance): void
    {
        $this->instances[$this->stateKey($key)] = $instance;
    }

    public function hasInstances(): bool
    {
        return $this->instances !== [];
    }

    private function stateKey(mixed $key): string
    {
        return is_bool($key) ? ($key ? 'true' : 'false') : (string)$key;
    }
}

final class ApplicationInfrastructureRuntimeDouble
{
    public array $loggedErrors = [];

    private $serviceExceptionFactory;

    public function __construct(?callable $serviceExceptionFactory = null)
    {
        $this->serviceExceptionFactory = $serviceExceptionFactory ?? static fn(): \Throwable => new RuntimeException('service exception');
    }

    public function parsePath(string $path): string
    {
        return '/parsed/' . $path;
    }

    public function logError(string $message): void
    {
        $this->loggedErrors[] = $message;
    }

    public function serviceExceptionFactory(): callable
    {
        return $this->serviceExceptionFactory;
    }
}

final class ApplicationInfrastructureConfigDouble
{
    public function __construct(private string $defaultCacheType = 'runtime')
    {
    }

    public function get(string $section): object
    {
        return new class($this->defaultCacheType) {
            public function __construct(private string $defaultCacheType)
            {
            }

            public function get(string $key): string
            {
                return $this->defaultCacheType;
            }
        };
    }
}

final class ApplicationInfrastructureSerializerOperationsDouble
{
    public function phpSnapshotEncoder(): callable
    {
        return static fn(mixed $value): string => serialize($value);
    }

    public function phpSnapshotDecoder(): callable
    {
        return static fn(string $value): mixed => unserialize($value);
    }
}

final class ApplicationInfrastructureCacheDouble
{
    public function get(string $key): object
    {
        return (object)['key' => $key];
    }
}
