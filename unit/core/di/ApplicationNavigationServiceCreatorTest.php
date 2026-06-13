<?php

declare(strict_types=1);

use fan\core\di\application_navigation_service_creator;
use fan\core\di\container;
use PHPUnit\Framework\TestCase;
use fan\project\service\tab;


final class ApplicationNavigationServiceCreatorTest extends TestCase
{    public function testTabCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = $this->containerWithTabDependencies();
        $tabDelegateFactory = static fn(): object => (object)['name' => 'tab_delegate_factory'];
        $tabViewParserFactory = static fn(): object => (object)['name' => 'tab_view_parser_factory'];
        $received = [];

        $tab = (new application_navigation_service_creator())->createTabService(
            $container,
            $tabDelegateFactory,
            $tabViewParserFactory,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return new ApplicationNavigationServiceCreatorTabProbe();
            }
        );

        $this->assertInstanceOf(ApplicationNavigationServiceCreatorTabProbe::class, $tab);
        $this->assertSame($container->get('upload_size_limit_provider'), $tab->uploadSizeLimitProvider);
        $this->assertSame('\\' . tab::class, $received[0] ?? null);
        $this->assertTrue($received[1] ?? null);
        $this->assertSame($container->get('matcher'), $received[2] ?? null);
        $this->assertSame($container->get('request'), $received[3] ?? null);
        $this->assertSame('member', ($received[5])('member', 'custom')->namespace);
        $this->assertSame($container->get('request_input'), $received[6] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[7] ?? null);
        $this->assertSame($container->get('role'), ($received[8])());
        $this->assertSame($container->get('transfer'), ($received[9])());
        $this->assertSame('service', ($received[10])('service', 'arr')->configType);
        $this->assertNull($received[18] ?? null);
        $this->assertSame('safe', ($received[14])(true)->mode);
        $this->assertSame($container->get('tab_state'), $received[29] ?? null);
        $this->assertSame('cache-key', ($received[32])('cache-key')->type);
        $this->assertSame($tabDelegateFactory, $received[34] ?? null);
        $this->assertSame($tabViewParserFactory, $received[35] ?? null);
        $this->assertSame($container->get('tab_alias_file_storage'), $received[39] ?? null);
        $this->assertSame(['value'], ($received[40])('value'));
        $this->assertSame(['a' => 1, 'b' => 2], ($received[41])(['a' => 1], ['b' => 2]));
        $this->assertSame('fallback', ($received[42])([], 'missing', 'fallback'));
        $this->assertSame($container->get('class_name_resolver'), $received[43] ?? null);
        $this->assertTrue(($received[44] ?? static fn(): bool => false)(new ArrayObject()));
        $this->assertSame('ApplicationNavigationServiceCreatorTest', ($received[45] ?? static fn(): string => '')($this));
        $this->assertSame($container->get('image_metadata_reader'), $received[46] ?? null);
        $this->assertSame($container->get('error_log_writer'), $received[47] ?? null);
        $this->assertSame($container->get('block_file_storage'), $received[48] ?? null);
        $this->assertSame($container->get('meta_file_storage'), $received[49] ?? null);
        $this->assertSame($container->get('project_tool_file_storage'), $received[50] ?? null);
        $this->assertSame($container->get('root_html_file_storage'), $received[51] ?? null);
    }

    public function testProjectServiceClassAvailabilityCheckIsInjected(): void
    {
        $checkedClasses = [];
        $creator = new application_navigation_service_creator(
            static function (string $className) use (&$checkedClasses): bool {
                $checkedClasses[] = $className;

                return false;
            }
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Service "tab" does not expose a project class.');

        try {
            $creator->createTabService(
                $this->containerWithTabDependencies(),
                static fn(): object => new stdClass(),
                static fn(): object => new stdClass(),
                static fn(): object => new stdClass()
            );
        } finally {
            $this->assertSame(['\fan\project\service\tab'], $checkedClasses);
        }
    }

    public function testNavigationCreatorUsesCommonDependencyBundle(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_service_creator.php');
        $tabDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_dependencies.php');
        $tabCoreDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_core_dependencies.php');
        $tabContextDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_context_dependencies.php');
        $tabInputContextDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_input_context_dependencies.php');
        $tabLocaleSessionContextDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_locale_session_context_dependencies.php');
        $tabLocaleContextDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_locale_context_dependencies.php');
        $tabSessionFactoryContextDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_session_factory_context_dependencies.php');
        $tabRoutingContextDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_routing_context_dependencies.php');
        $tabMatcherRoutingContextDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_matcher_routing_context_dependencies.php');
        $tabRequestRoutingContextDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_request_routing_context_dependencies.php');
        $tabServiceFactoryDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_service_factory_dependencies.php');
        $tabServiceFactoryApplicationDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_service_factory_application_dependencies.php');
        $tabServiceFactoryApplicationDebugDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_service_factory_application_debug_dependencies.php');
        $tabApplicationFactoryApplicationDebugDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_application_factory_application_debug_dependencies.php');
        $tabDebugFactoryApplicationDebugDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_debug_factory_application_debug_dependencies.php');
        $tabServiceFactoryRoleTransferDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_service_factory_role_transfer_dependencies.php');
        $tabRoleFactoryRoleTransferDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_role_factory_role_transfer_dependencies.php');
        $tabTransferFactoryRoleTransferDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_transfer_factory_role_transfer_dependencies.php');
        $tabServiceFactoryConfigHeaderDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_service_factory_config_header_dependencies.php');
        $tabConfigFactoryConfigHeaderDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_config_factory_config_header_dependencies.php');
        $tabHeaderFactoryConfigHeaderDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_header_factory_config_header_dependencies.php');
        $tabServiceFactoryErrorReflectorDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_service_factory_error_reflector_dependencies.php');
        $tabErrorFactoryErrorReflectorDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_error_factory_error_reflector_dependencies.php');
        $tabReflectorFactoryErrorReflectorDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_reflector_factory_error_reflector_dependencies.php');
        $tabServiceFactoryRuntimeDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_service_factory_runtime_dependencies.php');
        $tabServiceFactoryPayloadDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_service_factory_payload_dependencies.php');
        $tabJsonPayloadDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_json_payload_dependencies.php');
        $tabDataCookiePayloadDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_data_cookie_payload_dependencies.php');
        $tabDataLoaderPayloadDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_data_loader_payload_dependencies.php');
        $tabCookiePayloadDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_cookie_payload_dependencies.php');
        $tabDataModelFactoryDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_data_model_factory_dependencies.php');
        $tabEntityModelFactoryDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_entity_model_factory_dependencies.php');
        $tabPagerModelFactoryDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_pager_model_factory_dependencies.php');
        $tabMediaModelFactoryDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_media_model_factory_dependencies.php');
        $tabObfuscatorModelFactoryDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_obfuscator_model_factory_dependencies.php');
        $tabImageModifyModelFactoryDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_image_modify_model_factory_dependencies.php');
        $tabModelFactoryDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_model_factory_dependencies.php');
        $tabUserTimeModelFactoryDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_user_time_model_factory_dependencies.php');
        $tabUserFactoryUserTimeModelFactoryDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_user_factory_user_time_model_factory_dependencies.php');
        $tabDateFactoryUserTimeModelFactoryDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_date_factory_user_time_model_factory_dependencies.php');
        $tabSupportDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_support_dependencies.php');
        $tabSupportArrayHelperDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_support_array_helper_dependencies.php');
        $tabSupportArrayReadCheckDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_support_array_read_check_dependencies.php');
        $tabArrayValueReaderReadCheckDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_array_value_reader_read_check_dependencies.php');
        $tabArrayLikeCheckerReadCheckDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_array_like_checker_read_check_dependencies.php');
        $tabSupportArrayTransformDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_support_array_transform_dependencies.php');
        $tabArrayAdducerTransformDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_array_adducer_transform_dependencies.php');
        $tabRecursiveMergerTransformDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_recursive_merger_transform_dependencies.php');
        $tabSupportBlockDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_support_block_dependencies.php');
        $tabSupportBlockExceptionMetaDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_support_block_exception_meta_dependencies.php');
        $tabBlockExceptionFactoryExceptionMetaDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_block_exception_factory_exception_meta_dependencies.php');
        $tabMetaRowFactoryExceptionMetaDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_meta_row_factory_exception_meta_dependencies.php');
        $tabSupportBlockFactoryDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_support_block_factory_dependencies.php');
        $tabTabStateBlockFactoryDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_tab_state_block_factory_dependencies.php');
        $tabBlockFactoryInstanceBlockFactoryDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_block_factory_instance_block_factory_dependencies.php');
        $tabSupportClassHelperDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_support_class_helper_dependencies.php');
        $tabClassNameResolverClassHelperDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_class_name_resolver_class_helper_dependencies.php');
        $tabShortClassNameResolverClassHelperDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_short_class_name_resolver_class_helper_dependencies.php');
        $tabSupportHelperDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_support_helper_dependencies.php');
        $tabSupportLoaderDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_support_loader_dependencies.php');
        $tabSupportAssetDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_support_asset_dependencies.php');
        $tabAliasAssetDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_alias_asset_dependencies.php');
        $tabMediaErrorAssetDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_media_error_asset_dependencies.php');
        $tabImageMetadataReaderAssetDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_image_metadata_reader_asset_dependencies.php');
        $tabErrorLogWriterAssetDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_error_log_writer_asset_dependencies.php');
        $tabStorageDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_storage_dependencies.php');
        $tabFileStorageDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_file_storage_dependencies.php');
        $tabBlockMetaFileStorageDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_block_meta_file_storage_dependencies.php');
        $tabBlockFileStorageDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_block_file_storage_dependencies.php');
        $tabMetaFileStorageDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_meta_file_storage_dependencies.php');
        $tabRootHtmlFileStorageDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_root_html_file_storage_dependencies.php');
        $tabProjectToolStorageDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_project_tool_storage_dependencies.php');
        $tabUploadLimitStorageDependenciesSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_tab_upload_limit_storage_dependencies.php');

        $this->assertIsString($source);
        $this->assertIsString($tabDependenciesSource);
        $this->assertIsString($tabCoreDependenciesSource);
        $this->assertIsString($tabContextDependenciesSource);
        $this->assertIsString($tabInputContextDependenciesSource);
        $this->assertIsString($tabLocaleSessionContextDependenciesSource);
        $this->assertIsString($tabLocaleContextDependenciesSource);
        $this->assertIsString($tabSessionFactoryContextDependenciesSource);
        $this->assertIsString($tabRoutingContextDependenciesSource);
        $this->assertIsString($tabMatcherRoutingContextDependenciesSource);
        $this->assertIsString($tabRequestRoutingContextDependenciesSource);
        $this->assertIsString($tabServiceFactoryDependenciesSource);
        $this->assertIsString($tabServiceFactoryApplicationDependenciesSource);
        $this->assertIsString($tabServiceFactoryApplicationDebugDependenciesSource);
        $this->assertIsString($tabApplicationFactoryApplicationDebugDependenciesSource);
        $this->assertIsString($tabDebugFactoryApplicationDebugDependenciesSource);
        $this->assertIsString($tabServiceFactoryRoleTransferDependenciesSource);
        $this->assertIsString($tabRoleFactoryRoleTransferDependenciesSource);
        $this->assertIsString($tabTransferFactoryRoleTransferDependenciesSource);
        $this->assertIsString($tabServiceFactoryConfigHeaderDependenciesSource);
        $this->assertIsString($tabConfigFactoryConfigHeaderDependenciesSource);
        $this->assertIsString($tabHeaderFactoryConfigHeaderDependenciesSource);
        $this->assertIsString($tabServiceFactoryErrorReflectorDependenciesSource);
        $this->assertIsString($tabErrorFactoryErrorReflectorDependenciesSource);
        $this->assertIsString($tabReflectorFactoryErrorReflectorDependenciesSource);
        $this->assertIsString($tabServiceFactoryRuntimeDependenciesSource);
        $this->assertIsString($tabServiceFactoryPayloadDependenciesSource);
        $this->assertIsString($tabJsonPayloadDependenciesSource);
        $this->assertIsString($tabDataCookiePayloadDependenciesSource);
        $this->assertIsString($tabDataLoaderPayloadDependenciesSource);
        $this->assertIsString($tabCookiePayloadDependenciesSource);
        $this->assertIsString($tabDataModelFactoryDependenciesSource);
        $this->assertIsString($tabEntityModelFactoryDependenciesSource);
        $this->assertIsString($tabPagerModelFactoryDependenciesSource);
        $this->assertIsString($tabMediaModelFactoryDependenciesSource);
        $this->assertIsString($tabObfuscatorModelFactoryDependenciesSource);
        $this->assertIsString($tabImageModifyModelFactoryDependenciesSource);
        $this->assertIsString($tabModelFactoryDependenciesSource);
        $this->assertIsString($tabUserTimeModelFactoryDependenciesSource);
        $this->assertIsString($tabUserFactoryUserTimeModelFactoryDependenciesSource);
        $this->assertIsString($tabDateFactoryUserTimeModelFactoryDependenciesSource);
        $this->assertIsString($tabSupportDependenciesSource);
        $this->assertIsString($tabSupportArrayHelperDependenciesSource);
        $this->assertIsString($tabSupportArrayReadCheckDependenciesSource);
        $this->assertIsString($tabArrayValueReaderReadCheckDependenciesSource);
        $this->assertIsString($tabArrayLikeCheckerReadCheckDependenciesSource);
        $this->assertIsString($tabSupportArrayTransformDependenciesSource);
        $this->assertIsString($tabArrayAdducerTransformDependenciesSource);
        $this->assertIsString($tabRecursiveMergerTransformDependenciesSource);
        $this->assertIsString($tabSupportBlockDependenciesSource);
        $this->assertIsString($tabSupportBlockExceptionMetaDependenciesSource);
        $this->assertIsString($tabBlockExceptionFactoryExceptionMetaDependenciesSource);
        $this->assertIsString($tabMetaRowFactoryExceptionMetaDependenciesSource);
        $this->assertIsString($tabSupportBlockFactoryDependenciesSource);
        $this->assertIsString($tabTabStateBlockFactoryDependenciesSource);
        $this->assertIsString($tabBlockFactoryInstanceBlockFactoryDependenciesSource);
        $this->assertIsString($tabSupportClassHelperDependenciesSource);
        $this->assertIsString($tabClassNameResolverClassHelperDependenciesSource);
        $this->assertIsString($tabShortClassNameResolverClassHelperDependenciesSource);
        $this->assertIsString($tabSupportHelperDependenciesSource);
        $this->assertIsString($tabSupportLoaderDependenciesSource);
        $this->assertIsString($tabSupportAssetDependenciesSource);
        $this->assertIsString($tabAliasAssetDependenciesSource);
        $this->assertIsString($tabMediaErrorAssetDependenciesSource);
        $this->assertIsString($tabImageMetadataReaderAssetDependenciesSource);
        $this->assertIsString($tabErrorLogWriterAssetDependenciesSource);
        $this->assertIsString($tabStorageDependenciesSource);
        $this->assertIsString($tabFileStorageDependenciesSource);
        $this->assertIsString($tabBlockMetaFileStorageDependenciesSource);
        $this->assertIsString($tabBlockFileStorageDependenciesSource);
        $this->assertIsString($tabMetaFileStorageDependenciesSource);
        $this->assertIsString($tabRootHtmlFileStorageDependenciesSource);
        $this->assertIsString($tabProjectToolStorageDependenciesSource);
        $this->assertIsString($tabUploadLimitStorageDependenciesSource);
        $this->assertStringContainsString('private function commonDependencies(container_interface $container): application_creator_common_dependencies', $source);
        $this->assertStringContainsString('return new application_creator_common_dependencies($container);', $source);
        $this->assertStringContainsString('$common = $this->commonDependencies($container);', $source);
        $this->assertStringContainsString('$common->bootstrapRuntime()', $source);
        $this->assertStringContainsString('$common->config()', $source);
        $this->assertStringContainsString('$common->cacheFactory()', $source);
        $this->assertStringContainsString('private function tabDependencies(container_interface $container): application_navigation_tab_dependencies', $source);
        $this->assertStringContainsString('return new application_navigation_tab_dependencies($container);', $source);
        $this->assertStringContainsString('$tabDependencies = $this->tabDependencies($container);', $source);
        $this->assertStringContainsString('$tabDependencies->matcher()', $source);
        $this->assertStringContainsString('$tabDependencies->uploadSizeLimitProvider()', $source);
        $this->assertStringContainsString('final class application_navigation_tab_dependencies', $tabDependenciesSource);
        $this->assertStringContainsString('$this->core = new application_navigation_tab_core_dependencies($container);', $tabDependenciesSource);
        $this->assertStringContainsString('$this->storage = new application_navigation_tab_storage_dependencies($container);', $tabDependenciesSource);
        $this->assertStringContainsString('return $this->core->matcher();', $tabDependenciesSource);
        $this->assertStringContainsString('$this->context = new application_navigation_tab_context_dependencies($container);', $tabCoreDependenciesSource);
        $this->assertStringContainsString('$this->serviceFactoryDependencies = new application_navigation_tab_service_factory_dependencies($container);', $tabCoreDependenciesSource);
        $this->assertStringContainsString('$this->modelFactory = new application_navigation_tab_model_factory_dependencies($container);', $tabCoreDependenciesSource);
        $this->assertStringContainsString('return $this->context->matcher();', $tabCoreDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_routing_context_dependencies($container)', $tabContextDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_locale_session_context_dependencies($container)', $tabContextDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_input_context_dependencies($container)', $tabContextDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_matcher_routing_context_dependencies($container)', $tabRoutingContextDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_request_routing_context_dependencies($container)', $tabRoutingContextDependenciesSource);
        $this->assertStringContainsString('return $this->matcher->matcher();', $tabRoutingContextDependenciesSource);
        $this->assertStringContainsString('return $this->request->request();', $tabRoutingContextDependenciesSource);
        $this->assertStringContainsString('return $this->container->get(service_id::MATCHER);', $tabMatcherRoutingContextDependenciesSource);
        $this->assertStringContainsString('return $this->container->get(service_id::REQUEST);', $tabRequestRoutingContextDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_locale_context_dependencies($container)', $tabLocaleSessionContextDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_session_factory_context_dependencies($container)', $tabLocaleSessionContextDependenciesSource);
        $this->assertStringContainsString('return $this->locale->locale();', $tabLocaleSessionContextDependenciesSource);
        $this->assertStringContainsString('return $this->sessionFactory->sessionFactory();', $tabLocaleSessionContextDependenciesSource);
        $this->assertStringContainsString('return $this->container->get(service_id::LOCALE);', $tabLocaleContextDependenciesSource);
        $this->assertStringContainsString('return fn(mixed ...$arguments): mixed => $this->container->get(service_id::SESSION, ...$arguments);', $tabSessionFactoryContextDependenciesSource);
        $this->assertStringContainsString('return $this->container->get(service_id::REQUEST_INPUT);', $tabInputContextDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_service_factory_application_dependencies($container)', $tabServiceFactoryDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_service_factory_payload_dependencies($container)', $tabServiceFactoryDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_service_factory_runtime_dependencies($container)', $tabServiceFactoryDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_service_factory_role_transfer_dependencies($container)', $tabServiceFactoryApplicationDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_service_factory_application_debug_dependencies($container)', $tabServiceFactoryApplicationDependenciesSource);
        $this->assertStringContainsString('return $this->roleTransfer->roleFactory();', $tabServiceFactoryApplicationDependenciesSource);
        $this->assertStringContainsString('return $this->applicationDebug->debugFactory();', $tabServiceFactoryApplicationDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_role_factory_role_transfer_dependencies($container)', $tabServiceFactoryRoleTransferDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_transfer_factory_role_transfer_dependencies($container)', $tabServiceFactoryRoleTransferDependenciesSource);
        $this->assertStringContainsString('return $this->roleFactory->roleFactory();', $tabServiceFactoryRoleTransferDependenciesSource);
        $this->assertStringContainsString('return $this->transferFactory->transferFactory();', $tabServiceFactoryRoleTransferDependenciesSource);
        $this->assertStringContainsString('return fn(): mixed => $this->container->get(service_id::ROLE);', $tabRoleFactoryRoleTransferDependenciesSource);
        $this->assertStringContainsString('return fn(): mixed => $this->container->get(service_id::TRANSFER);', $tabTransferFactoryRoleTransferDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_application_factory_application_debug_dependencies($container)', $tabServiceFactoryApplicationDebugDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_debug_factory_application_debug_dependencies($container)', $tabServiceFactoryApplicationDebugDependenciesSource);
        $this->assertStringContainsString('return $this->applicationFactory->applicationFactory();', $tabServiceFactoryApplicationDebugDependenciesSource);
        $this->assertStringContainsString('return $this->debugFactory->debugFactory();', $tabServiceFactoryApplicationDebugDependenciesSource);
        $this->assertStringContainsString('return fn(): mixed => $this->container->get(service_id::APPLICATION);', $tabApplicationFactoryApplicationDebugDependenciesSource);
        $this->assertStringContainsString('return fn(): mixed => $this->container->get(service_id::DEBUG);', $tabDebugFactoryApplicationDebugDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_service_factory_config_header_dependencies($container)', $tabServiceFactoryRuntimeDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_service_factory_error_reflector_dependencies($container)', $tabServiceFactoryRuntimeDependenciesSource);
        $this->assertStringContainsString('return $this->configHeader->configFactory();', $tabServiceFactoryRuntimeDependenciesSource);
        $this->assertStringContainsString('return $this->errorReflector->reflectorFactory();', $tabServiceFactoryRuntimeDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_config_factory_config_header_dependencies($container)', $tabServiceFactoryConfigHeaderDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_header_factory_config_header_dependencies($container)', $tabServiceFactoryConfigHeaderDependenciesSource);
        $this->assertStringContainsString('return $this->configFactory->configFactory();', $tabServiceFactoryConfigHeaderDependenciesSource);
        $this->assertStringContainsString('return $this->headerFactory->headerFactory();', $tabServiceFactoryConfigHeaderDependenciesSource);
        $this->assertStringContainsString('return fn(string $configType = \'service\', string $sourceType = \'arr\'): mixed => $this->container->get(service_id::CONFIG, $configType, $sourceType);', $tabConfigFactoryConfigHeaderDependenciesSource);
        $this->assertStringContainsString('return fn(): mixed => $this->container->get(service_id::HEADER);', $tabHeaderFactoryConfigHeaderDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_error_factory_error_reflector_dependencies($container)', $tabServiceFactoryErrorReflectorDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_reflector_factory_error_reflector_dependencies($container)', $tabServiceFactoryErrorReflectorDependenciesSource);
        $this->assertStringContainsString('return $this->errorFactory->errorFactory();', $tabServiceFactoryErrorReflectorDependenciesSource);
        $this->assertStringContainsString('return $this->reflectorFactory->reflectorFactory();', $tabServiceFactoryErrorReflectorDependenciesSource);
        $this->assertStringContainsString('return fn(): mixed => $this->container->get(service_id::ERROR);', $tabErrorFactoryErrorReflectorDependenciesSource);
        $this->assertStringContainsString('return fn(): mixed => $this->container->get(service_id::REFLECTOR);', $tabReflectorFactoryErrorReflectorDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_json_payload_dependencies($container)', $tabServiceFactoryPayloadDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_data_cookie_payload_dependencies($container)', $tabServiceFactoryPayloadDependenciesSource);
        $this->assertStringContainsString('return $this->json->jsonFactory();', $tabServiceFactoryPayloadDependenciesSource);
        $this->assertStringContainsString('return $this->dataCookie->cookieFactory();', $tabServiceFactoryPayloadDependenciesSource);
        $this->assertStringContainsString('return fn(bool $useBase64 = false): mixed => $this->container->get(service_id::JSON, $useBase64);', $tabJsonPayloadDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_data_loader_payload_dependencies($container)', $tabDataCookiePayloadDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_cookie_payload_dependencies($container)', $tabDataCookiePayloadDependenciesSource);
        $this->assertStringContainsString('return $this->dataLoaderFactory->dataLoaderFactory();', $tabDataCookiePayloadDependenciesSource);
        $this->assertStringContainsString('return $this->cookieFactory->cookieFactory();', $tabDataCookiePayloadDependenciesSource);
        $this->assertStringContainsString('return fn(): mixed => $this->container->get(service_id::DATA_LOADER);', $tabDataLoaderPayloadDependenciesSource);
        $this->assertStringContainsString('return fn(): mixed => $this->container->get(service_id::COOKIE);', $tabCookiePayloadDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_data_model_factory_dependencies($container)', $tabModelFactoryDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_media_model_factory_dependencies($container)', $tabModelFactoryDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_user_time_model_factory_dependencies($container)', $tabModelFactoryDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_entity_model_factory_dependencies($container)', $tabDataModelFactoryDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_pager_model_factory_dependencies($container)', $tabDataModelFactoryDependenciesSource);
        $this->assertStringContainsString('return $this->entityFactory->entityFactory();', $tabDataModelFactoryDependenciesSource);
        $this->assertStringContainsString('return $this->pagerFactory->pagerFactory();', $tabDataModelFactoryDependenciesSource);
        $this->assertStringContainsString('return fn(mixed ...$arguments): mixed => $this->container->get(service_id::ENTITY, ...$arguments);', $tabEntityModelFactoryDependenciesSource);
        $this->assertStringContainsString('return fn(mixed ...$arguments): mixed => $this->container->get(service_id::PAGER, ...$arguments);', $tabPagerModelFactoryDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_obfuscator_model_factory_dependencies($container)', $tabMediaModelFactoryDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_image_modify_model_factory_dependencies($container)', $tabMediaModelFactoryDependenciesSource);
        $this->assertStringContainsString('return $this->obfuscatorFactory->obfuscatorFactory();', $tabMediaModelFactoryDependenciesSource);
        $this->assertStringContainsString('return $this->imageModifyFactory->imageModifyFactory();', $tabMediaModelFactoryDependenciesSource);
        $this->assertStringContainsString('return fn(mixed ...$arguments): mixed => $this->container->get(service_id::OBFUSCATOR, ...$arguments);', $tabObfuscatorModelFactoryDependenciesSource);
        $this->assertStringContainsString('return fn(mixed ...$arguments): mixed => $this->container->get(service_id::IMAGE_MODIFY, ...$arguments);', $tabImageModifyModelFactoryDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_user_factory_user_time_model_factory_dependencies($container)', $tabUserTimeModelFactoryDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_date_factory_user_time_model_factory_dependencies($container)', $tabUserTimeModelFactoryDependenciesSource);
        $this->assertStringContainsString('return $this->userFactory->userFactory();', $tabUserTimeModelFactoryDependenciesSource);
        $this->assertStringContainsString('return $this->dateFactory->dateFactory();', $tabUserTimeModelFactoryDependenciesSource);
        $this->assertStringContainsString('return fn(mixed ...$arguments): mixed => $this->container->get(service_id::USER, ...$arguments);', $tabUserFactoryUserTimeModelFactoryDependenciesSource);
        $this->assertStringContainsString('return fn(mixed ...$arguments): mixed => $this->container->get(service_id::DATE, ...$arguments);', $tabDateFactoryUserTimeModelFactoryDependenciesSource);
        $this->assertStringContainsString('$this->block = new application_navigation_tab_support_block_dependencies($container);', $tabSupportDependenciesSource);
        $this->assertStringContainsString('$this->helper = new application_navigation_tab_support_helper_dependencies($container);', $tabSupportDependenciesSource);
        $this->assertStringContainsString('$this->asset = new application_navigation_tab_support_asset_dependencies($container);', $tabSupportDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_support_block_factory_dependencies($container)', $tabSupportBlockDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_support_block_exception_meta_dependencies($container)', $tabSupportBlockDependenciesSource);
        $this->assertStringContainsString('return $this->blockFactory->tabState();', $tabSupportBlockDependenciesSource);
        $this->assertStringContainsString('return $this->exceptionMeta->metaRowFactory();', $tabSupportBlockDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_tab_state_block_factory_dependencies($container)', $tabSupportBlockFactoryDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_block_factory_instance_block_factory_dependencies($container)', $tabSupportBlockFactoryDependenciesSource);
        $this->assertStringContainsString('return $this->tabState->tabState();', $tabSupportBlockFactoryDependenciesSource);
        $this->assertStringContainsString('return $this->blockFactory->blockFactory();', $tabSupportBlockFactoryDependenciesSource);
        $this->assertStringContainsString('return $this->container->get(service_id::TAB_STATE);', $tabTabStateBlockFactoryDependenciesSource);
        $this->assertStringContainsString('return $this->container->get(service_id::BLOCK_FACTORY);', $tabBlockFactoryInstanceBlockFactoryDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_block_exception_factory_exception_meta_dependencies($container)', $tabSupportBlockExceptionMetaDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_meta_row_factory_exception_meta_dependencies($container)', $tabSupportBlockExceptionMetaDependenciesSource);
        $this->assertStringContainsString('return $this->blockExceptionFactory->blockExceptionFactory();', $tabSupportBlockExceptionMetaDependenciesSource);
        $this->assertStringContainsString('return $this->metaRowFactory->metaRowFactory();', $tabSupportBlockExceptionMetaDependenciesSource);
        $this->assertStringContainsString('return $this->container->get(service_id::BLOCK_EXCEPTION_FACTORY);', $tabBlockExceptionFactoryExceptionMetaDependenciesSource);
        $this->assertStringContainsString('return $this->container->get(service_id::META_ROW_FACTORY);', $tabMetaRowFactoryExceptionMetaDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_support_loader_dependencies($container)', $tabSupportHelperDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_support_array_helper_dependencies($container)', $tabSupportHelperDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_support_class_helper_dependencies($container)', $tabSupportHelperDependenciesSource);
        $this->assertStringContainsString('return $this->container->get(service_id::PHP_ARRAY_FILE_LOADER);', $tabSupportLoaderDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_support_array_transform_dependencies($container)', $tabSupportArrayHelperDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_support_array_read_check_dependencies($container)', $tabSupportArrayHelperDependenciesSource);
        $this->assertStringContainsString('return $this->transform->arrayAdducer();', $tabSupportArrayHelperDependenciesSource);
        $this->assertStringContainsString('return $this->readCheck->arrayLikeChecker();', $tabSupportArrayHelperDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_array_adducer_transform_dependencies($container)', $tabSupportArrayTransformDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_recursive_merger_transform_dependencies($container)', $tabSupportArrayTransformDependenciesSource);
        $this->assertStringContainsString('return $this->arrayAdducer->arrayAdducer();', $tabSupportArrayTransformDependenciesSource);
        $this->assertStringContainsString('return $this->recursiveMerger->recursiveMerger();', $tabSupportArrayTransformDependenciesSource);
        $this->assertStringContainsString('return $this->container->get(service_id::ARRAY_ADDUCER);', $tabArrayAdducerTransformDependenciesSource);
        $this->assertStringContainsString('return $this->container->get(service_id::RECURSIVE_MERGER);', $tabRecursiveMergerTransformDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_array_value_reader_read_check_dependencies($container)', $tabSupportArrayReadCheckDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_array_like_checker_read_check_dependencies($container)', $tabSupportArrayReadCheckDependenciesSource);
        $this->assertStringContainsString('return $this->arrayValueReader->arrayValueReader();', $tabSupportArrayReadCheckDependenciesSource);
        $this->assertStringContainsString('return $this->arrayLikeChecker->arrayLikeChecker();', $tabSupportArrayReadCheckDependenciesSource);
        $this->assertStringContainsString('return $this->container->get(service_id::ARRAY_VALUE_READER);', $tabArrayValueReaderReadCheckDependenciesSource);
        $this->assertStringContainsString('return $this->container->get(service_id::ARRAY_LIKE_CHECKER);', $tabArrayLikeCheckerReadCheckDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_class_name_resolver_class_helper_dependencies($container)', $tabSupportClassHelperDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_short_class_name_resolver_class_helper_dependencies($container)', $tabSupportClassHelperDependenciesSource);
        $this->assertStringContainsString('return $this->classNameResolver->classNameResolver();', $tabSupportClassHelperDependenciesSource);
        $this->assertStringContainsString('return $this->shortClassNameResolver->shortClassNameResolver();', $tabSupportClassHelperDependenciesSource);
        $this->assertStringContainsString('return $this->container->get(service_id::CLASS_NAME_RESOLVER);', $tabClassNameResolverClassHelperDependenciesSource);
        $this->assertStringContainsString('return $this->container->get(service_id::SHORT_CLASS_NAME_RESOLVER);', $tabShortClassNameResolverClassHelperDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_alias_asset_dependencies($container)', $tabSupportAssetDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_media_error_asset_dependencies($container)', $tabSupportAssetDependenciesSource);
        $this->assertStringContainsString('return $this->alias->tabAliasFileStorage();', $tabSupportAssetDependenciesSource);
        $this->assertStringContainsString('return $this->mediaError->errorLogWriter();', $tabSupportAssetDependenciesSource);
        $this->assertStringContainsString('return $this->container->get(service_id::TAB_ALIAS_FILE_STORAGE);', $tabAliasAssetDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_image_metadata_reader_asset_dependencies($container)', $tabMediaErrorAssetDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_error_log_writer_asset_dependencies($container)', $tabMediaErrorAssetDependenciesSource);
        $this->assertStringContainsString('return $this->imageMetadataReader->imageMetadataReader();', $tabMediaErrorAssetDependenciesSource);
        $this->assertStringContainsString('return $this->errorLogWriter->errorLogWriter();', $tabMediaErrorAssetDependenciesSource);
        $this->assertStringContainsString('return $this->container->get(service_id::IMAGE_METADATA_READER);', $tabImageMetadataReaderAssetDependenciesSource);
        $this->assertStringContainsString('return $this->container->get(service_id::ERROR_LOG_WRITER);', $tabErrorLogWriterAssetDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_file_storage_dependencies($container)', $tabStorageDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_project_tool_storage_dependencies($container)', $tabStorageDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_upload_limit_storage_dependencies($container)', $tabStorageDependenciesSource);
        $this->assertStringContainsString('return $this->fileStorage->blockFileStorage();', $tabStorageDependenciesSource);
        $this->assertStringContainsString('return $this->projectToolStorage->projectToolFileStorage();', $tabStorageDependenciesSource);
        $this->assertStringContainsString('return $this->uploadLimit->uploadSizeLimitProvider();', $tabStorageDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_block_meta_file_storage_dependencies($container)', $tabFileStorageDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_root_html_file_storage_dependencies($container)', $tabFileStorageDependenciesSource);
        $this->assertStringContainsString('return $this->blockMeta->blockFileStorage();', $tabFileStorageDependenciesSource);
        $this->assertStringContainsString('return $this->rootHtml->rootHtmlFileStorage();', $tabFileStorageDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_block_file_storage_dependencies($container)', $tabBlockMetaFileStorageDependenciesSource);
        $this->assertStringContainsString('new application_navigation_tab_meta_file_storage_dependencies($container)', $tabBlockMetaFileStorageDependenciesSource);
        $this->assertStringContainsString('return $this->blockFileStorage->blockFileStorage();', $tabBlockMetaFileStorageDependenciesSource);
        $this->assertStringContainsString('return $this->metaFileStorage->metaFileStorage();', $tabBlockMetaFileStorageDependenciesSource);
        $this->assertStringContainsString('return $this->container->get(service_id::BLOCK_FILE_STORAGE);', $tabBlockFileStorageDependenciesSource);
        $this->assertStringContainsString('return $this->container->get(service_id::META_FILE_STORAGE);', $tabMetaFileStorageDependenciesSource);
        $this->assertStringContainsString('return $this->container->get(service_id::ROOT_HTML_FILE_STORAGE);', $tabRootHtmlFileStorageDependenciesSource);
        $this->assertStringContainsString('return $this->container->get(service_id::PROJECT_TOOL_FILE_STORAGE);', $tabProjectToolStorageDependenciesSource);
        $this->assertStringContainsString('return $this->container->get(service_id::UPLOAD_SIZE_LIMIT_PROVIDER);', $tabUploadLimitStorageDependenciesSource);
    }

    private function containerWithTabDependencies(): container
    {
        $container = new container();
        $container
            ->factory('matcher', static fn(): object => (object)['name' => 'matcher'])
            ->factory('request', static fn(): object => (object)['name' => 'request'])
            ->factory('locale', static fn(): object => (object)['name' => 'locale'])
            ->factory('session', static fn(container $container, string $namespace, string $group): object => (object)['namespace' => $namespace, 'group' => $group], false)
            ->factory('request_input', static fn(): object => (object)['name' => 'request_input'])
            ->factory('bootstrap_runtime', static fn(): object => (object)['name' => 'runtime'])
            ->factory('role', static fn(): object => (object)['name' => 'role'])
            ->factory('transfer', static fn(): object => (object)['name' => 'transfer'])
            ->factory('config', static fn(container $container, string $configType = 'service', string $sourceType = 'arr'): object => (object)['configType' => $configType, 'sourceType' => $sourceType], false)
            ->factory('header', static fn(): object => (object)['name' => 'header'])
            ->factory('application', static fn(): object => (object)['name' => 'application'])
            ->factory('debug', static fn(): object => (object)['name' => 'debug'])
            ->factory('json', static fn(container $container, bool $useBase64 = false): object => (object)['mode' => $useBase64 ? 'safe' : 'plain'], false)
            ->factory('data_loader', static fn(): object => (object)['name' => 'data_loader'])
            ->factory('array_adducer', static fn(): callable => static fn(mixed $value): array => is_array($value) ? $value : [$value])
            ->factory('recursive_merger', static fn(): callable => static fn(mixed ...$values): array => array_replace_recursive(...$values))
            ->factory('array_value_reader', static fn(): callable => static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default)
            ->factory('class_name_resolver', static fn(): callable => static fn(object $object): string => get_class($object))
            ->factory('array_like_checker', static fn(): callable => static fn(mixed $value): bool => is_array($value) || $value instanceof \ArrayAccess)
            ->factory('short_class_name_resolver', static fn(): callable => static function (object|string $object): string {
                $className = is_object($object) ? get_class($object) : $object;
                $position = strrpos($className, '\\');

                return $position === false ? $className : substr($className, $position + 1);
            })
            ->factory('error', static fn(): object => (object)['name' => 'error'])
            ->factory('cookie', static fn(): object => (object)['name' => 'cookie'])
            ->factory('reflector', static fn(): object => (object)['name' => 'reflector'])
            ->factory('entity', static fn(): object => (object)['name' => 'entity'], false)
            ->factory('form', static fn(): object => (object)['name' => 'form'], false)
            ->factory('pager', static fn(): object => (object)['name' => 'pager'], false)
            ->factory('obfuscator', static fn(): object => (object)['name' => 'obfuscator'], false)
            ->factory('image_modify', static fn(): object => (object)['name' => 'image_modify'], false)
            ->factory('database', static fn(): object => (object)['name' => 'database'], false)
            ->factory('user', static fn(): object => (object)['name' => 'user'], false)
            ->factory('date', static fn(): object => (object)['name' => 'date'], false)
            ->factory('tab_state', static fn(): object => (object)['name' => 'tab_state'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('php_array_file_loader', static fn(): object => (object)['name' => 'php_array_file_loader'])
            ->factory('block_factory', static fn(): object => (object)['name' => 'block_factory'])
            ->factory('block_exception_factory', static fn(): object => (object)['name' => 'block_exception_factory'])
            ->factory('meta_row_factory', static fn(): object => (object)['name' => 'meta_row_factory'])
            ->factory('tab_alias_file_storage', static fn(): object => (object)['name' => 'tab_alias_file_storage'])
            ->factory('image_metadata_reader', static fn(): object => (object)['name' => 'image_metadata_reader'])
            ->factory('error_log_writer', static fn(): object => (object)['name' => 'error_log_writer'])
            ->factory('block_file_storage', static fn(): object => (object)['name' => 'block_file_storage'])
            ->factory('meta_file_storage', static fn(): object => (object)['name' => 'meta_file_storage'])
            ->factory('project_tool_file_storage', static fn(): object => (object)['name' => 'project_tool_file_storage'])
            ->factory('root_html_file_storage', static fn(): object => (object)['name' => 'root_html_file_storage'])
            ->factory('upload_size_limit_provider', static fn(): object => (object)['name' => 'upload_size_limit_provider']);

        return $container;
    }
}

final class ApplicationNavigationServiceCreatorTabProbe
{
    public ?object $uploadSizeLimitProvider = null;

    public function setUploadSizeLimitProvider(object $uploadSizeLimitProvider): void
    {
        $this->uploadSizeLimitProvider = $uploadSizeLimitProvider;
    }
}
