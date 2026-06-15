<?php

declare(strict_types=1);

use fan\core\di\service_id;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/tools/ai_map.php';
require_once dirname(__DIR__, 2) . '/tools/ai_explain.php';
require_once dirname(__DIR__, 2) . '/tools/ai_verify.php';

final class AiToolingTest extends TestCase
{
    public function testAiMapBuildsRequiredProjectSections(): void
    {
        $root = dirname(__DIR__, 2);
        $map = php_fan_ai_build_map($root);

        $this->assertSame(1, $map['schema_version']);
        $this->assertSame('htdocs/index.php', $map['entrypoints']['web']);
        $this->assertContains('core', $map['source_roots']);
        $this->assertContains('unit', $map['test_roots']);
        $this->assertArrayHasKey('fan\\core\\di\\', $map['autoload']['psr-4']);
        $this->assertContains('.ai/project.md', $map['ai_docs']);
        $this->assertSame(service_id::MATCHER, $map['services']['constants']['MATCHER']);
        $this->assertArrayHasKey(service_id::REQUEST, $map['services']['registered']);
        $this->assertArrayHasKey(service_id::MATCHER, $map['services']['registered']);
        foreach ([service_id::REQUEST, service_id::MATCHER, service_id::BOOTSTRAP_RUNTIME, service_id::CONFIG, service_id::CACHE, service_id::JSON, service_id::TAB, service_id::SESSION, service_id::USER] as $serviceId) {
            $this->assertArrayHasKey($serviceId, $map['services']['descriptors']);
            $descriptor = $map['services']['descriptors'][$serviceId];
            $this->assertSame($serviceId, $descriptor['id']);
            $this->assertNotSame([], $descriptor['registrar_files']);
            $this->assertArrayHasKey('class', $descriptor);
            $this->assertArrayHasKey('factory', $descriptor);
            $this->assertArrayHasKey('config_key', $descriptor);
            $this->assertArrayHasKey('source_edges', $descriptor);
            $this->assertArrayHasKey('source_locations', $descriptor);
            $this->assertIsString($descriptor['lifetime_reason']);
            $this->assertNotSame('', $descriptor['lifetime_reason']);
            $this->assertSame(
                $descriptor['dependencies'],
                $descriptor['factory_arguments']['container_dependencies']
            );
            $this->assertSame(
                $descriptor['registrar_files'],
                $descriptor['factory_origin']['registrar_files']
            );
            $this->assertSame(
                $descriptor['registrar_files'],
                $descriptor['source_edges']['registrar_files']
            );
            $this->assertSame(
                $descriptor['creator_methods'],
                $descriptor['source_edges']['creator_methods']
            );
            $this->assertSame(
                $descriptor['dependencies'],
                $descriptor['source_edges']['dependencies']
            );
            $this->assertSame(
                $descriptor['factory_arguments']['runtime_arguments'],
                $descriptor['source_edges']['runtime_arguments']
            );
            $this->assertSame(
                $descriptor['aliases'],
                $descriptor['source_edges']['aliases']
            );
            $this->assertArrayHasKey('referenced_by', $descriptor);
            $this->assertArrayHasKey('files', $descriptor['referenced_by']);
            $this->assertArrayHasKey('locations', $descriptor['referenced_by']);
            $this->assertIsArray($descriptor['referenced_by']['files']);
            $this->assertIsArray($descriptor['referenced_by']['locations']);
            foreach (['registrations', 'creator_methods', 'classes', 'dependencies', 'factories', 'config_keys', 'runtime_arguments', 'aliases'] as $locationKey) {
                $this->assertArrayHasKey($locationKey, $descriptor['source_locations']);
                $this->assertIsArray($descriptor['source_locations'][$locationKey]);
                foreach ($descriptor['source_locations'][$locationKey] as $location) {
                    $this->assertIsString($location['file']);
                    $this->assertGreaterThan(0, $location['line']);
                }
            }
        }
        $cacheDescriptor = $map['services']['descriptors'][service_id::CACHE];
        $requestDescriptor = $map['services']['descriptors'][service_id::REQUEST];
        $dateDescriptor = $map['services']['descriptors'][service_id::DATE];
        $sessionDescriptor = $map['services']['descriptors'][service_id::SESSION];

        $this->assertArrayHasKey('referenced_locations', $map['services']);
        $this->assertArrayHasKey(service_id::CONFIG, $map['services']['referenced_locations']);
        $this->assertContains(
            $this->sourceLineContainingAfter(
                'core/di/application_infrastructure_service_creator.php',
                'public function createCacheService',
                '$config = $infrastructureDependencies->config();'
            ),
            $this->serviceReferenceLocationLines(
                $map,
                service_id::CONFIG,
                'core/di/application_infrastructure_service_creator.php'
            )
        );

        $this->assertSame('\fan\project\service\cache', $cacheDescriptor['class']);
        $this->assertContains('core/di/application_infrastructure_service_creator.php', $cacheDescriptor['referenced_by']['files']);
        $this->assertContains('core/di/application_infrastructure_service_creator.php', array_column($cacheDescriptor['referenced_by']['locations'], 'file'));
        $this->assertSame('cacheEngineFactory', $cacheDescriptor['factory']);
        $this->assertSame('cache', $cacheDescriptor['config_key']);
        $this->assertSame('factory registration explicitly disables sharing', $cacheDescriptor['lifetime_reason']);
        $this->assertContains('cacheServiceFactory', $cacheDescriptor['source_edges']['factories']);
        $this->assertContains('\fan\project\service\cache', $cacheDescriptor['source_edges']['classes']);
        $this->assertContains(service_id::CONFIG, $cacheDescriptor['source_edges']['dependencies']);
        $this->assertContains('type', $cacheDescriptor['source_edges']['runtime_arguments']);
        $this->assertContains(
            [
                'file' => 'core/di/application_infrastructure_service_creator.php',
                'line' => $cacheDescriptor['source_locations']['creator_methods'][0]['line'],
                'method' => 'createCacheService',
            ],
            $cacheDescriptor['source_locations']['creator_methods']
        );
        $this->assertContains('cacheServiceFactory', array_column($cacheDescriptor['source_locations']['factories'], 'value'));
        $this->assertContains('cache', array_column($cacheDescriptor['source_locations']['config_keys'], 'value'));
        $this->assertContains('type', array_column($cacheDescriptor['source_locations']['runtime_arguments'], 'value'));
        $this->assertContains(service_id::CONFIG, array_column($cacheDescriptor['source_locations']['dependencies'], 'value'));
        $this->assertNotContains(service_id::CACHE, array_column($cacheDescriptor['source_locations']['dependencies'], 'value'));
        $this->assertSame(
            $this->sourceLineContaining(
                'core/di/application_infrastructure_service_registrar.php',
                'mixed $type = null'
            ),
            $this->sourceLocationLine($cacheDescriptor, 'runtime_arguments', 'type')
        );
        $this->assertSame(
            $this->sourceLineContaining('core/di/application_infrastructure_service_registrar.php', 'service_id::CACHE,'),
            $cacheDescriptor['source_locations']['registrations'][0]['line']
        );
        $this->assertSame(
            $this->sourceLineContainingAfter(
                'core/di/application_infrastructure_service_creator.php',
                'public function createCacheService',
                '$config = $infrastructureDependencies->config();'
            ),
            $this->sourceLocationLine($cacheDescriptor, 'dependencies', service_id::CONFIG)
        );
        $this->assertSame(
            $this->sourceLineContainingAfter(
                'core/di/application_infrastructure_service_creator.php',
                'public function createCacheService',
                'callable $cacheEngineFactory'
            ),
            $this->sourceLocationLine($cacheDescriptor, 'factories', 'cacheEngineFactory')
        );
        $this->assertSame(
            $this->sourceLineContainingAfter(
                'core/di/application_infrastructure_service_creator.php',
                'public function createCacheService',
                'callable $cacheServiceFactory'
            ),
            $this->sourceLocationLine($cacheDescriptor, 'factories', 'cacheServiceFactory')
        );
        $this->assertSame(
            $this->sourceLineContainingAfter(
                'core/di/application_infrastructure_service_creator.php',
                'public function createCacheService',
                "\$className = self::getProjectServiceClassName('cache');"
            ),
            $this->sourceLocationLine($cacheDescriptor, 'classes', '\fan\project\service\cache')
        );
        $this->assertSame(
            $this->sourceLineContaining('core/di/application_infrastructure_service_creator.php', "\$config->get('cache')"),
            $this->sourceLocationLine($cacheDescriptor, 'config_keys', 'cache')
        );

        $this->assertSame('\fan\project\service\request', $requestDescriptor['class']);
        $this->assertSame('requestServiceFactory', $requestDescriptor['factory']);
        $this->assertNull($requestDescriptor['config_key']);
        $this->assertSame('factory registration defaults to shared service', $requestDescriptor['lifetime_reason']);

        $this->assertContains('dateServiceFactory', $dateDescriptor['source_edges']['factories']);
        $this->assertContains('date', $dateDescriptor['source_edges']['config_keys']);
        $this->assertContains('TIMEZONE', $dateDescriptor['source_edges']['config_keys']);
        $this->assertContains('DEFAULT_FORMAT', $dateDescriptor['source_edges']['config_keys']);
        $this->assertContains('createDateService', array_column($dateDescriptor['source_locations']['creator_methods'], 'method'));
        $this->assertContains('TIMEZONE', array_column($dateDescriptor['source_locations']['config_keys'], 'value'));
        $this->assertSame(
            $this->sourceLineContaining('core/di/application_utility_service_creator.php', "\$config->get('TIMEZONE'"),
            $this->sourceLocationLine($dateDescriptor, 'config_keys', 'TIMEZONE')
        );

        $this->assertSame('\fan\project\service\session', $sessionDescriptor['class']);
        $this->assertContains('sessionServiceFactory', $sessionDescriptor['source_edges']['factories']);
        $this->assertContains('session', $sessionDescriptor['source_edges']['config_keys']);
        $this->assertContains('createRequestService', $map['services']['descriptors'][service_id::REQUEST]['creator_methods']);
        $this->assertContains('createRequestService', $map['services']['descriptors'][service_id::REQUEST]['factory_origin']['creator_methods']);
        $this->assertContains(service_id::CONFIG, $map['services']['descriptors'][service_id::REQUEST]['dependencies']);
        $this->assertContains('type', $map['services']['descriptors'][service_id::CACHE]['factory_arguments']['runtime_arguments']);
        $this->assertSame([
            'parameters' => [
                'container',
                'cacheState',
                'memcacheState',
                'cacheEngineFactory',
                'cacheServiceFactory',
                'type',
            ],
            'runtime_arguments' => ['type'],
            'container_dependencies' => ['container'],
            'optional_arguments' => ['type'],
        ], $map['services']['descriptors'][service_id::CACHE]['creator_method_arguments']['createCacheService']);
        $this->assertSame([
            'date',
            'format',
            'save',
            'timezone',
        ], $map['services']['descriptors'][service_id::DATE]['creator_method_arguments']['createDateService']['runtime_arguments']);
        $this->assertSame([
            'group',
            'nameSpace',
        ], $map['services']['descriptors'][service_id::SESSION]['creator_method_arguments']['createSessionService']['runtime_arguments']);
        $this->assertFalse($map['services']['descriptors'][service_id::CACHE]['shared']);
        $this->assertIsArray($map['services']['descriptors'][service_id::CACHE]['aliases']);
        $this->assertSame('.ai/meta.schema.json', $map['metadata']['meta_schema']);
        $this->assertArrayHasKey('own', $map['metadata']['meta']['top_level_key_usage']);
        $this->assertArrayHasKey('json', $map['metadata']['meta']['own_key_usage']);
        $this->assertSame(
            'core/block/admin/data_form.tpl',
            $map['metadata']['meta']['files']['core/block/admin/data_form.meta.php']['paired_template']
        );
        $this->assertArrayHasKey('categories', $map['dynamic_boundaries']);
        $this->assertArrayHasKey('category_reasons', $map['dynamic_boundaries']);
        $this->assertArrayHasKey('locations', $map['dynamic_boundaries']);
        $this->assertArrayHasKey('source_inventory', $map);
        $this->assertArrayHasKey('queues', $map['source_inventory']);
        $this->assertArrayHasKey('next', $map['source_inventory']);
        $this->assertSame('source_inventory.next', $map['source_inventory']['policy']['next_queue']);
        $this->assertContains('bootstrap_boundary', $map['source_inventory']['policy']['classification_kinds']);
        $this->assertSame('clean', $map['source_inventory']['queues']['service_locator_calls']['classification']);
        $this->assertSame(0, $map['source_inventory']['queues']['service_locator_calls']['count']);
        $this->assertSame('clean', $map['source_inventory']['queues']['unmanaged_loading_statements']['classification']);
        $this->assertSame(0, $map['source_inventory']['queues']['unmanaged_loading_statements']['count']);
        $this->assertSame('clean', $map['source_inventory']['queues']['unmanaged_container_lookups']['classification']);
        $this->assertSame(0, $map['source_inventory']['queues']['unmanaged_container_lookups']['count']);
        $this->assertSame([], $map['source_inventory']['next']);
        $this->assertSame([], $map['source_inventory']['queues']['unmanaged_container_lookups']['files']);
        $this->assertNotContains('core/application/application.php', $map['source_inventory']['queues']['unmanaged_container_lookups']['files']);
        $this->assertNotContains('core/di/application_pager_service_registrar.php', $map['source_inventory']['queues']['unmanaged_container_lookups']['files']);
        $this->assertNotContains('core/di/application_session_service_registrar.php', $map['source_inventory']['queues']['unmanaged_container_lookups']['files']);
        $this->assertNotContains('core/di/application_client_service_registrar.php', $map['source_inventory']['queues']['unmanaged_container_lookups']['files']);
        $this->assertNotContains('core/di/application_utility_service_registrar.php', $map['source_inventory']['queues']['unmanaged_container_lookups']['files']);
        $this->assertNotContains('core/di/application_user_service_registrar.php', $map['source_inventory']['queues']['unmanaged_container_lookups']['files']);
        $this->assertNotContains('core/di/application_infrastructure_service_registrar.php', $map['source_inventory']['queues']['unmanaged_container_lookups']['files']);
        $this->assertSame('bootstrap_boundary', $map['source_inventory']['queues']['bootstrap_container_lookups']['classification']);
        $this->assertSame(25, $map['source_inventory']['queues']['bootstrap_container_lookups']['count']);
        $this->assertSame([
            'core/application/application.php',
            'core/application/context.php',
            'core/di/application_compiled_template_adapter_defaults_provider.php',
            'core/di/application_image_adapter_defaults_provider.php',
            'core/di/application_state_registry.php',
            'core/di/application_storage_adapter_defaults_provider.php',
            'core/factory/application_registry_defaults_provider_factory.php',
        ], $map['source_inventory']['queues']['bootstrap_container_lookups']['files']);
        $this->assertSame('composition_boundary', $map['source_inventory']['queues']['composition_container_lookups']['classification']);
        $this->assertSame(272, $map['source_inventory']['queues']['composition_container_lookups']['count']);
        $this->assertCount(272, $map['source_inventory']['queues']['composition_container_lookups']['files']);
        $this->assertContains('core/di/application_pager_service_registrar_dependencies.php', $map['source_inventory']['queues']['composition_container_lookups']['files']);
        $this->assertContains('core/di/application_session_service_registrar_dependencies.php', $map['source_inventory']['queues']['composition_container_lookups']['files']);
        $this->assertContains('core/di/application_client_cookie_state_registrar_dependencies.php', $map['source_inventory']['queues']['composition_container_lookups']['files']);
        $this->assertContains('core/di/application_client_curl_state_registrar_dependencies.php', $map['source_inventory']['queues']['composition_container_lookups']['files']);
        $this->assertContains('core/di/application_client_rest_state_registrar_dependencies.php', $map['source_inventory']['queues']['composition_container_lookups']['files']);
        $this->assertContains('core/di/application_utility_date_state_registrar_dependencies.php', $map['source_inventory']['queues']['composition_container_lookups']['files']);
        $this->assertContains('core/di/application_utility_obfuscator_state_registrar_dependencies.php', $map['source_inventory']['queues']['composition_container_lookups']['files']);
        $this->assertContains('core/di/application_utility_image_modify_state_registrar_dependencies.php', $map['source_inventory']['queues']['composition_container_lookups']['files']);
        $this->assertContains('core/di/application_user_state_registrar_dependencies.php', $map['source_inventory']['queues']['composition_container_lookups']['files']);
        $this->assertContains('core/di/application_infrastructure_config_state_registrar_dependencies.php', $map['source_inventory']['queues']['composition_container_lookups']['files']);
        $this->assertContains('core/di/application_support_bootstrap_runtime_registrar_dependencies.php', $map['source_inventory']['queues']['composition_container_lookups']['files']);
        $this->assertContains('core/di/application_support_error_demonstrator_loader_registrar_dependencies.php', $map['source_inventory']['queues']['composition_container_lookups']['files']);
        $this->assertContains('core/di/application_support_plain_file_storage_registrar_dependencies.php', $map['source_inventory']['queues']['composition_container_lookups']['files']);
        $this->assertSame('intentional_compatibility', $map['source_inventory']['queues']['intentional_loading_boundaries']['classification']);
        $this->assertSame(12, $map['source_inventory']['queues']['intentional_loading_boundaries']['count']);
        $this->assertSame([
            'core/adapter/bootstrap_loader_file_storage.php',
            'core/adapter/compiled_template_loader.php',
            'core/adapter/error_demonstrator_loader.php',
            'core/adapter/php_array_file.php',
            'core/adapter/php_template_file.php',
            'core/adapter/project_tool_loader.php',
            'core/adapter/zend_autoloader.php',
        ], $map['source_inventory']['queues']['intentional_loading_boundaries']['files']);
        $this->assertSame('tooling_support', $map['source_inventory']['queues']['tooling_loading_statements']['classification']);
        $this->assertSame(9, $map['source_inventory']['queues']['tooling_loading_statements']['count']);
        $this->assertSame([
            'tools/ai_explain.php',
            'tools/ai_map.php',
            'tools/ai_static_check.php',
            'tools/ai_verify.php',
            'tools/bootstrap_smoke.php',
            'tools/composer_autoload.php',
        ], $map['source_inventory']['queues']['tooling_loading_statements']['files']);
        $this->assertSame('entrypoint_boundary', $map['source_inventory']['queues']['entrypoint_loading_statements']['classification']);
        $this->assertSame(2, $map['source_inventory']['queues']['entrypoint_loading_statements']['count']);
        $this->assertSame(['htdocs/index.php'], $map['source_inventory']['queues']['entrypoint_loading_statements']['files']);
        $this->assertSame('intentional_compatibility', $map['source_inventory']['queues']['explicit_native_construction_boundaries']['classification']);
        $this->assertSame(0, $map['source_inventory']['queues']['explicit_native_construction_boundaries']['count']);
        $this->assertArrayHasKey('composition_leaf_policy', $map['dynamic_boundaries']);
        $this->assertArrayHasKey('terminal_composition_leaves', $map['dynamic_boundaries']);
        $this->assertArrayHasKey('actionable_composition_roots', $map['dynamic_boundaries']);
        $this->assertArrayHasKey('named_migration_debt_policy', $map['dynamic_boundaries']);
        $this->assertArrayHasKey('actionable_named_migration_debt', $map['dynamic_boundaries']);
        $this->assertArrayHasKey('intentional_named_compatibility_boundaries', $map['dynamic_boundaries']);
        $this->assertSame(1, $map['dynamic_boundaries']['composition_leaf_policy']['terminal_count']);
        $this->assertSame(2, $map['dynamic_boundaries']['composition_leaf_policy']['actionable_min_count']);
        $this->assertSame('terminal_composition_leaf', $map['dynamic_boundaries']['composition_leaf_policy']['terminal_kind']);
        $this->assertSame('actionable_composition_root', $map['dynamic_boundaries']['composition_leaf_policy']['actionable_kind']);
        $this->assertSame('named_default_closure_boundary', $map['dynamic_boundaries']['named_migration_debt_policy']['boundary_kind']);
        $this->assertSame('actionable_named_migration_debt', $map['dynamic_boundaries']['named_migration_debt_policy']['actionable_kind']);
        $this->assertSame('intentional_named_compatibility_boundary', $map['dynamic_boundaries']['named_migration_debt_policy']['intentional_kind']);
        $this->assertSame([], $map['dynamic_boundaries']['actionable_composition_roots']);
        $this->assertContains('core/block/base_dependency_application_service_runtime_factory_group.php', $map['dynamic_boundaries']['terminal_composition_leaves']);
        $this->assertSame([], array_column($map['dynamic_boundaries']['actionable_named_migration_debt'], 'file'));
        $this->assertContains('core/base/model/entity_dependencies.php', array_column($map['dynamic_boundaries']['intentional_named_compatibility_boundaries'], 'file'));
        $this->assertContains('core/base/transfer/int.php', array_column($map['dynamic_boundaries']['intentional_named_compatibility_boundaries'], 'file'));
        $this->assertContains('core/block/base_dependency_defaults.php', array_column($map['dynamic_boundaries']['intentional_named_compatibility_boundaries'], 'file'));
        $this->assertContains('core/service/block_context.php', array_column($map['dynamic_boundaries']['intentional_named_compatibility_boundaries'], 'file'));
        $this->assertNotSame('', $map['dynamic_boundaries']['category_reasons']['composition_roots']);
        $this->assertContains('core/di/application_infrastructure_service_creator.php', $map['dynamic_boundaries']['categories']['composition_roots']);
        $this->assertContains('core/di/application_creator_common_dependencies.php', $map['dynamic_boundaries']['categories']['composition_roots']);
        $this->assertContains('core/service/translation.php', $map['dynamic_boundaries']['categories']['extension_api']);
        $this->assertContains('core/base/model/entity_dependencies.php', $map['dynamic_boundaries']['categories']['extension_api']);
        $this->assertContains('core/block/base_dependency_defaults.php', $map['dynamic_boundaries']['categories']['extension_api']);
        $this->assertContains('tools/composer_autoload.php', $map['dynamic_boundaries']['categories']['tooling_support']);
        $this->assertNotContains('core/service/debug.php', $map['dynamic_boundaries']['categories']['migration_debt']);
        $this->assertContains('core/base/transfer/int.php', $map['dynamic_boundaries']['categories']['extension_api']);
        $this->assertContains('core/service/block_context.php', $map['dynamic_boundaries']['categories']['extension_api']);
        $this->assertNotContains('core/base/model/entity.php', $map['dynamic_boundaries']['categories']['migration_debt']);
        $this->assertNotContains('core/block/base.php', $map['dynamic_boundaries']['categories']['migration_debt']);
        $this->assertNotContains('core/base/transfer/int.php', $map['dynamic_boundaries']['categories']['migration_debt']);
        $this->assertNotContains('core/service/block_context.php', $map['dynamic_boundaries']['categories']['migration_debt']);
        $requestDescriptor = $map['services']['descriptors'][service_id::REQUEST];
        $tabDescriptor = $map['services']['descriptors'][service_id::TAB];
        $userDescriptor = $map['services']['descriptors'][service_id::USER];
        $this->assertContains(service_id::BOOTSTRAP_RUNTIME, $requestDescriptor['dependencies']);
        $this->assertContains(service_id::CONFIG, $requestDescriptor['dependencies']);
        $this->assertContains(service_id::CACHE, $requestDescriptor['dependencies']);
        $requestDependencyValues = array_column($requestDescriptor['source_locations']['dependencies'], 'value');
        $this->assertContains(service_id::BOOTSTRAP_RUNTIME, $requestDependencyValues);
        $this->assertContains(service_id::CONFIG, $requestDependencyValues);
        $this->assertContains(service_id::CACHE, $requestDependencyValues);
        $this->assertContains('core/di/application_navigation_tab_dependencies.php', $map['dynamic_boundaries']['categories']['composition_roots']);
        $this->assertContains('core/di/application_navigation_tab_core_dependencies.php', $map['dynamic_boundaries']['categories']['composition_roots']);
        foreach ([service_id::MATCHER, service_id::REQUEST, service_id::TAB_STATE, service_id::UPLOAD_SIZE_LIMIT_PROVIDER] as $serviceId) {
            $this->assertContains($serviceId, $tabDescriptor['dependencies']);
            $this->assertContains($serviceId, array_column($tabDescriptor['source_locations']['dependencies'], 'value'));
        }
        $this->assertContains('core/di/application_navigation_tab_matcher_routing_context_dependencies.php', $map['services']['referenced'][service_id::MATCHER]);
        $this->assertContains('core/di/application_navigation_tab_request_routing_context_dependencies.php', $map['services']['referenced'][service_id::REQUEST]);
        $this->assertContains('core/di/application_user_service_dependencies.php', $map['dynamic_boundaries']['categories']['composition_roots']);
        $this->assertContains('core/di/application_infrastructure_config_cache_dependencies.php', $map['dynamic_boundaries']['categories']['composition_roots']);
        foreach ([service_id::SERIALIZER_OPERATIONS, service_id::SESSION, service_id::CURRENT_USER, service_id::ARRAY_ADDUCER] as $serviceId) {
            $this->assertContains($serviceId, $userDescriptor['dependencies']);
            $this->assertContains($serviceId, array_column($userDescriptor['source_locations']['dependencies'], 'value'));
        }
        $this->assertContains('core/di/application_user_serializer_operations_serialization_config_context_dependencies.php', $map['services']['referenced'][service_id::SERIALIZER_OPERATIONS]);
        $this->assertSame(
            $this->sourceLineContaining('core/di/application_infrastructure_service_creator.php', 'class_exists($className)'),
            $this->dynamicBoundaryLocationLine($map, 'core/di/application_infrastructure_service_creator.php', 'class_exists')
        );
        $this->assertContains(
            'tplType',
            $map['metadata']['templates']['files']['project/block/common/html_pager.tpl']['placeholders']
        );
    }

    public function testAiMapCanBeEncodedAsJson(): void
    {
        $json = json_encode(php_fan_ai_build_map(dirname(__DIR__, 2)), JSON_THROW_ON_ERROR);
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('php tools/ai_verify.php', $decoded['commands']['ai_verify']);
        $this->assertSame('php tools/ai_explain.php <file> --json', $decoded['commands']['ai_explain']);
        $this->assertSame('php tools/ai_static_check.php', $decoded['commands']['ai_static']);
        $this->assertSame('php fan ai:map --json', $decoded['commands']['fan_ai_map']);
        $this->assertSame('php fan ai:map --validate', $decoded['commands']['fan_ai_map_validate']);
        $this->assertSame('php fan ai:dynamic-boundaries --json', $decoded['commands']['fan_ai_dynamic_boundaries']);
        $this->assertSame('php fan ai:dynamic-boundaries --composition-open --json', $decoded['commands']['fan_ai_dynamic_boundaries_composition_open']);
        $this->assertSame('php fan ai:dynamic-boundaries --debt --json', $decoded['commands']['fan_ai_dynamic_boundaries_debt']);
        $this->assertSame('php fan ai:source-inventory --json', $decoded['commands']['fan_ai_source_inventory']);
    }

    public function testAiMapSchemaAndContractValidationPass(): void
    {
        $root = dirname(__DIR__, 2);
        $schema = json_decode((string)file_get_contents($root . '/.ai/map.schema.json'), true, 512, JSON_THROW_ON_ERROR);
        $map = php_fan_ai_build_map($root);

        $this->assertSame('PHP-FAN AI map', $schema['title']);
        $this->assertContains('services', $schema['required']);
        $this->assertContains('metadata', $schema['required']);
        $this->assertContains('source_inventory', $schema['required']);
        $this->assertSame('source_inventory.next', $schema['$defs']['sourceInventoryPolicy']['properties']['next_queue']['const']);
        $this->assertContains('bootstrap_boundary', $map['source_inventory']['policy']['classification_kinds']);
        $this->assertContains('line', $schema['properties']['services']['properties']['aliases']['additionalProperties']['required']);
        $this->assertContains('referenced_locations', $schema['properties']['services']['required']);
        $this->assertContains('referenced_by', $schema['$defs']['serviceDescriptor']['required']);
        $this->assertContains('aliases', $schema['$defs']['sourceEdges']['required']);
        $this->assertContains('aliases', $schema['$defs']['sourceLocations']['required']);
        $this->assertContains('category_reasons', $schema['properties']['dynamic_boundaries']['required']);
        $this->assertContains('locations', $schema['properties']['dynamic_boundaries']['required']);
        $this->assertContains('composition_leaf_policy', $schema['properties']['dynamic_boundaries']['required']);
        $this->assertContains('terminal_composition_leaves', $schema['properties']['dynamic_boundaries']['required']);
        $this->assertContains('actionable_composition_roots', $schema['properties']['dynamic_boundaries']['required']);
        $this->assertContains('named_migration_debt_policy', $schema['properties']['dynamic_boundaries']['required']);
        $this->assertContains('actionable_named_migration_debt', $schema['properties']['dynamic_boundaries']['required']);
        $this->assertContains('intentional_named_compatibility_boundaries', $schema['properties']['dynamic_boundaries']['required']);
        $this->assertSame(1, $schema['$defs']['dynamicBoundaryCompositionLeafPolicy']['properties']['terminal_count']['const']);
        $this->assertSame(2, $schema['$defs']['dynamicBoundaryCompositionLeafPolicy']['properties']['actionable_min_count']['const']);
        $this->assertSame('named_default_closure_boundary', $schema['$defs']['dynamicBoundaryNamedMigrationDebtPolicy']['properties']['boundary_kind']['const']);
        $this->assertSame('actionable_named_migration_debt', $schema['$defs']['dynamicBoundaryNamedMigrationDebtPolicy']['properties']['actionable_kind']['const']);
        $this->assertSame('intentional_named_compatibility_boundary', $schema['$defs']['dynamicBoundaryNamedMigrationDebtPolicy']['properties']['intentional_kind']['const']);
        $this->assertContains('boundary_kind', $schema['$defs']['dynamicBoundaryLocation']['required']);
        $this->assertSame(
            ['method_body_or_runtime_boundary', 'named_default_closure_boundary'],
            $schema['$defs']['dynamicBoundaryLocation']['properties']['boundary_kind']['enum']
        );
        $this->assertSame([], php_fan_ai_validate_map_contract($root, $map));
    }

    public function testAiServiceMapTracksAliasLineProvenance(): void
    {
        $root = sys_get_temp_dir() . '/php_fan_ai_alias_' . bin2hex(random_bytes(4));
        mkdir($root . '/core/di', 0777, true);
        file_put_contents($root . '/core/di/service_id.php', "<?php\nfinal class service_id { public const TARGET = 'target'; }\n");
        file_put_contents($root . '/core/di/test_service_registrar.php', <<<'PHP'
<?php

$container
    ->factory('target', static fn($container): object => (object)[])
    ->alias('target_alias', 'target');
PHP);

        try {
            $map = php_fan_ai_service_map($root, [
                'core/di/service_id.php',
                'core/di/test_service_registrar.php',
            ]);

            $this->assertSame([
                'target' => 'target',
                'file' => 'core/di/test_service_registrar.php',
                'line' => 5,
            ], $map['aliases']['target_alias']);
            $this->assertSame(['target_alias'], $map['descriptors']['target']['aliases']);
            $this->assertSame(['target_alias'], $map['descriptors']['target']['source_edges']['aliases']);
            $this->assertSame([
                [
                    'file' => 'core/di/test_service_registrar.php',
                    'line' => 5,
                    'value' => 'target_alias',
                ],
            ], $map['descriptors']['target']['source_locations']['aliases']);
        } finally {
            @unlink($root . '/core/di/test_service_registrar.php');
            @unlink($root . '/core/di/service_id.php');
            @rmdir($root . '/core/di');
            @rmdir($root . '/core');
            @rmdir($root);
        }
    }

    public function testAiServiceMapBuilderOwnsServiceDescriptorExtractionHelpers(): void
    {
        $root = dirname(__DIR__, 2);
        $mapSource = file_get_contents($root . '/tools/ai_map.php');
        $serviceMapSource = file_get_contents($root . '/tools/ai_service_map.php');

        $this->assertIsString($mapSource);
        $this->assertIsString($serviceMapSource);
        $this->assertStringContainsString('final class php_fan_ai_service_map_builder', $serviceMapSource);
        $this->assertStringContainsString('function php_fan_ai_service_descriptors(', $serviceMapSource);
        $this->assertStringContainsString('function php_fan_ai_service_creator_methods(', $serviceMapSource);
        $this->assertStringNotContainsString('function php_fan_ai_service_descriptors(', $mapSource);
        $this->assertStringNotContainsString('function php_fan_ai_extract_public_creator_methods(', $mapSource);
        $this->assertStringNotContainsString('function php_fan_ai_creator_method_arguments_from_parameters(', $mapSource);
        $this->assertStringNotContainsString('function php_fan_ai_container_dependencies_from_source(', $mapSource);
        $this->assertStringContainsString('function php_fan_ai_extract_public_creator_methods(', $serviceMapSource);
        $this->assertStringContainsString('function php_fan_ai_creator_method_arguments_from_parameters(', $serviceMapSource);
        $this->assertStringContainsString('function php_fan_ai_container_dependencies_from_source(', $serviceMapSource);
        $this->assertStringContainsString('new php_fan_ai_service_map_builder(', $mapSource);
    }

    public function testAiMapSchemaValidationRejectsDescriptorShapeDrift(): void
    {
        $root = dirname(__DIR__, 2);
        $map = php_fan_ai_build_map($root);
        unset($map['services']['descriptors'][service_id::CACHE]['creator_method_arguments']);

        $this->assertContains(
            'AI map JSON schema: $.services.descriptors.cache missing required key: creator_method_arguments',
            php_fan_ai_validate_map_contract($root, $map)
        );

        $map = php_fan_ai_build_map($root);
        unset($map['services']['descriptors'][service_id::CACHE]['source_edges']);

        $this->assertContains(
            'AI map JSON schema: $.services.descriptors.cache missing required key: source_edges',
            php_fan_ai_validate_map_contract($root, $map)
        );

        $map = php_fan_ai_build_map($root);
        unset($map['services']['descriptors'][service_id::CACHE]['source_locations']);

        $this->assertContains(
            'AI map JSON schema: $.services.descriptors.cache missing required key: source_locations',
            php_fan_ai_validate_map_contract($root, $map)
        );
    }

    public function testAiMapReferenceAndAliasEdgesStayConsistent(): void
    {
        $root = dirname(__DIR__, 2);
        $map = php_fan_ai_build_map($root);

        foreach ($map['services']['descriptors'] as $id => $descriptor) {
            $this->assertSame($descriptor['aliases'], $descriptor['source_edges']['aliases'], 'Alias source edges drifted for service: ' . $id);
            $aliasLocationValues = array_column($descriptor['source_locations']['aliases'], 'value');
            sort($aliasLocationValues);
            $descriptorAliases = $descriptor['aliases'];
            sort($descriptorAliases);
            $this->assertSame($descriptorAliases, $aliasLocationValues, 'Alias source locations drifted for service: ' . $id);

            $referencedByFiles = $descriptor['referenced_by']['files'];
            sort($referencedByFiles);
            $locationFiles = array_values(array_unique(array_column($descriptor['referenced_by']['locations'], 'file')));
            sort($locationFiles);
            $this->assertSame($referencedByFiles, $locationFiles, 'Referenced-by files drifted for service: ' . $id);
        }

        $file = 'core/di/application_infrastructure_service_creator.php';
        $explanation = php_fan_ai_explain_file($root, $file);
        foreach ($explanation['service_reference_locations'] as $id => $locations) {
            $expected = array_values(array_filter(
                $map['services']['referenced_locations'][$id] ?? [],
                static fn(array $location): bool => ($location['file'] ?? null) === $file
            ));
            $this->assertSame($expected, $locations, 'Explain service reference locations drifted for service: ' . $id);
        }
    }

    public function testDynamicBoundaryLocationsHaveCategoryReasonsAndExplainSubsets(): void
    {
        $root = dirname(__DIR__, 2);
        $map = php_fan_ai_build_map($root);

        $this->assertArrayNotHasKey('core/service/reflector.php', $map['dynamic_boundaries']['locations']);

        foreach ($map['dynamic_boundaries']['locations'] as $file => $locations) {
            $categoryDetails = php_fan_ai_dynamic_boundary_category_details_for_file($file);
            $this->assertIsArray($categoryDetails, 'Dynamic boundary locations missing category for: ' . $file);
            $this->assertNotSame('', $categoryDetails['reason'] ?? '', 'Dynamic boundary locations missing reason for: ' . $file);
            $this->assertNotSame([], $locations, 'Dynamic boundary locations must not be empty for: ' . $file);
            foreach ($locations as $location) {
                $this->assertContains(
                    $location['boundary_kind'] ?? null,
                    ['method_body_or_runtime_boundary', 'named_default_closure_boundary'],
                    'Dynamic boundary location missing kind for: ' . $file
                );
            }

            $explanation = php_fan_ai_explain_file($root, $file);
            $this->assertSame($locations, $explanation['dynamic_boundary_details']['locations'] ?? null, 'Explain dynamic locations drifted for: ' . $file);
        }
    }

    public function testDynamicBoundarySummaryHasStableShapeSortAndPatterns(): void
    {
        $summary = php_fan_ai_dynamic_boundary_summary([
            'dynamic_boundaries' => [
                'locations' => [
                    'core/block/base.php' => [
                        ['file' => 'core/block/base.php', 'line' => 20, 'pattern' => 'container_get', 'value' => '$container->get(', 'boundary_kind' => 'method_body_or_runtime_boundary'],
                    ],
                    'core/service/translation.php' => [
                        ['file' => 'core/service/translation.php', 'line' => 30, 'pattern' => 'class_exists', 'value' => 'class_exists(', 'boundary_kind' => 'named_default_closure_boundary'],
                        ['file' => 'core/service/translation.php', 'line' => 31, 'pattern' => 'class_exists', 'value' => 'class_exists(', 'boundary_kind' => 'named_default_closure_boundary'],
                    ],
                    'core/di/application_core_service_creator.php' => [
                        ['file' => 'core/di/application_core_service_creator.php', 'line' => 10, 'pattern' => 'container_get', 'value' => '$container->get(', 'boundary_kind' => 'method_body_or_runtime_boundary'],
                        ['file' => 'core/di/application_core_service_creator.php', 'line' => 11, 'pattern' => 'class_exists', 'value' => 'class_exists(', 'boundary_kind' => 'method_body_or_runtime_boundary'],
                    ],
                ],
            ],
        ]);

        $this->assertSame([
            'core/block/base.php',
            'core/di/application_core_service_creator.php',
            'core/service/translation.php',
        ], array_column($summary, 'file'));
        $this->assertNull($summary[0]['category']);
        $this->assertSame('composition_roots', $summary[1]['category']);
        $this->assertSame('extension_api', $summary[2]['category']);
        $this->assertSame('method_body_or_runtime_boundary', $summary[0]['boundary_kind']);
        $this->assertSame('method_body_or_runtime_boundary', $summary[1]['boundary_kind']);
        $this->assertSame('named_default_closure_boundary', $summary[2]['boundary_kind']);
        $this->assertNull($summary[0]['composition_leaf_kind']);
        $this->assertSame('actionable_composition_root', $summary[1]['composition_leaf_kind']);
        $this->assertNull($summary[2]['composition_leaf_kind']);
        $this->assertSame(1, $summary[0]['count']);
        $this->assertSame(2, $summary[1]['count']);
        $this->assertSame(['container_get'], $summary[0]['patterns']);
        $this->assertSame(['class_exists', 'container_get'], $summary[1]['patterns']);
        $this->assertSame(['class_exists'], $summary[2]['patterns']);
        $this->assertNotSame('', $summary[1]['reason']);

        $directSummary = php_fan_ai_dynamic_boundary_summary([
            'dynamic_boundaries' => [
                'locations' => [
                    'core/service/translation.php' => [
                        ['file' => 'core/service/translation.php', 'line' => 30, 'pattern' => 'class_exists', 'value' => 'class_exists(', 'boundary_kind' => 'named_default_closure_boundary'],
                    ],
                    'core/block/base.php' => [
                        ['file' => 'core/block/base.php', 'line' => 20, 'pattern' => 'container_get', 'value' => '$container->get(', 'boundary_kind' => 'method_body_or_runtime_boundary'],
                    ],
                ],
            ],
        ], 'method_body_or_runtime_boundary');
        $this->assertSame(['core/block/base.php'], array_column($directSummary, 'file'));

        $nextSummary = php_fan_ai_dynamic_boundary_summary([
            'dynamic_boundaries' => [
                'locations' => [
                    'core/di/application_core_service_creator.php' => [
                        ['file' => 'core/di/application_core_service_creator.php', 'line' => 10, 'pattern' => 'container_get', 'value' => '$container->get(', 'boundary_kind' => 'method_body_or_runtime_boundary'],
                    ],
                    'core/block/base.php' => [
                        ['file' => 'core/block/base.php', 'line' => 20, 'pattern' => 'container_get', 'value' => '$container->get(', 'boundary_kind' => 'method_body_or_runtime_boundary'],
                    ],
                ],
            ],
        ], 'method_body_or_runtime_boundary', ['composition_roots']);
        $this->assertSame(['core/block/base.php'], array_column($nextSummary, 'file'));

        $compositionSummary = php_fan_ai_dynamic_boundary_summary([
            'dynamic_boundaries' => [
                'locations' => [
                    'core/di/application_navigation_service_creator.php' => [
                        ['file' => 'core/di/application_navigation_service_creator.php', 'line' => 10, 'pattern' => 'container_get', 'value' => '$container->get(', 'boundary_kind' => 'method_body_or_runtime_boundary'],
                    ],
                    'core/di/application_core_service_creator.php' => [
                        ['file' => 'core/di/application_core_service_creator.php', 'line' => 10, 'pattern' => 'container_get', 'value' => '$container->get(', 'boundary_kind' => 'method_body_or_runtime_boundary'],
                        ['file' => 'core/di/application_core_service_creator.php', 'line' => 11, 'pattern' => 'container_get', 'value' => '$container->get(', 'boundary_kind' => 'method_body_or_runtime_boundary'],
                    ],
                    'core/block/base.php' => [
                        ['file' => 'core/block/base.php', 'line' => 20, 'pattern' => 'container_get', 'value' => '$container->get(', 'boundary_kind' => 'method_body_or_runtime_boundary'],
                    ],
                ],
            ],
        ], 'method_body_or_runtime_boundary', [], ['composition_roots'], true);
        $this->assertSame([
            'core/di/application_core_service_creator.php',
            'core/di/application_navigation_service_creator.php',
        ], array_column($compositionSummary, 'file'));
        $this->assertSame([
            'actionable_composition_root',
            'terminal_composition_leaf',
        ], array_column($compositionSummary, 'composition_leaf_kind'));

        $openCompositionSummary = php_fan_ai_dynamic_boundary_summary([
            'dynamic_boundaries' => [
                'locations' => [
                    'core/di/application_navigation_service_creator.php' => [
                        ['file' => 'core/di/application_navigation_service_creator.php', 'line' => 10, 'pattern' => 'container_get', 'value' => '$container->get(', 'boundary_kind' => 'method_body_or_runtime_boundary'],
                    ],
                    'core/di/application_core_service_creator.php' => [
                        ['file' => 'core/di/application_core_service_creator.php', 'line' => 10, 'pattern' => 'container_get', 'value' => '$container->get(', 'boundary_kind' => 'method_body_or_runtime_boundary'],
                        ['file' => 'core/di/application_core_service_creator.php', 'line' => 11, 'pattern' => 'container_get', 'value' => '$container->get(', 'boundary_kind' => 'method_body_or_runtime_boundary'],
                    ],
                ],
            ],
        ], 'method_body_or_runtime_boundary', [], ['composition_roots'], true, 2);
        $this->assertSame(['core/di/application_core_service_creator.php'], array_column($openCompositionSummary, 'file'));
        $this->assertSame(['actionable_composition_root'], array_column($openCompositionSummary, 'composition_leaf_kind'));

        $adapterReport = php_fan_ai_dynamic_boundary_summary_for_file([
            'dynamic_boundaries' => [
                'locations' => [
                    'core/adapter/zend_autoloader.php' => [
                        ['file' => 'core/adapter/zend_autoloader.php', 'line' => 20, 'pattern' => 'file_include', 'value' => 'require_once', 'boundary_kind' => 'named_default_closure_boundary'],
                    ],
                ],
            ],
        ], 'core/adapter/zend_autoloader.php');
        $this->assertSame('named_default_closure_boundary', $adapterReport['boundary_kind']);
        $this->assertSame(1, $adapterReport['count']);
        $this->assertSame('file_include', $adapterReport['locations'][0]['pattern']);
    }

    public function testArchitectureDocsDescribeServiceReferenceProvenance(): void
    {
        $source = (string)file_get_contents(dirname(__DIR__, 2) . '/.ai/architecture.md');

        $this->assertStringContainsString('## Service Reference Provenance', $source);
        $this->assertStringContainsString('referenced_by', $source);
        $this->assertStringContainsString('service_reference_locations', $source);
        $this->assertStringContainsString('services.referenced_locations', $source);
        $this->assertStringContainsString('source_locations.aliases', $source);
        $this->assertStringContainsString('source_edges.aliases', $source);
    }

    public function testCommandsDocsDescribeCompletionAudit(): void
    {
        $source = (string)file_get_contents(dirname(__DIR__, 2) . '/.ai/commands.md');

        $this->assertStringContainsString('## Completion Audit', $source);
        $this->assertStringContainsString('php fan ai:dynamic-boundaries --next --json', $source);
        $this->assertStringContainsString('php fan ai:dynamic-boundaries --composition-open --json', $source);
        $this->assertStringContainsString('php fan ai:dynamic-boundaries --debt --json', $source);
        $this->assertStringContainsString('php fan ai:source-inventory --next --json', $source);
        $this->assertStringContainsString('php tools/ai_verify.php --json', $source);
        $this->assertStringContainsString('git diff --check', $source);
        $this->assertStringContainsString('source-inventory --next`: `[]`', $source);
        $this->assertStringContainsString('unmanaged_container_lookups', $source);
        $this->assertStringContainsString('unmanaged_loading_statements', $source);
        $this->assertStringContainsString('classified and documented in `.ai/architecture.md`', $source);
    }

    public function testArchitectureDocsDescribeDiCreatorAvailabilityBoundaries(): void
    {
        $source = (string)file_get_contents(dirname(__DIR__, 2) . '/.ai/architecture.md');

        $this->assertStringContainsString('projectServiceClassExists(...)', $source);
        $this->assertStringContainsString('core/di/*_service_creator.php', $source);
        $this->assertStringContainsString('if (!class_exists($className))', $source);
        $this->assertStringContainsString('guarded as regressions', $source);
        $this->assertStringContainsString('dynamic_boundaries.locations', $source);
        $this->assertStringContainsString('dynamic_boundary_details.locations', $source);
        $this->assertStringContainsString('modelClassExists(...)', $source);
        $this->assertStringContainsString('`\ReflectionClass`', $source);
        $this->assertStringContainsString('not dynamic runtime debt by themselves', $source);
        $this->assertStringContainsString('Each location has', $source);
        $this->assertStringContainsString('boundary_kind = named_default_closure_boundary', $source);
        $this->assertStringContainsString('rank it below direct method-body debt', $source);
        $this->assertStringContainsString('php fan ai:dynamic-boundaries --direct --json', $source);
        $this->assertStringContainsString('php fan ai:dynamic-boundaries --next --json', $source);
        $this->assertStringContainsString('php fan ai:dynamic-boundaries --debt --json', $source);
        $this->assertStringContainsString('php fan ai:dynamic-boundaries --composition --json', $source);
        $this->assertStringContainsString('php fan ai:dynamic-boundaries --composition-open --json', $source);
        $this->assertStringContainsString('php fan ai:source-inventory --next --json', $source);
        $this->assertStringContainsString('When `php fan ai:source-inventory --next --json` returns `[]`', $source);
        $this->assertStringContainsString('completion audit', $source);
        $this->assertStringContainsString('`unmanaged_container_lookups`', $source);
        $this->assertStringContainsString('`bootstrap_container_lookups` remains a bootstrap/default-provider boundary', $source);
        $this->assertStringContainsString('`composition_container_lookups` remains a terminal composition-leaf boundary', $source);
        $this->assertStringContainsString('`unmanaged_loading_statements` must stay clean', $source);
        $this->assertStringContainsString('`272` one-lookup files', $source);
        $this->assertStringContainsString('`25` lookups across `7` files', $source);
        $this->assertStringContainsString('terminal_composition_leaf', $source);
        $this->assertStringContainsString('actionable_named_migration_debt', $source);
        $this->assertStringContainsString('intentional_named_compatibility_boundaries', $source);
        $this->assertStringContainsString('fan\core\ai\dynamic_boundary', $source);
        $this->assertStringContainsString('application_creator_common_dependencies', $source);
    }

    public function testAiExplainSummarizesSourceFile(): void
    {
        $root = dirname(__DIR__, 2);
        $map = php_fan_ai_build_map($root);
        $explanation = php_fan_ai_explain_file($root, 'core/base/model/request.php');

        $this->assertSame('core/base/model/request.php', $explanation['file']);
        $this->assertSame('php', $explanation['type']);
        $this->assertSame('fan\core\base\model', $explanation['namespace']);
        $this->assertSame('request', $explanation['classes'][0]['name']);
        $this->assertContains('unit/core/base/model/RequestTest.php', $explanation['related_tests']);
        $this->assertSame([], $explanation['related_service_ids']);
        $this->assertSame([], $explanation['related_service_descriptors']);
        $this->assertSame([], $explanation['service_reference_locations']);
        $this->assertFalse($explanation['dynamic_boundaries']['container_get']);
        $this->assertNull($explanation['dynamic_boundary_category']);
        $this->assertSame([], $explanation['dynamic_boundary_details']);

        $compositionExplanation = php_fan_ai_explain_file($root, 'core/di/application_infrastructure_service_creator.php');
        $this->assertSame('composition_roots', $compositionExplanation['dynamic_boundary_category']);
        $this->assertContains(service_id::CACHE, $compositionExplanation['related_service_ids']);
        $this->assertContains(service_id::CONFIG, $compositionExplanation['related_service_ids']);
        $this->assertArrayHasKey(service_id::CONFIG, $compositionExplanation['service_reference_locations']);
        $this->assertContains(
            $this->sourceLineContainingAfter(
                'core/di/application_infrastructure_service_creator.php',
                'public function createCacheService',
                '$config = $infrastructureDependencies->config();'
            ),
            array_column($compositionExplanation['service_reference_locations'][service_id::CONFIG], 'line')
        );
        $this->assertSame('composition_roots', $compositionExplanation['dynamic_boundary_details']['category']);
        $this->assertSame('core/di/application_infrastructure_service_creator.php', $compositionExplanation['dynamic_boundary_details']['matched_entry']);
        $this->assertSame('named_default_closure_boundary', $compositionExplanation['dynamic_boundary_details']['boundary_kind']);
        $this->assertNotSame('', $compositionExplanation['dynamic_boundary_details']['reason']);
        $this->assertContains('class_exists', $compositionExplanation['dynamic_boundary_details']['patterns']);
        $this->assertSame(
            $map['dynamic_boundaries']['locations']['core/di/application_infrastructure_service_creator.php'],
            $compositionExplanation['dynamic_boundary_details']['locations']
        );
        $this->assertSame(
            $this->sourceLineContaining('core/di/application_infrastructure_service_creator.php', 'class_exists($className)'),
            $compositionExplanation['dynamic_boundary_details']['locations'][0]['line']
        );
        $this->assertArrayHasKey(service_id::CACHE, $compositionExplanation['related_service_descriptors']);
        $this->assertSame('\fan\project\service\cache', $compositionExplanation['related_service_descriptors'][service_id::CACHE]['class']);
        $this->assertContains(service_id::CONFIG, $compositionExplanation['related_service_descriptors'][service_id::CACHE]['dependencies']);

        $registrarExplanation = php_fan_ai_explain_file($root, 'core/di/application_infrastructure_service_registrar.php');
        $this->assertContains(service_id::CACHE, $registrarExplanation['related_service_ids']);
        $this->assertContains(service_id::JSON, $registrarExplanation['related_service_ids']);
        $this->assertSame(service_id::JSON, $registrarExplanation['related_service_descriptors'][service_id::JSON]['id']);

        $extensionExplanation = php_fan_ai_explain_file($root, 'core/service/translation.php');
        $this->assertSame('extension_api', $extensionExplanation['dynamic_boundary_category']);
        $this->assertSame('extension_api', $extensionExplanation['dynamic_boundary_details']['category']);
        $this->assertSame('named_default_closure_boundary', $extensionExplanation['dynamic_boundary_details']['boundary_kind']);

        $adapterClosureExplanation = php_fan_ai_explain_file($root, 'core/adapter/zend_autoloader.php');
        $this->assertSame('named_default_closure_boundary', $adapterClosureExplanation['dynamic_boundary_details']['boundary_kind']);

        $reflectorExplanation = php_fan_ai_explain_file($root, 'core/service/reflector.php');
        $this->assertSame([], $reflectorExplanation['dynamic_boundary_details']);
    }

    public function testAiVerifyFastChecksPassForCurrentWorkspace(): void
    {
        $result = php_fan_ai_verify(dirname(__DIR__, 2), ['tools/ai_verify.php', '--skip-phpunit']);

        $this->assertSame('pass', $result['status']);
        $this->assertSame(
            ['tracked_noise', 'git_diff_check', 'php_lint_changed', 'ai_map_build', 'static_baseline'],
            array_column($result['checks'], 'name')
        );
    }

    public function testAiVerifyNoPhpunitAliasMatchesSkipPhpunit(): void
    {
        $result = php_fan_ai_verify(dirname(__DIR__, 2), ['tools/ai_verify.php', '--no-phpunit']);

        $this->assertSame('pass', $result['status']);
        $this->assertSame(
            ['tracked_noise', 'git_diff_check', 'php_lint_changed', 'ai_map_build', 'static_baseline'],
            array_column($result['checks'], 'name')
        );
    }

    public function testFanCliBridgeRunsAiCommands(): void
    {
        $root = dirname(__DIR__, 2);

        $mapResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:map', '--json'], $root);
        $this->assertSame(0, $mapResult['exit_code'], $mapResult['stderr']);
        $map = json_decode($mapResult['stdout'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(1, $map['schema_version']);

        $mapValidationResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:map', '--validate', '--json'], $root);
        $this->assertSame(0, $mapValidationResult['exit_code'], $mapValidationResult['stderr']);
        $mapValidation = json_decode($mapValidationResult['stdout'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('pass', $mapValidation['status']);
        $this->assertSame([], $mapValidation['errors']);

        $servicesResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:services', '--json'], $root);
        $this->assertSame(0, $servicesResult['exit_code'], $servicesResult['stderr']);
        $services = json_decode($servicesResult['stdout'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey(service_id::REQUEST, $services['descriptors']);

        $cacheServiceResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:services', service_id::CACHE, '--json'], $root);
        $this->assertSame(0, $cacheServiceResult['exit_code'], $cacheServiceResult['stderr']);
        $cacheService = json_decode($cacheServiceResult['stdout'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(service_id::CACHE, $cacheService['id']);
        $this->assertSame('\fan\project\service\cache', $cacheService['class']);
        $this->assertArrayHasKey('source_locations', $cacheService);

        $cacheServiceTextResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:services', service_id::CACHE], $root);
        $this->assertSame(0, $cacheServiceTextResult['exit_code'], $cacheServiceTextResult['stderr']);
        $this->assertStringContainsString('cache [non-shared] deps:', $cacheServiceTextResult['stdout']);
        $this->assertStringContainsString('class: \fan\project\service\cache', $cacheServiceTextResult['stdout']);

        $missingServiceResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:services', 'missing_service_id', '--json'], $root);
        $this->assertSame(1, $missingServiceResult['exit_code'], $missingServiceResult['stdout']);
        $this->assertSame('', $missingServiceResult['stdout']);
        $this->assertStringContainsString('Unknown service id: missing_service_id', $missingServiceResult['stderr']);

        $explainResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:explain', 'core/base/model/request.php', '--json'], $root);
        $this->assertSame(0, $explainResult['exit_code'], $explainResult['stderr']);
        $explanation = json_decode($explainResult['stdout'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('core/base/model/request.php', $explanation['file']);
        $this->assertArrayHasKey('service_reference_locations', $explanation);
        $this->assertArrayHasKey('dynamic_boundary_category', $explanation);
        $this->assertArrayHasKey('dynamic_boundary_details', $explanation);
        $this->assertArrayHasKey('related_service_ids', $explanation);
        $this->assertArrayHasKey('related_service_descriptors', $explanation);

        $sourceInventoryResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:source-inventory', '--json'], $root);
        $this->assertSame(0, $sourceInventoryResult['exit_code'], $sourceInventoryResult['stderr']);
        $sourceInventory = json_decode($sourceInventoryResult['stdout'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('source_inventory.next', $sourceInventory['policy']['next_queue']);
        $this->assertSame('clean', $sourceInventory['queues']['service_locator_calls']['classification']);
        $this->assertSame(0, $sourceInventory['queues']['unmanaged_loading_statements']['count']);
        $this->assertSame('bootstrap_boundary', $sourceInventory['queues']['bootstrap_container_lookups']['classification']);
        $this->assertSame(25, $sourceInventory['queues']['bootstrap_container_lookups']['count']);
        $this->assertSame('composition_boundary', $sourceInventory['queues']['composition_container_lookups']['classification']);
        $this->assertSame(272, $sourceInventory['queues']['composition_container_lookups']['count']);
        $this->assertSame('intentional_compatibility', $sourceInventory['queues']['intentional_loading_boundaries']['classification']);
        $this->assertSame(12, $sourceInventory['queues']['intentional_loading_boundaries']['count']);
        $this->assertSame('clean', $sourceInventory['queues']['unmanaged_container_lookups']['classification']);
        $this->assertSame(0, $sourceInventory['queues']['unmanaged_container_lookups']['count']);
        $this->assertSame([], $sourceInventory['next']);

        $sourceInventoryNextResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:source-inventory', '--next', '--json'], $root);
        $this->assertSame(0, $sourceInventoryNextResult['exit_code'], $sourceInventoryNextResult['stderr']);
        $sourceInventoryNext = json_decode($sourceInventoryNextResult['stdout'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame([], array_column($sourceInventoryNext, 'id'));

        $sourceInventoryQueueResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:source-inventory', 'unmanaged_loading_statements', '--json'], $root);
        $this->assertSame(0, $sourceInventoryQueueResult['exit_code'], $sourceInventoryQueueResult['stderr']);
        $sourceInventoryQueue = json_decode($sourceInventoryQueueResult['stdout'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('unmanaged_loading_statements', $sourceInventoryQueue['id']);
        $this->assertSame('clean', $sourceInventoryQueue['classification']);

        $missingSourceInventoryQueueResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:source-inventory', 'missing_queue', '--json'], $root);
        $this->assertSame(1, $missingSourceInventoryQueueResult['exit_code'], $missingSourceInventoryQueueResult['stdout']);
        $this->assertStringContainsString('Unknown source-inventory queue: missing_queue', $missingSourceInventoryQueueResult['stderr']);

        $dynamicBoundariesResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:dynamic-boundaries', '--json'], $root);
        $this->assertSame(0, $dynamicBoundariesResult['exit_code'], $dynamicBoundariesResult['stderr']);
        $dynamicBoundaries = json_decode($dynamicBoundariesResult['stdout'], true, 512, JSON_THROW_ON_ERROR);
        $dynamicBoundaryFiles = array_column($dynamicBoundaries, 'file');
        $this->assertContains('core/base/model/entity_dependencies.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_defaults.php', $dynamicBoundaryFiles);
        $this->assertNotContains('core/base/model/entity.php', $dynamicBoundaryFiles);
        $this->assertNotContains('core/block/base.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_database_application_data_factory_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_user_application_data_factory_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_application_service_runtime_factory_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_config_application_runtime_factory_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_error_application_support_factory_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_date_application_support_factory_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_array_adducer_helper_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_recursive_merger_helper_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_array_value_reader_helper_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_array_like_checker_helper_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_block_factory_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_block_exception_factory_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_class_helper_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_entity_data_core_factory_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_json_data_core_factory_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_data_loader_service_factory_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_pager_data_loader_factory_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_image_metadata_media_error_helper_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_error_log_media_error_helper_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_obfuscator_media_core_factory_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_image_modify_media_core_factory_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_media_transfer_factory_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_meta_maker_state_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_meta_maker_factory_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_php_array_file_loader_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_meta_row_factory_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_reflector_context_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_request_input_context_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_request_factory_context_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_role_factory_context_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_locale_factory_context_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_matcher_factory_context_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_session_context_group.php', $dynamicBoundaryFiles);

        $openCompositionResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:dynamic-boundaries', '--composition-open', '--json'], $root);
        $this->assertSame(0, $openCompositionResult['exit_code'], $openCompositionResult['stderr']);
        $this->assertSame([], json_decode($openCompositionResult['stdout'], true, 512, JSON_THROW_ON_ERROR));

        $debtResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:dynamic-boundaries', '--debt', '--json'], $root);
        $this->assertSame(0, $debtResult['exit_code'], $debtResult['stderr']);
        $debt = json_decode($debtResult['stdout'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame([], $debt);

        $blockDebtResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:dynamic-boundaries', 'core/block/base.php', '--debt', '--json'], $root);
        $this->assertSame(0, $blockDebtResult['exit_code'], $blockDebtResult['stderr']);
        $blockDebt = json_decode($blockDebtResult['stdout'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('core/block/base.php', $blockDebt['file']);
        $this->assertSame(0, $blockDebt['count']);
        $this->assertSame('actionable_named_migration_debt', $blockDebt['named_debt_kind']);
        $this->assertSame([], $blockDebt['locations']);
        $this->assertContains('core/block/base_dependency_tab_service_runtime_context_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_bootstrap_runtime_context_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_block_file_storage_block_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_block_file_storage_meta_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_project_tool_file_storage_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_root_html_file_storage_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_upload_limit_storage_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_view_parser_exception_factory_loader_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_view_router_factory_loader_group.php', $dynamicBoundaryFiles);
        $this->assertContains('core/block/base_dependency_view_state_loader_group.php', $dynamicBoundaryFiles);
        $blockDefaultsBoundary = $dynamicBoundaries[array_search('core/block/base_dependency_defaults.php', $dynamicBoundaryFiles, true)];
        $this->assertSame('extension_api', $blockDefaultsBoundary['category']);
        $this->assertSame('named_default_closure_boundary', $blockDefaultsBoundary['boundary_kind']);
        $this->assertContains('class_exists', $blockDefaultsBoundary['patterns']);
        $blockGroupBoundary = $dynamicBoundaries[array_search('core/block/base_dependency_tab_service_runtime_context_group.php', $dynamicBoundaryFiles, true)];
        $this->assertSame('composition_roots', $blockGroupBoundary['category']);
        $this->assertSame('method_body_or_runtime_boundary', $blockGroupBoundary['boundary_kind']);
        $this->assertContains('container_get', $blockGroupBoundary['patterns']);

        $dynamicBoundariesTextResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:dynamic-boundaries'], $root);
        $this->assertSame(0, $dynamicBoundariesTextResult['exit_code'], $dynamicBoundariesTextResult['stderr']);
        $this->assertStringContainsString('core/block/base_dependency_defaults.php [extension_api]', $dynamicBoundariesTextResult['stdout']);

        $dynamicBoundariesDirectResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:dynamic-boundaries', '--direct', '--json'], $root);
        $this->assertSame(0, $dynamicBoundariesDirectResult['exit_code'], $dynamicBoundariesDirectResult['stderr']);
        $dynamicBoundariesDirect = json_decode($dynamicBoundariesDirectResult['stdout'], true, 512, JSON_THROW_ON_ERROR);
        $directFiles = array_column($dynamicBoundariesDirect, 'file');
        $this->assertContains('core/block/base_dependency_database_application_data_factory_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_user_application_data_factory_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_application_service_runtime_factory_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_config_application_runtime_factory_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_error_application_support_factory_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_date_application_support_factory_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_array_adducer_helper_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_recursive_merger_helper_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_array_value_reader_helper_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_array_like_checker_helper_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_block_factory_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_block_exception_factory_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_class_helper_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_entity_data_core_factory_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_json_data_core_factory_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_data_loader_service_factory_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_pager_data_loader_factory_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_image_metadata_media_error_helper_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_error_log_media_error_helper_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_obfuscator_media_core_factory_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_image_modify_media_core_factory_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_media_transfer_factory_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_meta_maker_state_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_meta_maker_factory_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_php_array_file_loader_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_meta_row_factory_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_reflector_context_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_request_input_context_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_request_factory_context_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_role_factory_context_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_locale_factory_context_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_matcher_factory_context_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_session_context_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_tab_service_runtime_context_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_bootstrap_runtime_context_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_block_file_storage_block_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_block_file_storage_meta_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_project_tool_file_storage_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_root_html_file_storage_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_upload_limit_storage_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_view_parser_exception_factory_loader_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_view_router_factory_loader_group.php', $directFiles);
        $this->assertContains('core/block/base_dependency_view_state_loader_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_context_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_application_factory_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_application_runtime_factory_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_application_support_factory_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_array_helper_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_array_read_helper_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_array_transform_helper_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_block_factory_context_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_block_file_storage_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_data_core_factory_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_data_factory_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_data_loader_factory_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_helper_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_media_core_factory_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_media_error_helper_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_media_factory_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_meta_loader_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_meta_maker_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_meta_row_loader_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_navigation_context_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_project_file_storage_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_request_context_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_request_role_context_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_route_locale_context_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_runtime_context_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_storage_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_tab_runtime_context_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_view_factory_loader_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_view_loader_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_view_meta_group.php', $directFiles);
        $this->assertNotContains('core/block/base_dependency_resolver.php', $directFiles);
        $this->assertNotContains('core/adapter/bootstrap_loader_file_storage.php', $directFiles);
        $this->assertNotContains('core/service/locale.php', $directFiles);
        $this->assertNotContains('core/service/tab.php', $directFiles);
        $this->assertNotContains('core/service/timer.php', $directFiles);
        $this->assertNotContains('core/base/transfer/int.php', $directFiles);
        $this->assertNotContains('core/adapter/zend_autoloader.php', $directFiles);

        $dynamicBoundariesNextResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:dynamic-boundaries', '--next', '--json'], $root);
        $this->assertSame(0, $dynamicBoundariesNextResult['exit_code'], $dynamicBoundariesNextResult['stderr']);
        $dynamicBoundariesNext = json_decode($dynamicBoundariesNextResult['stdout'], true, 512, JSON_THROW_ON_ERROR);
        $nextFiles = array_column($dynamicBoundariesNext, 'file');
        $this->assertNotContains('core/block/base_dependency_resolver.php', $nextFiles);
        $this->assertNotContains('core/block/base_dependency_context_group.php', $nextFiles);
        foreach ($dynamicBoundariesNext as $entry) {
            $this->assertNotSame('composition_roots', $entry['category'] ?? null);
            $this->assertSame('method_body_or_runtime_boundary', $entry['boundary_kind'] ?? null);
        }

        $dynamicBoundariesCompositionResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:dynamic-boundaries', '--composition', '--json'], $root);
        $this->assertSame(0, $dynamicBoundariesCompositionResult['exit_code'], $dynamicBoundariesCompositionResult['stderr']);
        $dynamicBoundariesComposition = json_decode($dynamicBoundariesCompositionResult['stdout'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertNotSame([], $dynamicBoundariesComposition);
        $compositionCounts = array_column($dynamicBoundariesComposition, 'count');
        $sortedCompositionCounts = $compositionCounts;
        rsort($sortedCompositionCounts);
        $this->assertSame($sortedCompositionCounts, $compositionCounts);
        foreach ($dynamicBoundariesComposition as $entry) {
            $this->assertSame('composition_roots', $entry['category'] ?? null);
        }

        $dynamicBoundaryFileResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:dynamic-boundaries', 'core/block/base.php', '--json'], $root);
        $this->assertSame(0, $dynamicBoundaryFileResult['exit_code'], $dynamicBoundaryFileResult['stderr']);
        $dynamicBoundaryFile = json_decode($dynamicBoundaryFileResult['stdout'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('core/block/base.php', $dynamicBoundaryFile['file']);
        $this->assertNull($dynamicBoundaryFile['category']);
        $this->assertSame('method_body_or_runtime_boundary', $dynamicBoundaryFile['boundary_kind']);
        $this->assertSame([], $dynamicBoundaryFile['locations']);

        $dynamicBoundaryDirectFileResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:dynamic-boundaries', 'core/adapter/zend_autoloader.php', '--direct', '--json'], $root);
        $this->assertSame(0, $dynamicBoundaryDirectFileResult['exit_code'], $dynamicBoundaryDirectFileResult['stderr']);
        $dynamicBoundaryDirectFile = json_decode($dynamicBoundaryDirectFileResult['stdout'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('core/adapter/zend_autoloader.php', $dynamicBoundaryDirectFile['file']);
        $this->assertSame(0, $dynamicBoundaryDirectFile['count']);
        $this->assertSame([], $dynamicBoundaryDirectFile['locations']);

        $dynamicBoundaryFileTextResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:dynamic-boundaries', 'core/block/base.php'], $root);
        $this->assertSame(0, $dynamicBoundaryFileTextResult['exit_code'], $dynamicBoundaryFileTextResult['stderr']);
        $this->assertStringContainsString('core/block/base.php [uncategorized]', $dynamicBoundaryFileTextResult['stdout']);
        $this->assertStringContainsString('0 location(s): none', $dynamicBoundaryFileTextResult['stdout']);

        $doctorResult = php_fan_ai_verify_run([PHP_BINARY, 'fan', 'ai:doctor', '--no-phpunit'], $root);
        $this->assertSame(0, $doctorResult['exit_code'], $doctorResult['stdout'] . $doctorResult['stderr']);
    }

    private function sourceLocationLine(array $descriptor, string $section, string $value): int
    {
        foreach ($descriptor['source_locations'][$section] as $location) {
            if (($location['value'] ?? null) === $value) {
                return $location['line'];
            }
        }

        $this->fail('Missing source location for value: ' . $value);
    }

    private function dynamicBoundaryLocationLine(array $map, string $file, string $pattern): int
    {
        foreach ($map['dynamic_boundaries']['locations'][$file] ?? [] as $location) {
            if (($location['pattern'] ?? null) === $pattern) {
                return $location['line'];
            }
        }

        $this->fail('Missing dynamic boundary location for file/pattern: ' . $file . ' / ' . $pattern);
    }

    /**
     * @return list<int>
     */
    private function serviceReferenceLocationLines(array $map, string $serviceId, string $file): array
    {
        $lines = [];
        foreach ($map['services']['referenced_locations'][$serviceId] ?? [] as $location) {
            if (($location['file'] ?? null) === $file && ($location['value'] ?? null) === $serviceId) {
                $lines[] = $location['line'];
            }
        }

        if ($lines === []) {
            $this->fail('Missing referenced service location for service id: ' . $serviceId);
        }

        return $lines;
    }

    private function sourceLineContaining(string $relativeFile, string $needle): int
    {
        $lines = file(dirname(__DIR__, 2) . '/' . $relativeFile);
        $this->assertIsArray($lines);
        foreach ($lines as $lineNumber => $line) {
            if (str_contains($line, $needle)) {
                return $lineNumber + 1;
            }
        }

        $this->fail('Missing source line containing: ' . $needle);
    }

    private function sourceLineContainingAfter(string $relativeFile, string $startNeedle, string $needle): int
    {
        $lines = file(dirname(__DIR__, 2) . '/' . $relativeFile);
        $this->assertIsArray($lines);
        $started = false;
        foreach ($lines as $lineNumber => $line) {
            if (!$started && str_contains($line, $startNeedle)) {
                $started = true;
            }
            if ($started && str_contains($line, $needle)) {
                return $lineNumber + 1;
            }
        }

        $this->fail('Missing source line containing after marker: ' . $needle);
    }
}
