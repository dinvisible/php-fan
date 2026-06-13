<?php

declare(strict_types=1);

use fan\core\di\application_core_service_creator;
use fan\core\di\container;
use PHPUnit\Framework\TestCase;
use fan\project\service\application;
use fan\project\service\debug;
use fan\project\service\header;
use fan\project\service\locale;
use fan\project\service\reflector;
use fan\project\service\request;
use fan\project\service\role;


final class ApplicationCoreServiceCreatorTest extends TestCase
{
    public function testProjectServiceClassAvailabilityCheckIsInjected(): void
    {
        $checkedClasses = [];
        $factoryCalled = false;
        $creator = new application_core_service_creator(
            static function (string $className) use (&$checkedClasses): bool {
                $checkedClasses[] = $className;

                return false;
            }
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Service "request" does not expose a project class.');

        try {
            $creator->createRequestService(
                new container(),
                static function () use (&$factoryCalled): object {
                    $factoryCalled = true;

                    return (object)['service' => 'request'];
                }
            );
        } finally {
            $this->assertSame(['\\' . request::class], $checkedClasses);
            $this->assertFalse($factoryCalled);
        }
    }

    public function testRequestServiceCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = new container();
        $container
            ->factory('request_input', static fn(): object => (object)['name' => 'request_input'])
            ->factory('bootstrap_runtime', static fn(): object => (object)['name' => 'runtime'])
            ->factory('config', static fn(): object => (object)['name' => 'config'])
            ->factory('json', static fn(container $container, bool $useBase64 = false): object => (object)['useBase64' => $useBase64], false)
            ->factory('cookie', static fn(): object => (object)['name' => 'cookie'])
            ->factory('matcher', static fn(): object => (object)['name' => 'matcher'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('array_adducer', static fn(): callable => static fn(mixed $value): array => is_array($value) ? $value : [$value])
            ->factory('recursive_merger', static fn(): callable => static fn(array $left, array $right): array => array_replace_recursive($left, $right))
            ->factory('array_value_reader', static fn(): callable => static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default)
            ->factory('class_name_resolver', static fn(): callable => static fn(object $object): string => get_class($object));
        $received = [];

        $service = (new application_core_service_creator())->createRequestService(
            $container,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'request'];
            }
        );

        $this->assertSame('request', $service->service);
        $this->assertSame('\\' . request::class, $received[0] ?? null);
        $this->assertSame($container->get('request_input'), $received[1] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[2] ?? null);
        $this->assertSame('cache-key', ($received[8])('cache-key')->type);
        $this->assertSame($container->get('array_adducer'), $received[9] ?? null);
        $this->assertSame($container->get('recursive_merger'), $received[10] ?? null);
        $this->assertSame($container->get('array_value_reader'), $received[11] ?? null);
        $this->assertSame($container->get('class_name_resolver'), $received[12] ?? null);
    }

    public function testCoreCreatorUsesCommonDependencyBundle(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_service_creator.php');
        $bundleSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_creator_common_dependencies.php');
        $commonBootstrapSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_creator_bootstrap_runtime_dependencies.php');
        $commonConfigCacheSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_creator_config_cache_dependencies.php');
        $commonConfigSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_creator_config_dependencies.php');
        $commonCacheFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_creator_cache_factory_dependencies.php');
        $coreBundleSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_service_dependencies.php');
        $coreRequestSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_request_dependencies.php');
        $coreRequestFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_request_factory_dependencies.php');
        $coreRequestInputFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_request_input_factory_dependencies.php');
        $coreRequestRuntimeFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_request_runtime_factory_dependencies.php');
        $coreRequestMatcherFactoryRuntimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_request_matcher_factory_runtime_dependencies.php');
        $coreRequestRequestFactoryRuntimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_request_request_factory_runtime_dependencies.php');
        $coreRequestTransportFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_request_transport_factory_dependencies.php');
        $coreRequestJsonTransportFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_request_json_transport_factory_dependencies.php');
        $coreRequestCookieTransportFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_request_cookie_transport_factory_dependencies.php');
        $coreRequestHelperSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_request_helper_dependencies.php');
        $coreRequestArrayTransformHelperSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_request_array_transform_helper_dependencies.php');
        $coreRequestArrayAdducerTransformHelperSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_request_array_adducer_transform_helper_dependencies.php');
        $coreRequestRecursiveMergerTransformHelperSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_request_recursive_merger_transform_helper_dependencies.php');
        $coreRequestArrayReadClassHelperSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_request_array_read_class_helper_dependencies.php');
        $coreUserSessionSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_user_session_dependencies.php');
        $coreUserIdentitySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_user_identity_dependencies.php');
        $coreUserCurrentIdentitySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_user_current_identity_dependencies.php');
        $coreUserSessionSpaceSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_user_session_space_dependencies.php');
        $coreUserSessionFactorySessionSpaceSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_user_session_factory_session_space_dependencies.php');
        $coreUserCurrentUserSpaceFactorySessionSpaceSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_user_current_user_space_factory_session_space_dependencies.php');
        $coreUserDataFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_user_data_factory_dependencies.php');
        $coreUserDateDataFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_user_date_data_factory_dependencies.php');
        $coreUserEntityDataFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_user_entity_data_factory_dependencies.php');
        $coreProjectSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_project_dependencies.php');
        $coreProjectErrorSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_project_error_dependencies.php');
        $coreProjectErrorServiceSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_project_error_service_dependencies.php');
        $coreProjectErrorContextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_project_error_context_dependencies.php');
        $coreProjectErrorFactoryServiceSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_project_error_factory_service_dependencies.php');
        $coreProjectErrorStorageSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_project_error_storage_dependencies.php');
        $coreProjectErrorLogWriterStorageSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_project_error_log_writer_storage_dependencies.php');
        $coreProjectErrorFileStorageSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_project_error_file_storage_dependencies.php');
        $coreProjectStorageSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_project_storage_dependencies.php');
        $coreProjectReflectionMetaSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_project_reflection_meta_dependencies.php');
        $coreProjectReflectionClassFactoryMetaSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_project_reflection_class_factory_meta_dependencies.php');
        $coreProjectMetaFileStorageSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_project_meta_file_storage_dependencies.php');
        $coreProjectResponseLoaderSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_project_response_loader_dependencies.php');
        $coreProjectHeaderWriterResponseLoaderSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_project_header_writer_response_loader_dependencies.php');
        $coreProjectPhpArrayFileLoaderResponseLoaderSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_project_php_array_file_loader_response_loader_dependencies.php');
        $coreProjectTabSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_project_tab_dependencies.php');
        $coreProjectTabContextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_project_tab_context_dependencies.php');
        $coreProjectTabServiceContextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_project_tab_service_context_dependencies.php');
        $coreProjectTabInstanceServiceContextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_project_tab_instance_service_context_dependencies.php');
        $coreProjectTabFactoryServiceContextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_project_tab_factory_service_context_dependencies.php');
        $coreProjectLocaleContextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_project_locale_context_dependencies.php');
        $coreProjectApplicationContextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_project_application_context_dependencies.php');
        $coreProjectRouteStorageSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_project_route_storage_dependencies.php');
        $coreRequestArrayValueReaderHelperSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_request_array_value_reader_helper_dependencies.php');
        $coreRequestClassNameResolverHelperSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_request_class_name_resolver_helper_dependencies.php');

        $this->assertIsString($source);
        $this->assertIsString($bundleSource);
        $this->assertIsString($commonBootstrapSource);
        $this->assertIsString($commonConfigCacheSource);
        $this->assertIsString($commonConfigSource);
        $this->assertIsString($commonCacheFactorySource);
        $this->assertIsString($coreBundleSource);
        $this->assertIsString($coreRequestSource);
        $this->assertIsString($coreRequestFactorySource);
        $this->assertIsString($coreRequestInputFactorySource);
        $this->assertIsString($coreRequestRuntimeFactorySource);
        $this->assertIsString($coreRequestMatcherFactoryRuntimeSource);
        $this->assertIsString($coreRequestRequestFactoryRuntimeSource);
        $this->assertIsString($coreRequestTransportFactorySource);
        $this->assertIsString($coreRequestJsonTransportFactorySource);
        $this->assertIsString($coreRequestCookieTransportFactorySource);
        $this->assertIsString($coreRequestHelperSource);
        $this->assertIsString($coreRequestArrayTransformHelperSource);
        $this->assertIsString($coreRequestArrayAdducerTransformHelperSource);
        $this->assertIsString($coreRequestRecursiveMergerTransformHelperSource);
        $this->assertIsString($coreRequestArrayReadClassHelperSource);
        $this->assertIsString($coreUserSessionSource);
        $this->assertIsString($coreUserIdentitySource);
        $this->assertIsString($coreUserCurrentIdentitySource);
        $this->assertIsString($coreUserSessionSpaceSource);
        $this->assertIsString($coreUserSessionFactorySessionSpaceSource);
        $this->assertIsString($coreUserCurrentUserSpaceFactorySessionSpaceSource);
        $this->assertIsString($coreUserDataFactorySource);
        $this->assertIsString($coreUserDateDataFactorySource);
        $this->assertIsString($coreUserEntityDataFactorySource);
        $this->assertIsString($coreProjectSource);
        $this->assertIsString($coreProjectErrorSource);
        $this->assertIsString($coreProjectErrorServiceSource);
        $this->assertIsString($coreProjectErrorContextSource);
        $this->assertIsString($coreProjectErrorFactoryServiceSource);
        $this->assertIsString($coreProjectErrorStorageSource);
        $this->assertIsString($coreProjectErrorLogWriterStorageSource);
        $this->assertIsString($coreProjectErrorFileStorageSource);
        $this->assertIsString($coreProjectStorageSource);
        $this->assertIsString($coreProjectReflectionMetaSource);
        $this->assertIsString($coreProjectReflectionClassFactoryMetaSource);
        $this->assertIsString($coreProjectMetaFileStorageSource);
        $this->assertIsString($coreProjectResponseLoaderSource);
        $this->assertIsString($coreProjectHeaderWriterResponseLoaderSource);
        $this->assertIsString($coreProjectPhpArrayFileLoaderResponseLoaderSource);
        $this->assertIsString($coreProjectTabSource);
        $this->assertIsString($coreProjectTabContextSource);
        $this->assertIsString($coreProjectTabServiceContextSource);
        $this->assertIsString($coreProjectTabInstanceServiceContextSource);
        $this->assertIsString($coreProjectTabFactoryServiceContextSource);
        $this->assertIsString($coreProjectLocaleContextSource);
        $this->assertIsString($coreProjectApplicationContextSource);
        $this->assertIsString($coreProjectRouteStorageSource);
        $this->assertIsString($coreRequestArrayValueReaderHelperSource);
        $this->assertIsString($coreRequestClassNameResolverHelperSource);
        $this->assertStringContainsString('private function commonDependencies(container_interface $container): application_creator_common_dependencies', $source);
        $this->assertStringContainsString('return new application_creator_common_dependencies($container);', $source);
        $this->assertStringContainsString('private function coreDependencies(container_interface $container): application_core_service_dependencies', $source);
        $this->assertStringContainsString('return new application_core_service_dependencies($container);', $source);
        $this->assertStringContainsString('$common = $this->commonDependencies($container);', $source);
        $this->assertStringContainsString('$coreDependencies = $this->coreDependencies($container);', $source);
        $this->assertStringContainsString('$common->bootstrapRuntime()', $source);
        $this->assertStringContainsString('$common->config()', $source);
        $this->assertStringContainsString('$common->cacheFactory()', $source);
        $this->assertStringContainsString('$coreDependencies->requestInput()', $source);
        $this->assertStringContainsString('$coreDependencies->arrayAdducer()', $source);
        $this->assertStringContainsString('$coreDependencies->reflectionClassFactory()', $source);
        $this->assertStringContainsString('final class application_creator_common_dependencies', $bundleSource);
        $this->assertStringContainsString('new application_creator_bootstrap_runtime_dependencies($container)', $bundleSource);
        $this->assertStringContainsString('new application_creator_config_cache_dependencies($container)', $bundleSource);
        $this->assertStringContainsString('return $this->bootstrap->bootstrapRuntime();', $bundleSource);
        $this->assertStringContainsString('return $this->configCache->cacheFactory();', $bundleSource);
        $this->assertStringContainsString('return $this->container->get(service_id::BOOTSTRAP_RUNTIME);', $commonBootstrapSource);
        $this->assertStringContainsString('new application_creator_config_dependencies($container)', $commonConfigCacheSource);
        $this->assertStringContainsString('new application_creator_cache_factory_dependencies($container)', $commonConfigCacheSource);
        $this->assertStringContainsString('return $this->config->config();', $commonConfigCacheSource);
        $this->assertStringContainsString('return $this->cacheFactory->cacheFactory();', $commonConfigCacheSource);
        $this->assertStringContainsString('return $this->container->get(service_id::CONFIG);', $commonConfigSource);
        $this->assertStringContainsString('return fn(string $type): mixed => $this->container->get(service_id::CACHE, $type);', $commonCacheFactorySource);
        $this->assertStringContainsString('final class application_core_service_dependencies', $coreBundleSource);
        $this->assertStringContainsString('new application_core_request_dependencies($container)', $coreBundleSource);
        $this->assertStringContainsString('new application_core_user_session_dependencies($container)', $coreBundleSource);
        $this->assertStringContainsString('new application_core_project_dependencies($container)', $coreBundleSource);
        $this->assertStringContainsString('new application_core_request_factory_dependencies($container)', $coreRequestSource);
        $this->assertStringContainsString('new application_core_request_helper_dependencies($container)', $coreRequestSource);
        $this->assertStringContainsString('new application_core_project_error_dependencies($container)', $coreProjectSource);
        $this->assertStringContainsString('new application_core_project_storage_dependencies($container)', $coreProjectSource);
        $this->assertStringContainsString('new application_core_project_tab_dependencies($container)', $coreProjectSource);
        $this->assertStringContainsString('new application_core_project_error_service_dependencies($container)', $coreProjectErrorSource);
        $this->assertStringContainsString('new application_core_project_error_storage_dependencies($container)', $coreProjectErrorSource);
        $this->assertStringContainsString('return $this->service->error();', $coreProjectErrorSource);
        $this->assertStringContainsString('return $this->storage->errorFileStorage();', $coreProjectErrorSource);
        $this->assertStringContainsString('new application_core_project_error_context_dependencies($container)', $coreProjectErrorServiceSource);
        $this->assertStringContainsString('new application_core_project_error_factory_service_dependencies($container)', $coreProjectErrorServiceSource);
        $this->assertStringContainsString('return $this->error->error();', $coreProjectErrorServiceSource);
        $this->assertStringContainsString('return $this->errorFactory->errorFactory();', $coreProjectErrorServiceSource);
        $this->assertStringContainsString('return $this->container->get(service_id::ERROR);', $coreProjectErrorContextSource);
        $this->assertStringContainsString('return fn(): mixed => $this->container->get(service_id::ERROR);', $coreProjectErrorFactoryServiceSource);
        $this->assertStringContainsString('new application_core_project_error_log_writer_storage_dependencies($container)', $coreProjectErrorStorageSource);
        $this->assertStringContainsString('new application_core_project_error_file_storage_dependencies($container)', $coreProjectErrorStorageSource);
        $this->assertStringContainsString('return $this->errorLogWriter->errorLogWriter();', $coreProjectErrorStorageSource);
        $this->assertStringContainsString('return $this->errorFileStorage->errorFileStorage();', $coreProjectErrorStorageSource);
        $this->assertStringContainsString('return $this->container->get(service_id::ERROR_LOG_WRITER);', $coreProjectErrorLogWriterStorageSource);
        $this->assertStringContainsString('return $this->container->get(service_id::ERROR_FILE_STORAGE);', $coreProjectErrorFileStorageSource);
        $this->assertStringContainsString('new application_core_project_reflection_meta_dependencies($container)', $coreProjectStorageSource);
        $this->assertStringContainsString('new application_core_project_response_loader_dependencies($container)', $coreProjectStorageSource);
        $this->assertStringContainsString('return $this->reflectionMeta->reflectionClassFactory();', $coreProjectStorageSource);
        $this->assertStringContainsString('return $this->responseLoader->phpArrayFileLoader();', $coreProjectStorageSource);
        $this->assertStringContainsString('new application_core_request_input_factory_dependencies($container)', $coreRequestFactorySource);
        $this->assertStringContainsString('new application_core_request_transport_factory_dependencies($container)', $coreRequestFactorySource);
        $this->assertStringContainsString('new application_core_request_runtime_factory_dependencies($container)', $coreRequestFactorySource);
        $this->assertStringContainsString('return $this->container->get(service_id::REQUEST_INPUT);', $coreRequestInputFactorySource);
        $this->assertStringContainsString('new application_core_request_matcher_factory_runtime_dependencies($container)', $coreRequestRuntimeFactorySource);
        $this->assertStringContainsString('new application_core_request_request_factory_runtime_dependencies($container)', $coreRequestRuntimeFactorySource);
        $this->assertStringContainsString('return $this->matcherFactory->matcherFactory();', $coreRequestRuntimeFactorySource);
        $this->assertStringContainsString('return $this->requestFactory->requestFactory();', $coreRequestRuntimeFactorySource);
        $this->assertStringContainsString('return fn(): mixed => $this->container->get(service_id::MATCHER);', $coreRequestMatcherFactoryRuntimeSource);
        $this->assertStringContainsString('return fn(): mixed => $this->container->get(service_id::REQUEST);', $coreRequestRequestFactoryRuntimeSource);
        $this->assertStringContainsString('new application_core_request_json_transport_factory_dependencies($container)', $coreRequestTransportFactorySource);
        $this->assertStringContainsString('new application_core_request_cookie_transport_factory_dependencies($container)', $coreRequestTransportFactorySource);
        $this->assertStringContainsString('return $this->jsonFactory->jsonFactory();', $coreRequestTransportFactorySource);
        $this->assertStringContainsString('return $this->cookieFactory->cookieFactory();', $coreRequestTransportFactorySource);
        $this->assertStringContainsString('return fn(bool $useBase64 = false): mixed => $this->container->get(service_id::JSON, $useBase64);', $coreRequestJsonTransportFactorySource);
        $this->assertStringContainsString('return fn(mixed $path = null, mixed $domain = null): mixed => $this->container->get(service_id::COOKIE, $path, $domain);', $coreRequestCookieTransportFactorySource);
        $this->assertStringContainsString('new application_core_request_array_transform_helper_dependencies($container)', $coreRequestHelperSource);
        $this->assertStringContainsString('new application_core_request_array_read_class_helper_dependencies($container)', $coreRequestHelperSource);
        $this->assertStringContainsString('return $this->arrayTransform->arrayAdducer();', $coreRequestHelperSource);
        $this->assertStringContainsString('return $this->arrayReadClass->classNameResolver();', $coreRequestHelperSource);
        $this->assertStringContainsString('new application_core_request_array_adducer_transform_helper_dependencies($container)', $coreRequestArrayTransformHelperSource);
        $this->assertStringContainsString('new application_core_request_recursive_merger_transform_helper_dependencies($container)', $coreRequestArrayTransformHelperSource);
        $this->assertStringContainsString('return $this->arrayAdducer->arrayAdducer();', $coreRequestArrayTransformHelperSource);
        $this->assertStringContainsString('return $this->recursiveMerger->recursiveMerger();', $coreRequestArrayTransformHelperSource);
        $this->assertStringContainsString('new application_core_request_array_value_reader_helper_dependencies($container)', $coreRequestArrayReadClassHelperSource);
        $this->assertStringContainsString('new application_core_request_class_name_resolver_helper_dependencies($container)', $coreRequestArrayReadClassHelperSource);
        $this->assertStringContainsString('return $this->arrayValueReader->arrayValueReader();', $coreRequestArrayReadClassHelperSource);
        $this->assertStringContainsString('return $this->classNameResolver->classNameResolver();', $coreRequestArrayReadClassHelperSource);
        $this->assertStringContainsString('return $this->container->get(service_id::ARRAY_ADDUCER);', $coreRequestArrayAdducerTransformHelperSource);
        $this->assertStringContainsString('return $this->container->get(service_id::RECURSIVE_MERGER);', $coreRequestRecursiveMergerTransformHelperSource);
        $this->assertStringContainsString('return $this->container->get(service_id::ARRAY_VALUE_READER);', $coreRequestArrayValueReaderHelperSource);
        $this->assertStringContainsString('return $this->container->get(service_id::CLASS_NAME_RESOLVER);', $coreRequestClassNameResolverHelperSource);
        $this->assertStringContainsString('new application_core_user_identity_dependencies($container)', $coreUserSessionSource);
        $this->assertStringContainsString('new application_core_user_data_factory_dependencies($container)', $coreUserSessionSource);
        $this->assertStringContainsString('new application_core_user_current_identity_dependencies($container)', $coreUserIdentitySource);
        $this->assertStringContainsString('new application_core_user_session_space_dependencies($container)', $coreUserIdentitySource);
        $this->assertStringContainsString('return $this->currentUser->currentUserFactory();', $coreUserIdentitySource);
        $this->assertStringContainsString('return $this->sessionSpace->currentUserSpaceFactory();', $coreUserIdentitySource);
        $this->assertStringContainsString('return fn(bool $checkLogout): mixed => $this->container->get($checkLogout ? service_id::CURRENT_USER_CHECKED : service_id::CURRENT_USER);', $coreUserCurrentIdentitySource);
        $this->assertStringContainsString('new application_core_user_session_factory_session_space_dependencies($container)', $coreUserSessionSpaceSource);
        $this->assertStringContainsString('new application_core_user_current_user_space_factory_session_space_dependencies($container)', $coreUserSessionSpaceSource);
        $this->assertStringContainsString('return $this->sessionFactory->sessionFactory();', $coreUserSessionSpaceSource);
        $this->assertStringContainsString('return $this->currentUserSpaceFactory->currentUserSpaceFactory();', $coreUserSessionSpaceSource);
        $this->assertStringContainsString('return fn(string $namespace, string $group): mixed => $this->container->get(service_id::SESSION, $namespace, $group);', $coreUserSessionFactorySessionSpaceSource);
        $this->assertStringContainsString('return fn(): mixed => $this->container->get(service_id::CURRENT_USER_SPACE);', $coreUserCurrentUserSpaceFactorySessionSpaceSource);
        $this->assertStringContainsString('new application_core_user_date_data_factory_dependencies($container)', $coreUserDataFactorySource);
        $this->assertStringContainsString('new application_core_user_entity_data_factory_dependencies($container)', $coreUserDataFactorySource);
        $this->assertStringContainsString('return $this->dateFactory->dateFactory();', $coreUserDataFactorySource);
        $this->assertStringContainsString('return $this->entityFactory->entityFactory();', $coreUserDataFactorySource);
        $this->assertStringContainsString('return fn(?string $date = null, mixed $format = null): mixed => $this->container->get(service_id::DATE, $date, $format);', $coreUserDateDataFactorySource);
        $this->assertStringContainsString('return fn(): mixed => $this->container->get(service_id::ENTITY);', $coreUserEntityDataFactorySource);
        $this->assertStringContainsString('new application_core_project_tab_context_dependencies($container)', $coreProjectTabSource);
        $this->assertStringContainsString('new application_core_project_application_context_dependencies($container)', $coreProjectTabSource);
        $this->assertStringContainsString('new application_core_project_route_storage_dependencies($container)', $coreProjectTabSource);
        $this->assertStringContainsString('new application_core_project_tab_service_context_dependencies($container)', $coreProjectTabContextSource);
        $this->assertStringContainsString('new application_core_project_locale_context_dependencies($container)', $coreProjectTabContextSource);
        $this->assertStringContainsString('return $this->tabService->tabFactory();', $coreProjectTabContextSource);
        $this->assertStringContainsString('return $this->localeContext->locale();', $coreProjectTabContextSource);
        $this->assertStringContainsString('new application_core_project_tab_instance_service_context_dependencies($container)', $coreProjectTabServiceContextSource);
        $this->assertStringContainsString('new application_core_project_tab_factory_service_context_dependencies($container)', $coreProjectTabServiceContextSource);
        $this->assertStringContainsString('return $this->tab->tab();', $coreProjectTabServiceContextSource);
        $this->assertStringContainsString('return $this->tabFactory->tabFactory();', $coreProjectTabServiceContextSource);
        $this->assertStringContainsString('return $this->container->get(service_id::TAB);', $coreProjectTabInstanceServiceContextSource);
        $this->assertStringContainsString('return fn(): mixed => $this->container->get(service_id::TAB);', $coreProjectTabFactoryServiceContextSource);
        $this->assertStringContainsString('return $this->container->get(service_id::LOCALE);', $coreProjectLocaleContextSource);
        $this->assertStringContainsString('return $this->container->get(service_id::APPLICATION);', $coreProjectApplicationContextSource);
        $this->assertStringContainsString('return $this->container->get(service_id::MATCHER_ROUTE_FILE_STORAGE);', $coreProjectRouteStorageSource);
        $this->assertStringContainsString('new application_core_project_reflection_class_factory_meta_dependencies($container)', $coreProjectReflectionMetaSource);
        $this->assertStringContainsString('new application_core_project_meta_file_storage_dependencies($container)', $coreProjectReflectionMetaSource);
        $this->assertStringContainsString('return $this->reflectionClassFactory->reflectionClassFactory();', $coreProjectReflectionMetaSource);
        $this->assertStringContainsString('return $this->metaFileStorage->metaFileStorage();', $coreProjectReflectionMetaSource);
        $this->assertStringContainsString('return $this->container->get(service_id::REFLECTION_CLASS_FACTORY);', $coreProjectReflectionClassFactoryMetaSource);
        $this->assertStringContainsString('return $this->container->get(service_id::META_FILE_STORAGE);', $coreProjectMetaFileStorageSource);
        $this->assertStringContainsString('new application_core_project_header_writer_response_loader_dependencies($container)', $coreProjectResponseLoaderSource);
        $this->assertStringContainsString('new application_core_project_php_array_file_loader_response_loader_dependencies($container)', $coreProjectResponseLoaderSource);
        $this->assertStringContainsString('return $this->headerWriter->headerWriter();', $coreProjectResponseLoaderSource);
        $this->assertStringContainsString('return $this->phpArrayFileLoader->phpArrayFileLoader();', $coreProjectResponseLoaderSource);
        $this->assertStringContainsString('return $this->container->get(service_id::HEADER_WRITER);', $coreProjectHeaderWriterResponseLoaderSource);
        $this->assertStringContainsString('return $this->container->get(service_id::PHP_ARRAY_FILE_LOADER);', $coreProjectPhpArrayFileLoaderResponseLoaderSource);
    }

    public function testRoleServiceCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = new container();
        $container
            ->factory('current_user', static fn(): object => (object)['name' => 'current_user'])
            ->factory('current_user_checked', static fn(): object => (object)['name' => 'current_user_checked'])
            ->factory('session', static fn(container $container, string $namespace, string $group): object => (object)['namespace' => $namespace, 'group' => $group], false)
            ->factory('error', static fn(): object => (object)['name' => 'error'])
            ->factory('current_user_space', static fn(): string => 'frontend')
            ->factory('date', static fn(container $container, string $date, mixed $format = null): object => (object)['date' => $date, 'format' => $format], false)
            ->factory('bootstrap_runtime', static fn(): object => (object)['name' => 'runtime'])
            ->factory('config', static fn(): object => (object)['name' => 'config'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false);
        $received = [];

        $service = (new application_core_service_creator())->createRoleService(
            $container,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'role'];
            }
        );

        $this->assertSame('role', $service->service);
        $this->assertSame('\\' . role::class, $received[0] ?? null);
        $this->assertSame('current_user_checked', ($received[1])(true)->name);
        $this->assertSame('main', ($received[2])('main', 'custom')->namespace);
        $this->assertSame('frontend', ($received[4])());
        $this->assertSame('cache-key', ($received[8])('cache-key')->type);
    }

    public function testApplicationServiceCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = new container();
        $container
            ->factory('bootstrap_runtime', static fn(): object => (object)['name' => 'runtime'])
            ->factory('config', static fn(): object => (object)['name' => 'config'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('array_adducer', static fn(): callable => static fn(mixed $value): array => is_array($value) ? $value : [$value]);
        $received = [];

        $service = (new application_core_service_creator())->createApplicationService(
            $container,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'application'];
            }
        );

        $this->assertSame('application', $service->service);
        $this->assertSame('\\' . application::class, $received[0] ?? null);
        $this->assertTrue($received[1] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[2] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[3] ?? null);
        $this->assertSame($container->get('config'), $received[4] ?? null);
        $this->assertSame('cache-key', ($received[5])('cache-key')->type);
        $this->assertSame($container->get('array_adducer'), $received[6] ?? null);
    }

    public function testDebugServiceCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = new container();
        $container
            ->factory('tab', static fn(): object => (object)['name' => 'tab'])
            ->factory('request_input', static fn(): object => (object)['name' => 'request_input'])
            ->factory('bootstrap_runtime', static fn(): object => (object)['name' => 'runtime'])
            ->factory('config', static fn(): object => (object)['name' => 'config'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('meta_file_storage', static fn(): object => (object)['name' => 'meta_file_storage'])
            ->factory('array_adducer', static fn(): callable => static fn(mixed $value): array => is_array($value) ? $value : [$value])
            ->factory('reflection_class_factory', static fn(): object => (object)['name' => 'reflection_class_factory']);
        $received = [];

        $service = (new application_core_service_creator())->createDebugService(
            $container,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'debug'];
            }
        );

        $this->assertSame('debug', $service->service);
        $this->assertSame('\\' . debug::class, $received[0] ?? null);
        $this->assertTrue($received[1] ?? null);
        $this->assertSame($container->get('tab'), $received[2] ?? null);
        $this->assertSame($container->get('request_input'), $received[3] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[4] ?? null);
        $this->assertSame($container->get('config'), $received[5] ?? null);
        $this->assertSame('cache-key', ($received[6])('cache-key')->type);
        $this->assertSame($container->get('meta_file_storage'), $received[7] ?? null);
        $this->assertSame($container->get('array_adducer'), $received[8] ?? null);
        $this->assertSame($container->get('reflection_class_factory'), $received[9] ?? null);
    }

    public function testReflectorServiceCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = new container();
        $container
            ->factory('bootstrap_runtime', static fn(): object => (object)['name' => 'runtime'])
            ->factory('config', static fn(): object => (object)['name' => 'config'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('reflection_class_factory', static fn(): object => (object)['name' => 'reflection_class_factory']);
        $received = [];

        $service = (new application_core_service_creator())->createReflectorService(
            $container,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'reflector'];
            }
        );

        $this->assertSame('reflector', $service->service);
        $this->assertSame('\\' . reflector::class, $received[0] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[1] ?? null);
        $this->assertSame($container->get('config'), $received[2] ?? null);
        $this->assertSame('cache-key', ($received[3])('cache-key')->type);
        $this->assertSame($container->get('reflection_class_factory'), $received[4] ?? null);
        $this->assertArrayNotHasKey(5, $received);
    }

    public function testLocaleServiceCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = new container();
        $container
            ->factory('entity', static fn(): object => (object)['name' => 'entity'])
            ->factory('tab', static fn(): object => (object)['name' => 'tab'])
            ->factory('session', static fn(container $container, string $namespace, string $group): object => (object)['namespace' => $namespace, 'group' => $group], false)
            ->factory('request', static fn(): object => (object)['name' => 'request'])
            ->factory('cookie', static fn(container $container, mixed $path = null, mixed $domain = null): object => (object)['path' => $path, 'domain' => $domain], false)
            ->factory('matcher', static fn(): object => (object)['name' => 'matcher'])
            ->factory('bootstrap_runtime', static fn(): object => (object)['name' => 'runtime'])
            ->factory('config', static fn(): object => (object)['name' => 'config'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('array_adducer', static fn(): callable => static fn(mixed $value): array => is_array($value) ? $value : [$value])
            ->factory('class_name_resolver', static fn(): callable => static fn(object $object): string => get_class($object));
        $received = [];

        $service = (new application_core_service_creator())->createLocaleService(
            $container,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'locale'];
            }
        );

        $this->assertSame('locale', $service->service);
        $this->assertSame('\\' . locale::class, $received[0] ?? null);
        $this->assertSame($container->get('entity'), ($received[2])());
        $this->assertSame($container->get('tab'), ($received[3])());
        $this->assertSame('locale', ($received[4])('locale', 'service')->namespace);
        $this->assertSame($container->get('request'), ($received[5])());
        $this->assertSame('/', ($received[6])('/', null)->path);
        $this->assertSame($container->get('matcher'), ($received[7])());
        $this->assertSame($container->get('bootstrap_runtime'), $received[8] ?? null);
        $this->assertSame($container->get('config'), $received[9] ?? null);
        $this->assertSame('runtime', ($received[10])('runtime')->type);
        $this->assertSame($container->get('array_adducer'), $received[11] ?? null);
        $this->assertSame($container->get('class_name_resolver'), $received[12] ?? null);
    }

    public function testHeaderServiceCreatorPassesRecursiveMergerToInjectedFactory(): void
    {
        $container = new container();
        $container
            ->factory('request_input', static fn(): object => (object)['name' => 'request_input'])
            ->factory('header_writer', static fn(): object => (object)['name' => 'header_writer'])
            ->factory('bootstrap_runtime', static fn(): object => (object)['name' => 'runtime'])
            ->factory('config', static fn(): object => (object)['name' => 'config'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('recursive_merger', static fn(): callable => static fn(array $left, array $right): array => array_replace_recursive($left, $right));
        $received = [];

        $service = (new application_core_service_creator())->createHeaderService(
            $container,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'header'];
            }
        );

        $this->assertSame('header', $service->service);
        $this->assertSame('\\' . header::class, $received[0] ?? null);
        $this->assertTrue($received[1] ?? null);
        $this->assertSame($container->get('request_input'), $received[2] ?? null);
        $this->assertSame($container->get('header_writer'), $received[3] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[4] ?? null);
        $this->assertSame($container->get('config'), $received[5] ?? null);
        $this->assertSame('cache-key', ($received[6])('cache-key')->type);
        $this->assertSame($container->get('recursive_merger'), $received[7] ?? null);
    }
}
