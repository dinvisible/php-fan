<?php

declare(strict_types=1);

/**
 * Builds a machine-readable project map for AI coding agents.
 *
 * Usage:
 *   php tools/ai_map.php --json
 *   php tools/ai_map.php --write
 *   php tools/ai_map.php --validate
 */

require_once __DIR__ . '/../core/di/service_descriptor.php';
require_once __DIR__ . '/../core/ai/dynamic_boundary.php';
require_once __DIR__ . '/ai_service_map.php';

function php_fan_ai_build_map(string $root): array
{
    $root = rtrim($root, DIRECTORY_SEPARATOR);
    $sourceRoots = ['core', 'project', 'htdocs', 'tools'];
    $testRoots = ['unit'];
    $phpFiles = php_fan_ai_php_files($root, array_merge($sourceRoots, $testRoots));
    $productionPhpFiles = array_values(array_filter(
        $phpFiles,
        static fn(string $file): bool => !str_starts_with($file, 'unit/')
    ));
    $serviceMap = php_fan_ai_service_map($root, $productionPhpFiles);
    $metaFiles = php_fan_ai_meta_files($root);
    $templateFiles = php_fan_ai_template_files($root);

    return [
        'schema_version' => 1,
        'generated_at' => gmdate(DATE_ATOM),
        'entrypoints' => [
            'web' => 'htdocs/index.php',
        ],
        'source_roots' => $sourceRoots,
        'test_roots' => $testRoots,
        'autoload' => php_fan_ai_composer_autoload($root),
        'ai_docs' => php_fan_ai_files($root, ['.ai'], static fn(string $file): bool => str_ends_with($file, '.md')),
        'commands' => [
            'test' => 'php vendor/bin/phpunit --configuration phpunit.xml',
            'ai_map_json' => 'php tools/ai_map.php --json',
            'ai_map_validate' => 'php tools/ai_map.php --validate',
            'ai_explain' => 'php tools/ai_explain.php <file> --json',
            'ai_verify' => 'php tools/ai_verify.php',
            'ai_static' => 'php tools/ai_static_check.php',
            'fan_ai_map' => 'php fan ai:map --json',
            'fan_ai_map_validate' => 'php fan ai:map --validate',
            'fan_ai_services' => 'php fan ai:services --json',
            'fan_ai_explain' => 'php fan ai:explain <file> --json',
            'fan_ai_verify' => 'php fan ai:verify',
            'fan_ai_doctor' => 'php fan ai:doctor',
            'fan_ai_dynamic_boundaries' => 'php fan ai:dynamic-boundaries --json',
            'fan_ai_dynamic_boundaries_composition_open' => 'php fan ai:dynamic-boundaries --composition-open --json',
            'fan_ai_dynamic_boundaries_debt' => 'php fan ai:dynamic-boundaries --debt --json',
            'fan_ai_source_inventory' => 'php fan ai:source-inventory --json',
        ],
        'counts' => [
            'php_files' => count($phpFiles),
            'production_php_files' => count($productionPhpFiles),
            'strict_production_php_files' => php_fan_ai_count_strict_files($root, $productionPhpFiles),
            'meta_files' => count($metaFiles),
            'template_files' => count($templateFiles),
            'registered_service_ids' => count($serviceMap['registered']),
            'referenced_service_ids' => count($serviceMap['referenced']),
            'service_descriptors' => count($serviceMap['descriptors']),
        ],
        'files' => [
            'meta' => $metaFiles,
            'templates' => $templateFiles,
        ],
        'services' => $serviceMap,
        'metadata' => php_fan_ai_metadata_map($root, $metaFiles, $templateFiles),
        'dynamic_boundaries' => php_fan_ai_dynamic_boundaries_map($root, $productionPhpFiles),
        'source_inventory' => php_fan_ai_source_inventory_map($root, $productionPhpFiles),
        'verification' => [
            'phpunit' => 'php vendor/bin/phpunit --configuration phpunit.xml',
            'diff_check' => 'git diff --check',
        ],
    ];
}

function php_fan_ai_metadata_map(string $root, array $metaFiles, array $templateFiles): array
{
    $metaEntries = [];
    $topLevelKeyUsage = [];
    $ownKeyUsage = [];
    $commonKeyUsage = [];

    foreach ($metaFiles as $relativeFile) {
        $data = php_fan_ai_meta_file_data($root, $relativeFile);
        $topLevelKeys = array_keys($data);
        $ownKeys = isset($data['own']) && is_array($data['own']) ? array_keys($data['own']) : [];
        $commonKeys = isset($data['common']) && is_array($data['common']) ? array_keys($data['common']) : [];

        php_fan_ai_increment_usage($topLevelKeyUsage, $topLevelKeys);
        php_fan_ai_increment_usage($ownKeyUsage, $ownKeys);
        php_fan_ai_increment_usage($commonKeyUsage, $commonKeys);

        $metaEntries[$relativeFile] = [
            'top_level_keys' => $topLevelKeys,
            'own_keys' => $ownKeys,
            'common_keys' => $commonKeys,
            'paired_template' => php_fan_ai_paired_template($relativeFile, $templateFiles),
        ];
    }

    $templateEntries = [];
    $placeholderUsage = [];
    foreach ($templateFiles as $relativeFile) {
        $placeholders = php_fan_ai_template_placeholders($root, $relativeFile);
        php_fan_ai_increment_usage($placeholderUsage, $placeholders);
        $templateEntries[$relativeFile] = [
            'placeholders' => $placeholders,
            'paired_meta' => php_fan_ai_paired_meta_file($relativeFile, $metaFiles),
        ];
    }

    ksort($metaEntries);
    ksort($templateEntries);
    ksort($topLevelKeyUsage);
    ksort($ownKeyUsage);
    ksort($commonKeyUsage);
    ksort($placeholderUsage);

    return [
        'meta_schema' => '.ai/meta.schema.json',
        'meta' => [
            'files' => $metaEntries,
            'top_level_key_usage' => $topLevelKeyUsage,
            'own_key_usage' => $ownKeyUsage,
            'common_key_usage' => $commonKeyUsage,
        ],
        'templates' => [
            'files' => $templateEntries,
            'placeholder_usage' => $placeholderUsage,
        ],
    ];
}

function php_fan_ai_dynamic_boundaries_map(?string $root = null, ?array $productionPhpFiles = null): array
{
    $map = [
        'manual_loading_adapters' => [
            'core/adapter/php_array_file.php',
            'core/adapter/php_template_file.php',
            'core/adapter/bootstrap_loader_file_storage.php',
            'core/adapter/compiled_template_loader.php',
            'core/adapter/project_tool_loader.php',
            'core/adapter/error_demonstrator_loader.php',
            'core/adapter/zend_autoloader.php',
        ],
        'legacy_metadata' => '*.meta.php',
        'legacy_templates' => '*.tpl',
        'request_globals_adapter' => 'core/adapter/request_input_native_environment.php',
        'category_reasons' => [
            'extension_api' => 'Documented extension/loading APIs intentionally use dynamic classes, includes, templates, metadata, or runtime-discovered project code.',
            'composition_roots' => 'Composition roots wire configured services and project service classes; dynamic checks are allowed behind named boundaries.',
            'tooling_support' => 'Developer tooling may inspect Composer/runtime metadata dynamically without affecting production service behavior.',
            'migration_debt' => 'Legacy dynamic runtime behavior is inventoried for later reduction and must not spread silently.',
        ],
        'categories' => [
            'extension_api' => [
                '*.meta.php',
                '*.tpl',
                'core/adapter/bootstrap_loader_file_storage.php',
                'core/adapter/compiled_template_loader.php',
                'core/adapter/pear_http_session.php',
                'core/adapter/project_tool_loader.php',
                'core/adapter/twig_template_service.php',
                'core/adapter/zend_autoloader.php',
                'core/base/model/entity_dependencies.php',
                'core/base/transfer/int.php',
                'core/block/base_dependency_defaults.php',
                'core/service/locale.php',
                'core/service/plain.php',
                'core/service/block_context.php',
                'core/service/tab.php',
                'core/service/timer.php',
                'core/service/translation.php',
            ],
            'composition_roots' => [
                'core/di/application_client_service_dependencies.php',
                'core/di/application_client_service_creator.php',
                'core/di/application_client_array_adducer_payload_dependencies.php',
                'core/di/application_client_array_payload_dependencies.php',
                'core/di/application_client_array_value_reader_payload_dependencies.php',
                'core/di/application_client_bootstrap_runtime_dependencies.php',
                'core/di/application_client_cache_runtime_dependencies.php',
                'core/di/application_client_config_cache_runtime_dependencies.php',
                'core/di/application_client_config_runtime_dependencies.php',
                'core/di/application_client_cookie_state_registrar_dependencies.php',
                'core/di/application_client_curl_adapter_transport_dependencies.php',
                'core/di/application_client_curl_factory_transport_dependencies.php',
                'core/di/application_client_curl_state_registrar_dependencies.php',
                'core/di/application_client_curl_transport_dependencies.php',
                'core/di/application_client_error_transport_dependencies.php',
                'core/di/application_client_payload_dependencies.php',
                'core/di/application_client_request_payload_dependencies.php',
                'core/di/application_client_rest_state_registrar_dependencies.php',
                'core/di/application_client_runtime_dependencies.php',
                'core/di/application_client_cookie_writer_payload_dependencies.php',
                'core/di/application_client_serialization_payload_dependencies.php',
                'core/di/application_client_serialization_transport_dependencies.php',
                'core/di/application_client_serializer_operations_payload_dependencies.php',
                'core/di/application_client_service_registrar_dependencies.php',
                'core/di/application_client_transport_dependencies.php',
                'core/di/application_content_context_dependencies.php',
                'core/di/application_content_bootstrap_runtime_dependencies.php',
                'core/di/application_content_block_context_dependencies.php',
                'core/di/application_content_cache_runtime_dependencies.php',
                'core/di/application_content_config_cache_runtime_dependencies.php',
                'core/di/application_content_config_runtime_dependencies.php',
                'core/di/application_content_error_block_context_dependencies.php',
                'core/di/application_content_error_context_dependencies.php',
                'core/di/application_content_locale_context_dependencies.php',
                'core/di/application_content_localization_context_dependencies.php',
                'core/di/application_content_matcher_context_dependencies.php',
                'core/di/application_content_request_input_context_dependencies.php',
                'core/di/application_content_request_matcher_context_dependencies.php',
                'core/di/application_content_runtime_dependencies.php',
                'core/di/application_content_service_dependencies.php',
                'core/di/application_content_service_creator.php',
                'core/di/application_content_php_array_file_loader_storage_dependencies.php',
                'core/di/application_content_storage_dependencies.php',
                'core/di/application_content_tab_factory_context_dependencies.php',
                'core/di/application_content_translation_file_storage_dependencies.php',
                'core/di/application_controller_bootstrap_runtime_dependencies.php',
                'core/di/application_controller_cache_runtime_dependencies.php',
                'core/di/application_controller_config_cache_runtime_dependencies.php',
                'core/di/application_controller_config_runtime_dependencies.php',
                'core/di/application_controller_handler_dependencies.php',
                'core/di/application_controller_header_plain_route_dependencies.php',
                'core/di/application_controller_matcher_plain_route_dependencies.php',
                'core/di/application_controller_obfuscator_factory_handler_dependencies.php',
                'core/di/application_controller_obfuscator_handler_dependencies.php',
                'core/di/application_controller_plain_config_dependencies.php',
                'core/di/application_controller_plain_dependencies.php',
                'core/di/application_controller_plain_file_handler_dependencies.php',
                'core/di/application_controller_plain_route_dependencies.php',
                'core/di/application_controller_request_handler_dependencies.php',
                'core/di/application_controller_runtime_dependencies.php',
                'core/di/application_controller_service_dependencies.php',
                'core/di/application_controller_service_creator.php',
                'core/di/application_creator_bootstrap_runtime_dependencies.php',
                'core/di/application_creator_cache_factory_dependencies.php',
                'core/di/application_creator_common_dependencies.php',
                'core/di/application_creator_config_dependencies.php',
                'core/di/application_creator_config_cache_dependencies.php',
                'core/di/application_core_project_application_context_dependencies.php',
                'core/di/application_core_project_error_dependencies.php',
                'core/di/application_core_project_error_context_dependencies.php',
                'core/di/application_core_project_error_file_storage_dependencies.php',
                'core/di/application_core_project_error_factory_service_dependencies.php',
                'core/di/application_core_project_error_log_writer_storage_dependencies.php',
                'core/di/application_core_project_error_service_dependencies.php',
                'core/di/application_core_project_error_storage_dependencies.php',
                'core/di/application_core_project_dependencies.php',
                'core/di/application_core_project_header_writer_response_loader_dependencies.php',
                'core/di/application_core_project_locale_context_dependencies.php',
                'core/di/application_core_project_meta_file_storage_dependencies.php',
                'core/di/application_core_project_php_array_file_loader_response_loader_dependencies.php',
                'core/di/application_core_project_reflection_class_factory_meta_dependencies.php',
                'core/di/application_core_project_reflection_meta_dependencies.php',
                'core/di/application_core_project_route_storage_dependencies.php',
                'core/di/application_core_project_response_loader_dependencies.php',
                'core/di/application_core_project_storage_dependencies.php',
                'core/di/application_core_project_tab_context_dependencies.php',
                'core/di/application_core_project_tab_factory_service_context_dependencies.php',
                'core/di/application_core_project_tab_instance_service_context_dependencies.php',
                'core/di/application_core_project_tab_service_context_dependencies.php',
                'core/di/application_core_project_tab_dependencies.php',
                'core/di/application_core_request_array_adducer_transform_helper_dependencies.php',
                'core/di/application_core_request_array_read_class_helper_dependencies.php',
                'core/di/application_core_request_array_transform_helper_dependencies.php',
                'core/di/application_core_request_array_value_reader_helper_dependencies.php',
                'core/di/application_core_request_class_name_resolver_helper_dependencies.php',
                'core/di/application_core_request_cookie_transport_factory_dependencies.php',
                'core/di/application_core_request_dependencies.php',
                'core/di/application_core_request_factory_dependencies.php',
                'core/di/application_core_request_helper_dependencies.php',
                'core/di/application_core_request_input_factory_dependencies.php',
                'core/di/application_core_request_json_transport_factory_dependencies.php',
                'core/di/application_core_request_matcher_factory_runtime_dependencies.php',
                'core/di/application_core_request_recursive_merger_transform_helper_dependencies.php',
                'core/di/application_core_request_request_factory_runtime_dependencies.php',
                'core/di/application_core_request_runtime_factory_dependencies.php',
                'core/di/application_core_request_transport_factory_dependencies.php',
                'core/di/application_core_service_dependencies.php',
                'core/di/application_core_service_creator.php',
                'core/di/application_core_user_current_identity_dependencies.php',
                'core/di/application_core_user_current_user_space_factory_session_space_dependencies.php',
                'core/di/application_core_user_data_factory_dependencies.php',
                'core/di/application_core_user_date_data_factory_dependencies.php',
                'core/di/application_core_user_entity_data_factory_dependencies.php',
                'core/di/application_core_user_identity_dependencies.php',
                'core/di/application_core_user_session_factory_session_space_dependencies.php',
                'core/di/application_core_user_session_space_dependencies.php',
                'core/di/application_core_user_session_dependencies.php',
                'core/di/application_infrastructure_config_cache_cache_factory_dependencies.php',
                'core/di/application_infrastructure_config_cache_bootstrap_runtime_support_dependencies.php',
                'core/di/application_infrastructure_config_cache_cache_source_file_metadata_storage_dependencies.php',
                'core/di/application_infrastructure_config_cache_dependencies.php',
                'core/di/application_infrastructure_config_cache_class_helper_dependencies.php',
                'core/di/application_infrastructure_config_cache_config_cache_factory_dependencies.php',
                'core/di/application_infrastructure_config_cache_config_factory_dependencies.php',
                'core/di/application_infrastructure_config_cache_config_instance_dependencies.php',
                'core/di/application_infrastructure_config_cache_config_source_file_storage_dependencies.php',
                'core/di/application_infrastructure_config_cache_core_fatal_exception_factory_dependencies.php',
                'core/di/application_infrastructure_config_cache_error_factory_runtime_support_dependencies.php',
                'core/di/application_infrastructure_config_cache_exception_dependencies.php',
                'core/di/application_infrastructure_config_cache_error500_exception_factory_dependencies.php',
                'core/di/application_infrastructure_config_cache_exception_factory_dependencies.php',
                'core/di/application_infrastructure_config_cache_factory_dependencies.php',
                'core/di/application_infrastructure_config_cache_header_writer_dependencies.php',
                'core/di/application_infrastructure_config_cache_loader_serializer_dependencies.php',
                'core/di/application_infrastructure_config_cache_php_array_file_loader_dependencies.php',
                'core/di/application_infrastructure_config_cache_request_input_dependencies.php',
                'core/di/application_infrastructure_config_cache_request_header_dependencies.php',
                'core/di/application_infrastructure_config_cache_runtime_support_dependencies.php',
                'core/di/application_infrastructure_config_cache_serializer_operations_dependencies.php',
                'core/di/application_infrastructure_config_cache_storage_dependencies.php',
                'core/di/application_infrastructure_config_cache_support_dependencies.php',
                'core/di/application_infrastructure_config_cache_type_cache_factory_dependencies.php',
                'core/di/application_infrastructure_config_cache_typed_config_factory_dependencies.php',
                'core/di/application_infrastructure_runtime_bootstrap_runtime_error_dependencies.php',
                'core/di/application_infrastructure_runtime_cache_factory_dependencies.php',
                'core/di/application_infrastructure_runtime_config_cache_dependencies.php',
                'core/di/application_infrastructure_runtime_config_dependencies.php',
                'core/di/application_infrastructure_runtime_dependencies.php',
                'core/di/application_infrastructure_runtime_error_dependencies.php',
                'core/di/application_infrastructure_runtime_error_factory_dependencies.php',
                'core/di/application_infrastructure_bootstrap_runtime_registrar_dependencies.php',
                'core/di/application_infrastructure_cache_factory_registrar_dependencies.php',
                'core/di/application_infrastructure_cache_memcache_state_registrar_dependencies.php',
                'core/di/application_infrastructure_cache_state_registrar_dependencies.php',
                'core/di/application_infrastructure_config_registrar_dependencies.php',
                'core/di/application_infrastructure_config_state_registrar_dependencies.php',
                'core/di/application_infrastructure_file_system_state_registrar_dependencies.php',
                'core/di/application_infrastructure_json_state_registrar_dependencies.php',
                'core/di/application_infrastructure_service_dependencies.php',
                'core/di/application_infrastructure_service_creator.php',
                'core/di/application_infrastructure_service_registrar_dependencies.php',
                'core/di/application_infrastructure_storage_dependencies.php',
                'core/di/application_navigation_service_creator.php',
                'core/di/application_navigation_tab_alias_asset_dependencies.php',
                'core/di/application_navigation_tab_application_factory_application_debug_dependencies.php',
                'core/di/application_navigation_tab_block_file_storage_dependencies.php',
                'core/di/application_navigation_tab_block_meta_file_storage_dependencies.php',
                'core/di/application_navigation_tab_config_factory_config_header_dependencies.php',
                'core/di/application_navigation_tab_cookie_payload_dependencies.php',
                'core/di/application_navigation_tab_data_cookie_payload_dependencies.php',
                'core/di/application_navigation_tab_data_loader_payload_dependencies.php',
                'core/di/application_navigation_tab_data_model_factory_dependencies.php',
                'core/di/application_navigation_tab_date_factory_user_time_model_factory_dependencies.php',
                'core/di/application_navigation_tab_context_dependencies.php',
                'core/di/application_navigation_tab_core_dependencies.php',
                'core/di/application_navigation_tab_debug_factory_application_debug_dependencies.php',
                'core/di/application_navigation_tab_dependencies.php',
                'core/di/application_navigation_tab_entity_model_factory_dependencies.php',
                'core/di/application_navigation_tab_error_factory_error_reflector_dependencies.php',
                'core/di/application_navigation_tab_error_log_writer_asset_dependencies.php',
                'core/di/application_navigation_tab_file_storage_dependencies.php',
                'core/di/application_navigation_tab_header_factory_config_header_dependencies.php',
                'core/di/application_navigation_tab_image_metadata_reader_asset_dependencies.php',
                'core/di/application_navigation_tab_image_modify_model_factory_dependencies.php',
                'core/di/application_navigation_tab_input_context_dependencies.php',
                'core/di/application_navigation_tab_json_payload_dependencies.php',
                'core/di/application_navigation_tab_locale_context_dependencies.php',
                'core/di/application_navigation_tab_locale_session_context_dependencies.php',
                'core/di/application_navigation_tab_matcher_routing_context_dependencies.php',
                'core/di/application_navigation_tab_media_error_asset_dependencies.php',
                'core/di/application_navigation_tab_media_model_factory_dependencies.php',
                'core/di/application_navigation_tab_meta_file_storage_dependencies.php',
                'core/di/application_navigation_tab_model_factory_dependencies.php',
                'core/di/application_navigation_tab_obfuscator_model_factory_dependencies.php',
                'core/di/application_navigation_tab_pager_model_factory_dependencies.php',
                'core/di/application_navigation_tab_project_tool_storage_dependencies.php',
                'core/di/application_navigation_tab_reflector_factory_error_reflector_dependencies.php',
                'core/di/application_navigation_tab_request_routing_context_dependencies.php',
                'core/di/application_navigation_tab_routing_context_dependencies.php',
                'core/di/application_navigation_tab_root_html_file_storage_dependencies.php',
                'core/di/application_navigation_tab_role_factory_role_transfer_dependencies.php',
                'core/di/application_navigation_tab_session_factory_context_dependencies.php',
                'core/di/application_navigation_tab_service_factory_application_debug_dependencies.php',
                'core/di/application_navigation_tab_service_factory_application_dependencies.php',
                'core/di/application_navigation_tab_service_factory_config_header_dependencies.php',
                'core/di/application_navigation_tab_service_factory_dependencies.php',
                'core/di/application_navigation_tab_service_factory_error_reflector_dependencies.php',
                'core/di/application_navigation_tab_service_factory_payload_dependencies.php',
                'core/di/application_navigation_tab_service_factory_role_transfer_dependencies.php',
                'core/di/application_navigation_tab_service_factory_runtime_dependencies.php',
                'core/di/application_navigation_tab_storage_dependencies.php',
                'core/di/application_navigation_tab_array_adducer_transform_dependencies.php',
                'core/di/application_navigation_tab_array_like_checker_read_check_dependencies.php',
                'core/di/application_navigation_tab_array_value_reader_read_check_dependencies.php',
                'core/di/application_navigation_tab_block_exception_factory_exception_meta_dependencies.php',
                'core/di/application_navigation_tab_block_factory_instance_block_factory_dependencies.php',
                'core/di/application_navigation_tab_meta_row_factory_exception_meta_dependencies.php',
                'core/di/application_navigation_tab_recursive_merger_transform_dependencies.php',
                'core/di/application_navigation_tab_support_array_read_check_dependencies.php',
                'core/di/application_navigation_tab_support_array_helper_dependencies.php',
                'core/di/application_navigation_tab_support_array_transform_dependencies.php',
                'core/di/application_navigation_tab_support_asset_dependencies.php',
                'core/di/application_navigation_tab_support_block_exception_meta_dependencies.php',
                'core/di/application_navigation_tab_support_block_factory_dependencies.php',
                'core/di/application_navigation_tab_support_block_dependencies.php',
                'core/di/application_navigation_tab_class_name_resolver_class_helper_dependencies.php',
                'core/di/application_navigation_tab_short_class_name_resolver_class_helper_dependencies.php',
                'core/di/application_navigation_tab_support_class_helper_dependencies.php',
                'core/di/application_navigation_tab_support_dependencies.php',
                'core/di/application_navigation_tab_support_helper_dependencies.php',
                'core/di/application_navigation_tab_support_loader_dependencies.php',
                'core/di/application_navigation_tab_tab_state_block_factory_dependencies.php',
                'core/di/application_navigation_tab_transfer_factory_role_transfer_dependencies.php',
                'core/di/application_navigation_tab_upload_limit_storage_dependencies.php',
                'core/di/application_navigation_tab_user_factory_user_time_model_factory_dependencies.php',
                'core/di/application_navigation_tab_user_time_model_factory_dependencies.php',
                'core/di/application_pager_context_dependencies.php',
                'core/di/application_pager_bootstrap_runtime_dependencies.php',
                'core/di/application_pager_cache_factory_config_cache_runtime_dependencies.php',
                'core/di/application_pager_config_cache_runtime_dependencies.php',
                'core/di/application_pager_config_config_cache_runtime_dependencies.php',
                'core/di/application_pager_entity_context_dependencies.php',
                'core/di/application_pager_exception_dependencies.php',
                'core/di/application_pager_request_context_dependencies.php',
                'core/di/application_pager_runtime_dependencies.php',
                'core/di/application_pager_service_dependencies.php',
                'core/di/application_pager_service_creator.php',
                'core/di/application_pager_service_registrar_dependencies.php',
                'core/di/application_pager_tab_context_dependencies.php',
                'core/di/application_session_application_instance_application_context_dependencies.php',
                'core/di/application_session_application_context_dependencies.php',
                'core/di/application_session_array_runtime_dependencies.php',
                'core/di/application_session_bootstrap_bootstrap_runtime_dependencies.php',
                'core/di/application_session_bootstrap_runtime_dependencies.php',
                'core/di/application_session_cache_factory_dependencies.php',
                'core/di/application_session_config_application_context_dependencies.php',
                'core/di/application_session_context_dependencies.php',
                'core/di/application_session_cookie_factory_state_factory_dependencies.php',
                'core/di/application_session_date_factory_support_factory_dependencies.php',
                'core/di/application_session_error_factory_support_factory_dependencies.php',
                'core/di/application_session_factory_dependencies.php',
                'core/di/application_session_header_context_dependencies.php',
                'core/di/application_session_native_session_native_runtime_dependencies.php',
                'core/di/application_session_native_runtime_dependencies.php',
                'core/di/application_session_pear_http_session_loader_native_runtime_dependencies.php',
                'core/di/application_session_php_runtime_settings_bootstrap_runtime_dependencies.php',
                'core/di/application_session_request_input_request_context_dependencies.php',
                'core/di/application_session_request_instance_request_context_dependencies.php',
                'core/di/application_session_request_context_dependencies.php',
                'core/di/application_session_runtime_dependencies.php',
                'core/di/application_session_service_dependencies.php',
                'core/di/application_session_service_creator.php',
                'core/di/application_session_service_registrar_dependencies.php',
                'core/di/application_session_session_factory_state_factory_dependencies.php',
                'core/di/application_session_state_factory_dependencies.php',
                'core/di/application_session_support_factory_dependencies.php',
                'core/di/application_user_application_instance_application_request_context_dependencies.php',
                'core/di/application_user_application_factory_application_request_input_factory_dependencies.php',
                'core/di/application_user_application_request_input_factory_dependencies.php',
                'core/di/application_user_application_factory_dependencies.php',
                'core/di/application_user_application_request_context_dependencies.php',
                'core/di/application_user_array_runtime_dependencies.php',
                'core/di/application_user_bootstrap_runtime_bootstrap_cache_runtime_dependencies.php',
                'core/di/application_user_bootstrap_cache_runtime_dependencies.php',
                'core/di/application_user_bootstrap_runtime_registrar_dependencies.php',
                'core/di/application_user_cache_factory_registrar_dependencies.php',
                'core/di/application_user_cache_factory_bootstrap_cache_runtime_dependencies.php',
                'core/di/application_user_config_registrar_dependencies.php',
                'core/di/application_user_config_serialization_config_context_dependencies.php',
                'core/di/application_user_config_factory_dependencies.php',
                'core/di/application_user_context_dependencies.php',
                'core/di/application_user_current_user_factory_identity_factory_dependencies.php',
                'core/di/application_user_error500_exception_factory_exception_session_context_dependencies.php',
                'core/di/application_user_exception_session_context_dependencies.php',
                'core/di/application_user_factory_dependencies.php',
                'core/di/application_user_identity_factory_dependencies.php',
                'core/di/application_user_request_input_factory_application_request_input_factory_dependencies.php',
                'core/di/application_user_request_application_request_context_dependencies.php',
                'core/di/application_user_runtime_dependencies.php',
                'core/di/application_user_serializer_operations_serialization_config_context_dependencies.php',
                'core/di/application_user_serialization_config_context_dependencies.php',
                'core/di/application_user_service_dependencies.php',
                'core/di/application_user_service_creator.php',
                'core/di/application_user_service_registrar_dependencies.php',
                'core/di/application_user_session_exception_session_context_dependencies.php',
                'core/di/application_user_session_factory_identity_factory_dependencies.php',
                'core/di/application_user_state_registrar_dependencies.php',
                'core/di/application_user_support_factory_dependencies.php',
                'core/di/application_user_error_factory_support_factory_dependencies.php',
                'core/di/application_user_entity_factory_support_factory_dependencies.php',
                'core/di/application_utility_cache_factory_config_cache_core_dependencies.php',
                'core/di/application_utility_config_cache_core_dependencies.php',
                'core/di/application_utility_config_config_cache_core_dependencies.php',
                'core/di/application_utility_core_dependencies.php',
                'core/di/application_utility_class_storage_dependencies.php',
                'core/di/application_utility_file_storage_dependencies.php',
                'core/di/application_utility_php_array_file_loader_file_storage_dependencies.php',
                'core/di/application_utility_soap_wsdl_file_storage_file_storage_dependencies.php',
                'core/di/application_utility_date_state_registrar_dependencies.php',
                'core/di/application_utility_helper_error_core_dependencies.php',
                'core/di/application_utility_array_value_reader_helper_error_core_dependencies.php',
                'core/di/application_utility_error_factory_helper_error_core_dependencies.php',
                'core/di/application_utility_image_canvas_output_dependencies.php',
                'core/di/application_utility_image_canvas_operations_canvas_output_dependencies.php',
                'core/di/application_utility_image_output_writer_canvas_output_dependencies.php',
                'core/di/application_utility_image_dependencies.php',
                'core/di/application_utility_image_modify_state_registrar_dependencies.php',
                'core/di/application_utility_image_metadata_resource_dependencies.php',
                'core/di/application_utility_image_metadata_reader_metadata_resource_dependencies.php',
                'core/di/application_utility_image_resource_factory_metadata_resource_dependencies.php',
                'core/di/application_utility_image_storage_dependencies.php',
                'core/di/application_utility_image_source_file_storage_image_storage_dependencies.php',
                'core/di/application_utility_obfuscator_file_storage_image_storage_dependencies.php',
                'core/di/application_utility_obfuscator_state_registrar_dependencies.php',
                'core/di/application_utility_bootstrap_runtime_runtime_core_dependencies.php',
                'core/di/application_utility_runtime_core_dependencies.php',
                'core/di/application_utility_php_runtime_settings_runtime_core_dependencies.php',
                'core/di/application_utility_service_dependencies.php',
                'core/di/application_utility_storage_dependencies.php',
                'core/di/application_utility_service_registrar_dependencies.php',
                'core/di/application_utility_service_creator.php',
                'core/di/application_support_application_registrar_dependencies.php',
                'core/di/application_support_array_adducer_registrar_dependencies.php',
                'core/di/application_support_array_value_reader_registrar_dependencies.php',
                'core/di/application_support_bootstrap_context_registrar_dependencies.php',
                'core/di/application_support_bootstrap_runtime_registrar_dependencies.php',
                'core/di/application_support_cache_factory_registrar_dependencies.php',
                'core/di/application_support_class_name_resolver_registrar_dependencies.php',
                'core/di/application_support_config_registrar_dependencies.php',
                'core/di/application_support_delayed_meta_factory_registrar_dependencies.php',
                'core/di/application_support_entity_registrar_dependencies.php',
                'core/di/application_support_error_demonstrator_file_storage_registrar_dependencies.php',
                'core/di/application_support_error_demonstrator_loader_registrar_dependencies.php',
                'core/di/application_support_error_demonstrator_registrar_dependencies.php',
                'core/di/application_support_error_log_writer_registrar_dependencies.php',
                'core/di/application_support_error_registrar_dependencies.php',
                'core/di/application_support_exception_factory_registrar_dependencies.php',
                'core/di/application_support_header_writer_registrar_dependencies.php',
                'core/di/application_support_image_metadata_reader_registrar_dependencies.php',
                'core/di/application_support_image_modify_factory_registrar_dependencies.php',
                'core/di/application_support_meta_maker_state_registrar_dependencies.php',
                'core/di/application_support_meta_view_registrar_dependencies.php',
                'core/di/application_support_php_runtime_settings_registrar_dependencies.php',
                'core/di/application_support_plain_exception_factory_registrar_dependencies.php',
                'core/di/application_support_plain_file_storage_registrar_dependencies.php',
                'core/di/application_support_recursive_merger_registrar_dependencies.php',
                'core/di/application_support_reflection_class_factory_registrar_dependencies.php',
                'core/di/application_support_request_registrar_dependencies.php',
                'core/di/application_support_service_listener_state_registrar_dependencies.php',
                'core/di/application_support_service_dependencies_registrar_dependencies.php',
                'core/di/application_support_service_registrar_dependencies.php',
                'core/di/application_support_service_single_state_registrar_dependencies.php',
                'core/di/application_support_spec_file_image_row_state_registrar_dependencies.php',
                'core/di/application_support_tab_resolver_registrar_dependencies.php',
                'core/di/application_support_transfer_exception_factory_registrar_dependencies.php',
                'core/di/application_support_translation_registrar_dependencies.php',
                'core/di/application_support_view_keeper_factory_registrar_dependencies.php',
                'core/di/application_support_view_loader_json_keeper_factory_registrar_dependencies.php',
                'core/di/application_support_view_loader_state_registrar_dependencies.php',
                'core/di/application_support_view_loader_text_keeper_factory_registrar_dependencies.php',
                'core/di/configured_class_instantiator.php',
                'core/block/base_dependency_application_service_runtime_factory_group.php',
                'core/block/base_dependency_application_data_factory_group.php',
                'core/block/base_dependency_application_factory_group.php',
                'core/block/base_dependency_application_runtime_factory_group.php',
                'core/block/base_dependency_application_support_factory_group.php',
                'core/block/base_dependency_array_adducer_helper_group.php',
                'core/block/base_dependency_array_helper_group.php',
                'core/block/base_dependency_array_like_checker_helper_group.php',
                'core/block/base_dependency_array_read_helper_group.php',
                'core/block/base_dependency_array_transform_helper_group.php',
                'core/block/base_dependency_array_value_reader_helper_group.php',
                'core/block/base_dependency_block_exception_factory_group.php',
                'core/block/base_dependency_block_factory_group.php',
                'core/block/base_dependency_block_file_storage_block_group.php',
                'core/block/base_dependency_block_file_storage_group.php',
                'core/block/base_dependency_block_file_storage_meta_group.php',
                'core/block/base_dependency_block_factory_context_group.php',
                'core/block/base_dependency_class_helper_group.php',
                'core/block/base_dependency_config_application_runtime_factory_group.php',
                'core/block/base_dependency_context_group.php',
                'core/block/base_dependency_data_core_factory_group.php',
                'core/block/base_dependency_data_factory_group.php',
                'core/block/base_dependency_data_loader_service_factory_group.php',
                'core/block/base_dependency_data_loader_factory_group.php',
                'core/block/base_dependency_date_application_support_factory_group.php',
                'core/block/base_dependency_database_application_data_factory_group.php',
                'core/block/base_dependency_entity_data_core_factory_group.php',
                'core/block/base_dependency_error_log_media_error_helper_group.php',
                'core/block/base_dependency_error_application_support_factory_group.php',
                'core/block/base_dependency_factory_group.php',
                'core/block/base_dependency_helper_group.php',
                'core/block/base_dependency_image_metadata_media_error_helper_group.php',
                'core/block/base_dependency_image_modify_media_core_factory_group.php',
                'core/block/base_dependency_json_data_core_factory_group.php',
                'core/block/base_dependency_locale_factory_context_group.php',
                'core/block/base_dependency_matcher_factory_context_group.php',
                'core/block/base_dependency_media_core_factory_group.php',
                'core/block/base_dependency_media_error_helper_group.php',
                'core/block/base_dependency_media_transfer_factory_group.php',
                'core/block/base_dependency_meta_maker_factory_group.php',
                'core/block/base_dependency_meta_maker_group.php',
                'core/block/base_dependency_meta_maker_state_group.php',
                'core/block/base_dependency_meta_loader_group.php',
                'core/block/base_dependency_meta_row_factory_group.php',
                'core/block/base_dependency_meta_row_loader_group.php',
                'core/block/base_dependency_media_factory_group.php',
                'core/block/base_dependency_navigation_context_group.php',
                'core/block/base_dependency_obfuscator_media_core_factory_group.php',
                'core/block/base_dependency_pager_data_loader_factory_group.php',
                'core/block/base_dependency_php_array_file_loader_group.php',
                'core/block/base_dependency_project_file_storage_group.php',
                'core/block/base_dependency_project_tool_file_storage_group.php',
                'core/block/base_dependency_reflector_context_group.php',
                'core/block/base_dependency_request_factory_context_group.php',
                'core/block/base_dependency_request_input_context_group.php',
                'core/block/base_dependency_request_context_group.php',
                'core/block/base_dependency_request_role_context_group.php',
                'core/block/base_dependency_recursive_merger_helper_group.php',
                'core/block/base_dependency_role_factory_context_group.php',
                'core/block/base_dependency_route_locale_context_group.php',
                'core/block/base_dependency_root_html_file_storage_group.php',
                'core/block/base_dependency_resolver.php',
                'core/block/base_dependency_runtime_context_group.php',
                'core/block/base_dependency_session_context_group.php',
                'core/block/base_dependency_bootstrap_runtime_context_group.php',
                'core/block/base_dependency_tab_service_runtime_context_group.php',
                'core/block/base_dependency_storage_group.php',
                'core/block/base_dependency_tab_runtime_context_group.php',
                'core/block/base_dependency_upload_limit_storage_group.php',
                'core/block/base_dependency_user_application_data_factory_group.php',
                'core/block/base_dependency_view_factory_loader_group.php',
                'core/block/base_dependency_view_loader_group.php',
                'core/block/base_dependency_view_parser_exception_factory_loader_group.php',
                'core/block/base_dependency_view_router_factory_loader_group.php',
                'core/block/base_dependency_view_state_loader_group.php',
                'core/block/base_dependency_view_meta_group.php',
                'core/factory/application_runtime_class_instantiator_provider.php',
                'core/factory/application_runtime_configured_service_provider.php',
                'core/factory/application_runtime_factory_defaults_provider_factory.php',
                'core/factory/bootstrap_object_class_instantiator_provider.php',
                'core/factory/bootstrap_object_configured_service_provider.php',
                'core/factory/bootstrap_object_defaults_provider_factory.php',
                'core/factory/cache_engine_factory.php',
                'core/factory/configured_service_factory.php',
                'core/factory/adapter/reflection_class_factory.php',
            ],
            'tooling_support' => [
                'tools/composer_autoload.php',
            ],
            'migration_debt' => [],
        ],
    ];

    $map['locations'] = $root !== null && $productionPhpFiles !== null
        ? php_fan_ai_dynamic_boundary_locations($root, $productionPhpFiles)
        : [];
    $compositionQueues = php_fan_ai_dynamic_boundary_composition_queues($map);
    $namedDebtQueues = php_fan_ai_dynamic_boundary_named_migration_debt_queues($map);
    $map['composition_leaf_policy'] = [
        'terminal_count' => 1,
        'actionable_min_count' => 2,
        'terminal_kind' => \fan\core\ai\dynamic_boundary::TERMINAL_COMPOSITION_LEAF,
        'actionable_kind' => \fan\core\ai\dynamic_boundary::ACTIONABLE_COMPOSITION_ROOT,
        'description' => 'Composition roots with one dynamic boundary are terminal leaves; actionable split candidates start at count 2.',
    ];
    $map['terminal_composition_leaves'] = $compositionQueues['terminal_composition_leaves'];
    $map['actionable_composition_roots'] = $compositionQueues['actionable_composition_roots'];
    $map['named_migration_debt_policy'] = [
        'boundary_kind' => \fan\core\ai\dynamic_boundary::NAMED_DEFAULT_CLOSURE,
        'category' => 'migration_debt',
        'actionable_kind' => \fan\core\ai\dynamic_boundary::ACTIONABLE_NAMED_MIGRATION_DEBT,
        'intentional_kind' => \fan\core\ai\dynamic_boundary::INTENTIONAL_NAMED_COMPATIBILITY,
        'description' => 'Named/default compatibility boundaries are split into actionable migration debt and intentional compatibility leaves.',
    ];
    $map['actionable_named_migration_debt'] = $namedDebtQueues['actionable_named_migration_debt'];
    $map['intentional_named_compatibility_boundaries'] = $namedDebtQueues['intentional_named_compatibility_boundaries'];

    return $map;
}

function php_fan_ai_dynamic_boundary_category_for_file(string $relativeFile): ?string
{
    return php_fan_ai_dynamic_boundary_category_details_for_file($relativeFile)['category'] ?? null;
}

function php_fan_ai_dynamic_boundary_category_details_for_file(string $relativeFile): ?array
{
    if (str_ends_with($relativeFile, '.meta.php') || str_ends_with($relativeFile, '.tpl')) {
        $map = php_fan_ai_dynamic_boundaries_map();

        return [
            'category' => 'extension_api',
            'matched_entry' => str_ends_with($relativeFile, '.meta.php') ? '*.meta.php' : '*.tpl',
            'reason' => $map['category_reasons']['extension_api'],
        ];
    }

    $map = php_fan_ai_dynamic_boundaries_map();
    foreach ($map['categories'] as $category => $files) {
        if (in_array($relativeFile, $files, true)) {
            return [
                'category' => $category,
                'matched_entry' => $relativeFile,
                'reason' => $map['category_reasons'][$category] ?? '',
            ];
        }
    }

    return null;
}

function php_fan_ai_dynamic_boundary_kind_for_file(string $relativeFile): string
{
    return \fan\core\ai\dynamic_boundary::kindForFile($relativeFile);
}

function php_fan_ai_dynamic_boundary_kind_for_location(string $relativeFile, string $pattern, string $lineText): string
{
    return \fan\core\ai\dynamic_boundary::kindForLocation($relativeFile, $pattern, $lineText);
}

function php_fan_ai_dynamic_boundary_kind_for_locations(string $relativeFile, array $locations): string
{
    return \fan\core\ai\dynamic_boundary::kindForLocations($relativeFile, $locations);
}

function php_fan_ai_dynamic_boundary_locations(string $root, array $relativeFiles): array
{
    $locations = [];
    foreach ($relativeFiles as $relativeFile) {
        if (php_fan_ai_dynamic_boundary_category_details_for_file($relativeFile) === null) {
            continue;
        }

        $absoluteFile = rtrim($root, DIRECTORY_SEPARATOR) . '/' . $relativeFile;
        if (!is_file($absoluteFile)) {
            continue;
        }

        $source = (string)file_get_contents($absoluteFile);
        $fileLocations = php_fan_ai_dynamic_boundary_locations_from_source($relativeFile, $source);
        if ($fileLocations !== []) {
            $locations[$relativeFile] = $fileLocations;
        }
    }

    ksort($locations);

    return $locations;
}

function php_fan_ai_dynamic_boundary_locations_from_source(string $relativeFile, string $source): array
{
    $code = php_fan_ai_code_without_comments_and_strings($source);
    $locations = [];

    foreach (php_fan_ai_dynamic_boundary_location_patterns() as $patternName => $pattern) {
        if (preg_match_all($pattern, $code, $matches, PREG_OFFSET_CAPTURE) === 0) {
            continue;
        }

        foreach ($matches[0] as [$value, $offset]) {
            $line = substr_count($source, "\n", 0, $offset) + 1;
            $lineText = php_fan_ai_source_line($source, $line);
            $locations[] = [
                'file' => $relativeFile,
                'line' => $line,
                'pattern' => $patternName,
                'value' => trim((string)$value),
                'boundary_kind' => php_fan_ai_dynamic_boundary_kind_for_location($relativeFile, $patternName, $lineText),
            ];
        }
    }

    usort(
        $locations,
        static fn(array $left, array $right): int => [
            (int)$left['line'],
            (string)$left['pattern'],
            (string)$left['value'],
        ] <=> [
            (int)$right['line'],
            (string)$right['pattern'],
            (string)$right['value'],
        ]
    );

    return $locations;
}

function php_fan_ai_source_line(string $source, int $line): string
{
    $lines = explode("\n", $source);

    return $lines[$line - 1] ?? '';
}

function php_fan_ai_dynamic_boundary_location_patterns(): array
{
    return [
        'class_exists' => '/\bclass_exists\s*\(/',
        'ReflectionClass' => '/\bnew\s+\\\\?ReflectionClass\s*\(/',
        'configured_service_factory' => '/\bconfigured_service_factory\b/',
        'configured_class_instantiator' => '/\bconfigured_class_instantiator\b/',
        'container_get' => '/(?:\$container|\$this->container|\$this->container\(\)|\$this->context\(\)->container\(\))\s*->\s*get\s*\(/',
        'dynamic_new' => '/new\s+\$[A-Za-z_][A-Za-z0-9_]*\s*\(/',
        'file_include' => '/\b(?:include|include_once|require|require_once)\b/',
        'callback_dispatch' => '/\bcall_user_func(?:_array)?\s*\(/',
        'eval' => '/\beval\s*\(/',
    ];
}

function php_fan_ai_code_without_comments_and_strings(string $source): string
{
    $code = '';
    foreach (PhpToken::tokenize($source) as $token) {
        if (in_array($token->id, [T_COMMENT, T_DOC_COMMENT, T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE], true)) {
            $code .= str_repeat(' ', strlen($token->text));
            continue;
        }

        $code .= $token->text;
    }

    return $code;
}

function php_fan_ai_meta_file_data(string $root, string $relativeFile): array
{
    $absoluteFile = $root . '/' . $relativeFile;
    if (!is_file($absoluteFile)) {
        return [];
    }

    try {
        $data = (static fn(string $file): mixed => include $file)($absoluteFile);
    } catch (\Throwable) {
        return [];
    }

    return is_array($data) ? $data : [];
}

function php_fan_ai_increment_usage(array &$usage, array $keys): void
{
    foreach ($keys as $key) {
        if (!is_string($key) && !is_int($key)) {
            continue;
        }
        $key = (string)$key;
        $usage[$key] = ($usage[$key] ?? 0) + 1;
    }
}

function php_fan_ai_paired_template(string $metaFile, array $templateFiles): ?string
{
    $candidate = substr($metaFile, 0, -strlen('.meta.php')) . '.tpl';

    return in_array($candidate, $templateFiles, true) ? $candidate : null;
}

function php_fan_ai_paired_meta_file(string $templateFile, array $metaFiles): ?string
{
    $candidate = substr($templateFile, 0, -strlen('.tpl')) . '.meta.php';

    return in_array($candidate, $metaFiles, true) ? $candidate : null;
}

function php_fan_ai_template_placeholders(string $root, string $relativeFile): array
{
    $source = file_get_contents($root . '/' . $relativeFile);
    if (!is_string($source)) {
        return [];
    }

    $placeholders = [];
    if (preg_match_all('/\{[A-Za-z_][A-Za-z0-9_]*\}/', $source, $matches) > 0) {
        $placeholders = array_merge($placeholders, $matches[0]);
    }

    if (preg_match_all('/\{\{([^}]*)\}\}|\{%([^%]*)%\}/', $source, $matches, PREG_SET_ORDER) > 0) {
        $ignored = [
            'and' => true,
            'default' => true,
            'else' => true,
            'elseif' => true,
            'endfor' => true,
            'endif' => true,
            'false' => true,
            'for' => true,
            'if' => true,
            'in' => true,
            'last' => true,
            'loop' => true,
            'not' => true,
            'null' => true,
            'or' => true,
            'true' => true,
        ];
        foreach ($matches as $match) {
            $expression = $match[1] !== '' ? $match[1] : $match[2];
            if (preg_match_all('/\b[A-Za-z_][A-Za-z0-9_]*\b/', $expression, $tokens) === 0) {
                continue;
            }
            foreach ($tokens[0] as $token) {
                if (!isset($ignored[$token])) {
                    $placeholders[] = $token;
                }
            }
        }
    }

    $placeholders = array_values(array_unique($placeholders));
    sort($placeholders);

    return $placeholders;
}

function php_fan_ai_composer_autoload(string $root): array
{
    $composerFile = $root . '/composer.json';
    if (!is_file($composerFile)) {
        return [];
    }

    $composer = json_decode((string)file_get_contents($composerFile), true);
    if (!is_array($composer)) {
        return [];
    }

    return [
        'psr-4' => $composer['autoload']['psr-4'] ?? [],
        'files' => $composer['autoload']['files'] ?? [],
        'dev_psr-4' => $composer['autoload-dev']['psr-4'] ?? [],
    ];
}

function php_fan_ai_service_map(string $root, array $relativePhpFiles): array
{
    return (new php_fan_ai_service_map_builder($root, $relativePhpFiles))->build();
}

function php_fan_ai_service_descriptor(string $root, string $serviceId): ?array
{
    $map = php_fan_ai_build_map($root);

    return $map['services']['descriptors'][$serviceId] ?? null;
}

function php_fan_ai_source_inventory_map(string $root, array $productionPhpFiles): array
{
    $queues = [];
    foreach (php_fan_ai_source_inventory_queue_definitions() as $queueId => $definition) {
        $locations = php_fan_ai_source_inventory_queue_locations($root, $productionPhpFiles, $definition);
        $queues[$queueId] = php_fan_ai_source_inventory_queue_summary($queueId, $definition, $locations);
    }

    $next = array_values(array_filter(
        $queues,
        static fn(array $queue): bool => ($queue['classification'] ?? null) === 'actionable'
            && (int)($queue['count'] ?? 0) > 0
    ));
    usort(
        $next,
        static fn(array $left, array $right): int => [
            (int)$left['priority'],
            -(int)$left['count'],
            (string)$left['id'],
        ] <=> [
            (int)$right['priority'],
            -(int)$right['count'],
            (string)$right['id'],
        ]
    );

    return [
        'policy' => [
            'description' => 'Compact source-guard queues for post-dynamic refactoring. The next queue contains only actionable non-zero guard queues.',
            'classification_kinds' => [
                'actionable',
                'clean',
                'bootstrap_boundary',
                'composition_boundary',
                'intentional_compatibility',
                'tooling_support',
                'entrypoint_boundary',
            ],
            'next_queue' => 'source_inventory.next',
        ],
        'queues' => $queues,
        'next' => $next,
    ];
}

function php_fan_ai_source_inventory_queue_definitions(): array
{
    return [
        'service_locator_calls' => [
            'guard_kind' => 'service_locator_call',
            'classification' => 'actionable',
            'priority' => 10,
            'reason' => 'Production service-locator getService() calls should stay removed from runtime code.',
            'patterns' => [
                'service_locator_call' => '/->\s*getService\s*\(/',
            ],
        ],
        'unmanaged_container_lookups' => [
            'guard_kind' => 'container_lookup',
            'classification' => 'actionable',
            'priority' => 20,
            'reason' => 'Container lookups outside DI composition roots should be migrated behind explicit collaborators.',
            'patterns' => [
                'container_get' => '/(?:\$container|\$this->container|\$this->container\(\)|\$this->context\(\)->container\(\))\s*->\s*get\s*\(/',
            ],
            'container_classification' => 'actionable',
        ],
        'bootstrap_container_lookups' => [
            'guard_kind' => 'container_lookup',
            'classification' => 'bootstrap_boundary',
            'priority' => 80,
            'reason' => 'Application bootstrap/default-provider container lookups that intentionally bridge runtime registries and configured defaults.',
            'patterns' => [
                'container_get' => '/(?:\$container|\$this->container|\$this->container\(\)|\$this->context\(\)->container\(\))\s*->\s*get\s*\(/',
            ],
            'container_classification' => 'bootstrap_boundary',
        ],
        'composition_container_lookups' => [
            'guard_kind' => 'container_lookup',
            'classification' => 'composition_boundary',
            'priority' => 90,
            'reason' => 'Container lookups that remain inside explicit DI composition-root leaves.',
            'patterns' => [
                'container_get' => '/(?:\$container|\$this->container|\$this->container\(\)|\$this->context\(\)->container\(\))\s*->\s*get\s*\(/',
            ],
            'container_classification' => 'composition_boundary',
        ],
        'unmanaged_loading_statements' => [
            'guard_kind' => 'loading_statement',
            'classification' => 'actionable',
            'priority' => 30,
            'reason' => 'Loading statements outside entrypoints, extension APIs, and tooling should move behind explicit loader adapters.',
            'patterns' => [
                'loading_statement' => '/\b(?:include|include_once|require|require_once)\b/',
            ],
            'loading_classification' => 'actionable',
        ],
        'intentional_loading_boundaries' => [
            'guard_kind' => 'loading_statement',
            'classification' => 'intentional_compatibility',
            'priority' => 100,
            'reason' => 'Loading statements that remain inside documented extension/loading adapter boundaries.',
            'patterns' => [
                'loading_statement' => '/\b(?:include|include_once|require|require_once)\b/',
            ],
            'loading_classification' => 'intentional_compatibility',
        ],
        'tooling_loading_statements' => [
            'guard_kind' => 'loading_statement',
            'classification' => 'tooling_support',
            'priority' => 110,
            'reason' => 'Loading statements used by AI/developer tooling rather than production runtime paths.',
            'patterns' => [
                'loading_statement' => '/\b(?:include|include_once|require|require_once)\b/',
            ],
            'loading_classification' => 'tooling_support',
        ],
        'entrypoint_loading_statements' => [
            'guard_kind' => 'loading_statement',
            'classification' => 'entrypoint_boundary',
            'priority' => 120,
            'reason' => 'Front-controller/bootstrap entrypoints intentionally load the Composer or framework bootstrap layer.',
            'patterns' => [
                'loading_statement' => '/\b(?:include|include_once|require|require_once)\b/',
            ],
            'loading_classification' => 'entrypoint_boundary',
        ],
        'explicit_native_construction_boundaries' => [
            'guard_kind' => 'native_concrete_construction',
            'classification' => 'intentional_compatibility',
            'priority' => 130,
            'reason' => 'Native framework/project concrete constructions that are pinned to explicit factories or adapter boundaries.',
            'patterns' => [
                'request_input_construction' => '/new\s+\\\\?fan\\\\core\\\\service\\\\request_input\s*\(/',
                'exception_construction' => '/new\s+\\\\?fan\\\\(?:core|project)\\\\exception\\\\[A-Za-z0-9_\\\\]+\s*\(/',
                'config_row_construction' => '/new\s+\\\\?fan\\\\(?:core|project)\\\\service\\\\config\\\\row\s*\(/',
                'phpmailer_construction' => '/new\s+\\\\?PHPMailer\\\\PHPMailer\\\\PHPMailer\s*\(/',
            ],
        ],
    ];
}

function php_fan_ai_source_inventory_queue_locations(string $root, array $productionPhpFiles, array $definition): array
{
    $locations = [];
    foreach ($productionPhpFiles as $relativeFile) {
        if (!is_string($relativeFile)) {
            continue;
        }
        if (!php_fan_ai_source_inventory_file_matches_definition($relativeFile, $definition)) {
            continue;
        }

        $absoluteFile = rtrim($root, DIRECTORY_SEPARATOR) . '/' . $relativeFile;
        if (!is_file($absoluteFile)) {
            continue;
        }

        $source = (string)file_get_contents($absoluteFile);
        $code = php_fan_ai_code_without_comments_and_strings($source);
        foreach (($definition['patterns'] ?? []) as $patternName => $pattern) {
            if (!is_string($patternName) || !is_string($pattern)) {
                continue;
            }
            if (preg_match_all($pattern, $code, $matches, PREG_OFFSET_CAPTURE) === 0) {
                continue;
            }
            foreach ($matches[0] as [$value, $offset]) {
                $line = substr_count($source, "\n", 0, $offset) + 1;
                $locations[] = [
                    'file' => $relativeFile,
                    'line' => $line,
                    'pattern' => $patternName,
                    'value' => trim((string)$value),
                ];
            }
        }
    }

    usort(
        $locations,
        static fn(array $left, array $right): int => [
            (string)$left['file'],
            (int)$left['line'],
            (string)$left['pattern'],
            (string)$left['value'],
        ] <=> [
            (string)$right['file'],
            (int)$right['line'],
            (string)$right['pattern'],
            (string)$right['value'],
        ]
    );

    return $locations;
}

function php_fan_ai_source_inventory_file_matches_definition(string $relativeFile, array $definition): bool
{
    if (isset($definition['container_classification']) && is_string($definition['container_classification'])) {
        return php_fan_ai_source_inventory_container_classification($relativeFile) === $definition['container_classification'];
    }

    if (array_key_exists('file_category', $definition)) {
        $category = php_fan_ai_dynamic_boundary_category_for_file($relativeFile);

        return $category === $definition['file_category'];
    }

    if (isset($definition['loading_classification']) && is_string($definition['loading_classification'])) {
        return php_fan_ai_source_inventory_loading_classification($relativeFile) === $definition['loading_classification'];
    }

    return true;
}

function php_fan_ai_source_inventory_container_classification(string $relativeFile): string
{
    if (php_fan_ai_dynamic_boundary_category_for_file($relativeFile) === 'composition_roots') {
        return 'composition_boundary';
    }

    if (in_array($relativeFile, php_fan_ai_source_inventory_bootstrap_container_boundary_files(), true)) {
        return 'bootstrap_boundary';
    }

    return 'actionable';
}

function php_fan_ai_source_inventory_bootstrap_container_boundary_files(): array
{
    return [
        'core/application/application.php',
        'core/application/context.php',
        'core/di/application_compiled_template_adapter_defaults_provider.php',
        'core/di/application_image_adapter_defaults_provider.php',
        'core/di/application_state_registry.php',
        'core/di/application_storage_adapter_defaults_provider.php',
        'core/factory/application_registry_defaults_provider_factory.php',
    ];
}

function php_fan_ai_source_inventory_loading_classification(string $relativeFile): string
{
    if ($relativeFile === 'htdocs/index.php') {
        return 'entrypoint_boundary';
    }

    if (str_starts_with($relativeFile, 'tools/')) {
        return 'tooling_support';
    }

    $dynamicBoundaryMap = php_fan_ai_dynamic_boundaries_map();
    if (in_array($relativeFile, $dynamicBoundaryMap['manual_loading_adapters'] ?? [], true)) {
        return 'intentional_compatibility';
    }

    return match (php_fan_ai_dynamic_boundary_category_for_file($relativeFile)) {
        'extension_api' => 'intentional_compatibility',
        'tooling_support' => 'tooling_support',
        'composition_roots' => 'composition_boundary',
        default => 'actionable',
    };
}

function php_fan_ai_source_inventory_queue_summary(string $queueId, array $definition, array $locations): array
{
    $files = array_values(array_unique(array_column($locations, 'file')));
    sort($files);
    $classification = (string)($definition['classification'] ?? 'actionable');
    if ($classification === 'actionable' && $locations === []) {
        $classification = 'clean';
    }

    return [
        'id' => $queueId,
        'guard_kind' => (string)($definition['guard_kind'] ?? 'unknown'),
        'classification' => $classification,
        'priority' => (int)($definition['priority'] ?? 100),
        'reason' => (string)($definition['reason'] ?? ''),
        'count' => count($locations),
        'files' => $files,
        'locations' => $locations,
    ];
}

function php_fan_ai_source_inventory_queue(array $map, string $queueId): ?array
{
    $queue = $map['source_inventory']['queues'][$queueId] ?? null;

    return is_array($queue) ? $queue : null;
}

function php_fan_ai_dynamic_boundary_summary(
    array $map,
    ?string $boundaryKind = null,
    array $excludeCategories = [],
    array $includeCategories = [],
    bool $rankByCount = false,
    int $minimumCount = 0
): array
{
    $summary = [];
    $locationsByFile = $map['dynamic_boundaries']['locations'] ?? [];
    if (!is_array($locationsByFile)) {
        return [];
    }

    foreach ($locationsByFile as $file => $locations) {
        if (!is_string($file) || !is_array($locations)) {
            continue;
        }
        $locations = php_fan_ai_dynamic_boundary_filter_locations($locations, $boundaryKind);
        if ($boundaryKind !== null && $locations === []) {
            continue;
        }
        $categoryDetails = php_fan_ai_dynamic_boundary_category_details_for_file($file) ?? [];
        $category = $categoryDetails['category'] ?? null;
        if ($includeCategories !== [] && (!is_string($category) || !in_array($category, $includeCategories, true))) {
            continue;
        }
        if (is_string($category) && in_array($category, $excludeCategories, true)) {
            continue;
        }
        $count = count($locations);
        if ($count < $minimumCount) {
            continue;
        }
        $patterns = [];
        foreach ($locations as $location) {
            if (is_array($location) && is_string($location['pattern'] ?? null)) {
                $patterns[] = $location['pattern'];
            }
        }
        $patterns = array_values(array_unique($patterns));
        sort($patterns);

        $summary[] = [
            'file' => $file,
            'category' => $category,
            'boundary_kind' => php_fan_ai_dynamic_boundary_kind_for_locations($file, $locations),
            'composition_leaf_kind' => \fan\core\ai\dynamic_boundary::compositionLeafKind(is_string($category) ? $category : null, $count),
            'reason' => $categoryDetails['reason'] ?? '',
            'count' => $count,
            'patterns' => $patterns,
        ];
    }

    usort(
        $summary,
        $rankByCount
            ? static fn(array $left, array $right): int => [
                -(int)$left['count'],
                (string)$left['file'],
            ] <=> [
                -(int)$right['count'],
                (string)$right['file'],
            ]
            : static fn(array $left, array $right): int => [
                (string)($left['category'] ?? ''),
                (string)$left['file'],
            ] <=> [
                (string)($right['category'] ?? ''),
                (string)$right['file'],
            ]
    );

    return $summary;
}

function php_fan_ai_dynamic_boundary_composition_queues(array $map): array
{
    $terminalLeaves = [];
    $actionableRoots = [];
    $locationsByFile = $map['locations'] ?? [];
    if (!is_array($locationsByFile)) {
        $locationsByFile = $map['dynamic_boundaries']['locations'] ?? [];
    }
    if (!is_array($locationsByFile)) {
        return [
            'terminal_composition_leaves' => [],
            'actionable_composition_roots' => [],
        ];
    }

    foreach ($locationsByFile as $file => $locations) {
        if (!is_string($file) || !is_array($locations)) {
            continue;
        }
        $categoryDetails = php_fan_ai_dynamic_boundary_category_details_for_file($file) ?? [];
        $kind = \fan\core\ai\dynamic_boundary::compositionLeafKind(
            is_string($categoryDetails['category'] ?? null) ? $categoryDetails['category'] : null,
            count($locations)
        );
        if ($kind === \fan\core\ai\dynamic_boundary::TERMINAL_COMPOSITION_LEAF) {
            $terminalLeaves[] = $file;
        }
        if ($kind === \fan\core\ai\dynamic_boundary::ACTIONABLE_COMPOSITION_ROOT) {
            $actionableRoots[] = $file;
        }
    }

    sort($terminalLeaves);
    sort($actionableRoots);

    return [
        'terminal_composition_leaves' => $terminalLeaves,
        'actionable_composition_roots' => $actionableRoots,
    ];
}

function php_fan_ai_dynamic_boundary_named_migration_debt_queues(array $map): array
{
    $actionableLocationsByFile = [];
    $intentionalLocationsByFile = [];
    $locationsByFile = $map['locations'] ?? [];
    if (!is_array($locationsByFile)) {
        $locationsByFile = $map['dynamic_boundaries']['locations'] ?? [];
    }
    if (!is_array($locationsByFile)) {
        return [
            'actionable_named_migration_debt' => [],
            'intentional_named_compatibility_boundaries' => [],
        ];
    }

    foreach ($locationsByFile as $file => $locations) {
        if (!is_string($file) || !is_array($locations)) {
            continue;
        }
        $categoryDetails = php_fan_ai_dynamic_boundary_category_details_for_file($file) ?? [];
        $category = $categoryDetails['category'] ?? null;
        foreach ($locations as $location) {
            if (!is_array($location) || ($location['boundary_kind'] ?? null) !== \fan\core\ai\dynamic_boundary::NAMED_DEFAULT_CLOSURE) {
                continue;
            }
            $line = (int)($location['line'] ?? 0);
            $debtKind = \fan\core\ai\dynamic_boundary::namedMigrationDebtKindForLocation($file, $line);
            if ($debtKind === null) {
                continue;
            }
            $location['named_debt_kind'] = $debtKind;
            if ($debtKind === \fan\core\ai\dynamic_boundary::INTENTIONAL_NAMED_COMPATIBILITY) {
                $intentionalLocationsByFile[$file][] = $location;
                continue;
            }
            if ($category === 'migration_debt') {
                $actionableLocationsByFile[$file][] = $location;
            }
        }
    }

    return [
        'actionable_named_migration_debt' => php_fan_ai_dynamic_boundary_named_debt_summary_from_locations(
            $actionableLocationsByFile,
            \fan\core\ai\dynamic_boundary::ACTIONABLE_NAMED_MIGRATION_DEBT
        ),
        'intentional_named_compatibility_boundaries' => php_fan_ai_dynamic_boundary_named_debt_summary_from_locations(
            $intentionalLocationsByFile,
            \fan\core\ai\dynamic_boundary::INTENTIONAL_NAMED_COMPATIBILITY
        ),
    ];
}

function php_fan_ai_dynamic_boundary_named_debt_summary_from_locations(array $locationsByFile, string $namedDebtKind): array
{
    $summary = [];
    foreach ($locationsByFile as $file => $locations) {
        if (!is_string($file) || !is_array($locations) || $locations === []) {
            continue;
        }
        $categoryDetails = php_fan_ai_dynamic_boundary_category_details_for_file($file) ?? [];
        $patterns = [];
        foreach ($locations as $location) {
            if (is_array($location) && is_string($location['pattern'] ?? null)) {
                $patterns[] = $location['pattern'];
            }
        }
        $patterns = array_values(array_unique($patterns));
        sort($patterns);

        $summary[] = [
            'file' => $file,
            'category' => $categoryDetails['category'] ?? null,
            'boundary_kind' => \fan\core\ai\dynamic_boundary::NAMED_DEFAULT_CLOSURE,
            'named_debt_kind' => $namedDebtKind,
            'reason' => $categoryDetails['reason'] ?? '',
            'count' => count($locations),
            'patterns' => $patterns,
            'locations' => array_values($locations),
        ];
    }

    usort(
        $summary,
        static fn(array $left, array $right): int => [
            -(int)$left['count'],
            (string)$left['file'],
        ] <=> [
            -(int)$right['count'],
            (string)$right['file'],
        ]
    );

    return $summary;
}

function php_fan_ai_dynamic_boundary_named_migration_debt_summary(array $map): array
{
    $summary = $map['dynamic_boundaries']['actionable_named_migration_debt'] ?? null;
    if (is_array($summary)) {
        return array_values($summary);
    }

    return php_fan_ai_dynamic_boundary_named_migration_debt_queues($map)['actionable_named_migration_debt'];
}

function php_fan_ai_dynamic_boundary_named_migration_debt_summary_for_file(array $map, string $file): array
{
    foreach (php_fan_ai_dynamic_boundary_named_migration_debt_summary($map) as $entry) {
        if (($entry['file'] ?? null) === $file) {
            return $entry;
        }
    }

    $categoryDetails = php_fan_ai_dynamic_boundary_category_details_for_file($file) ?? [];

    return [
        'file' => $file,
        'category' => $categoryDetails['category'] ?? null,
        'boundary_kind' => \fan\core\ai\dynamic_boundary::NAMED_DEFAULT_CLOSURE,
        'named_debt_kind' => \fan\core\ai\dynamic_boundary::ACTIONABLE_NAMED_MIGRATION_DEBT,
        'reason' => $categoryDetails['reason'] ?? '',
        'count' => 0,
        'patterns' => [],
        'locations' => [],
    ];
}

function php_fan_ai_dynamic_boundary_summary_for_file(array $map, string $file, ?string $boundaryKind = null): array
{
    $locationsByFile = $map['dynamic_boundaries']['locations'] ?? [];
    $locations = is_array($locationsByFile) && is_array($locationsByFile[$file] ?? null)
        ? $locationsByFile[$file]
        : [];
    $locations = php_fan_ai_dynamic_boundary_filter_locations($locations, $boundaryKind);
    $categoryDetails = php_fan_ai_dynamic_boundary_category_details_for_file($file) ?? [];
    $patterns = [];
    foreach ($locations as $location) {
        if (is_array($location) && is_string($location['pattern'] ?? null)) {
            $patterns[] = $location['pattern'];
        }
    }
    $patterns = array_values(array_unique($patterns));
    sort($patterns);

    return [
        'file' => $file,
        'category' => $categoryDetails['category'] ?? null,
        'boundary_kind' => php_fan_ai_dynamic_boundary_kind_for_locations($file, $locations),
        'composition_leaf_kind' => \fan\core\ai\dynamic_boundary::compositionLeafKind(
            is_string($categoryDetails['category'] ?? null) ? $categoryDetails['category'] : null,
            count($locations)
        ),
        'reason' => $categoryDetails['reason'] ?? '',
        'count' => count($locations),
        'patterns' => $patterns,
        'locations' => $locations,
    ];
}

function php_fan_ai_dynamic_boundary_filter_locations(array $locations, ?string $boundaryKind): array
{
    return \fan\core\ai\dynamic_boundary::filterLocations($locations, $boundaryKind);
}

function php_fan_ai_count_strict_files(string $root, array $relativePhpFiles): int
{
    $count = 0;
    foreach ($relativePhpFiles as $relativeFile) {
        $source = file_get_contents($root . '/' . $relativeFile);
        if (is_string($source) && str_contains($source, 'declare(strict_types=1)')) {
            $count++;
        }
    }

    return $count;
}

function php_fan_ai_meta_files(string $root): array
{
    return php_fan_ai_files(
        $root,
        ['core', 'project', 'htdocs'],
        static fn(string $file): bool => str_ends_with($file, '.meta.php')
    );
}

function php_fan_ai_template_files(string $root): array
{
    return php_fan_ai_files(
        $root,
        ['core', 'project', 'htdocs'],
        static fn(string $file): bool => str_ends_with($file, '.tpl')
    );
}

function php_fan_ai_php_files(string $root, array $roots): array
{
    return php_fan_ai_files(
        $root,
        $roots,
        static fn(string $file): bool => str_ends_with($file, '.php')
    );
}

function php_fan_ai_files(string $root, array $roots, callable $filter): array
{
    $files = [];
    foreach ($roots as $relativeRoot) {
        $absoluteRoot = $root . '/' . $relativeRoot;
        if (!is_dir($absoluteRoot)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($absoluteRoot, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $relativePath = str_replace($root . '/', '', $file->getPathname());
            if (php_fan_ai_is_excluded_path($relativePath)) {
                continue;
            }

            if ($filter($relativePath)) {
                $files[] = $relativePath;
            }
        }
    }

    sort($files);

    return $files;
}

function php_fan_ai_is_excluded_path(string $relativePath): bool
{
    foreach (['vendor/', 'node_modules/', 'legacy_assets/', '.git/'] as $prefix) {
        if (str_starts_with($relativePath, $prefix)) {
            return true;
        }
    }

    return str_contains($relativePath, '/.venv/');
}

function php_fan_ai_render_markdown(array $map): string
{
    $lines = [
        '# PHP-FAN AI Map',
        '',
        '- Generated at: ' . $map['generated_at'],
        '- Production PHP files: ' . $map['counts']['production_php_files'],
        '- Strict production PHP files: ' . $map['counts']['strict_production_php_files'],
        '- Registered service ids: ' . $map['counts']['registered_service_ids'],
        '- Referenced service ids: ' . $map['counts']['referenced_service_ids'],
        '- Meta files: ' . $map['counts']['meta_files'],
        '- Template files: ' . $map['counts']['template_files'],
        '',
        '## Commands',
        '',
    ];

    foreach ($map['commands'] as $name => $command) {
        $lines[] = '- `' . $name . '`: `' . $command . '`';
    }

    return implode("\n", $lines) . "\n";
}

function php_fan_ai_write_map(string $root, array $map): string
{
    $target = $root . '/.ai/map.json';
    $encoded = json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if (!is_string($encoded)) {
        throw new RuntimeException('Unable to encode AI map.');
    }
    file_put_contents($target, $encoded . "\n");

    return $target;
}

function php_fan_ai_validate_json_schema_subset(mixed $value, array $schema, string $path = '$', ?array $rootSchema = null): array
{
    $rootSchema ??= $schema;

    if (isset($schema['$ref']) && is_string($schema['$ref'])) {
        $resolved = php_fan_ai_resolve_json_schema_ref($rootSchema, $schema['$ref']);
        if ($resolved === null) {
            return [$path . ' has unresolved schema ref: ' . $schema['$ref']];
        }

        return php_fan_ai_validate_json_schema_subset($value, $resolved, $path, $rootSchema);
    }

    $errors = [];
    if (array_key_exists('const', $schema) && $value !== $schema['const']) {
        $errors[] = $path . ' must equal ' . php_fan_ai_json_schema_value_label($schema['const']);
    }

    if (isset($schema['type']) && !php_fan_ai_json_schema_type_matches($value, $schema['type'])) {
        $errors[] = $path . ' expected type ' . php_fan_ai_json_schema_type_label($schema['type']);

        return $errors;
    }

    $types = php_fan_ai_json_schema_types($schema['type'] ?? null);
    if ($types === [] && isset($schema['properties']) || in_array('object', $types, true)) {
        if (is_array($value)) {
            $properties = is_array($schema['properties'] ?? null) ? $schema['properties'] : [];
            foreach (($schema['required'] ?? []) as $requiredKey) {
                if (is_string($requiredKey) && !array_key_exists($requiredKey, $value)) {
                    $errors[] = $path . ' missing required key: ' . $requiredKey;
                }
            }

            foreach ($properties as $key => $propertySchema) {
                if (!is_string($key) || !is_array($propertySchema) || !array_key_exists($key, $value)) {
                    continue;
                }
                $errors = array_merge(
                    $errors,
                    php_fan_ai_validate_json_schema_subset($value[$key], $propertySchema, $path . '.' . $key, $rootSchema)
                );
            }

            if (array_key_exists('additionalProperties', $schema)) {
                $additional = $schema['additionalProperties'];
                foreach ($value as $key => $entryValue) {
                    $keyLabel = (string)$key;
                    if (array_key_exists($keyLabel, $properties)) {
                        continue;
                    }
                    if ($additional === false) {
                        $errors[] = $path . ' has unexpected key: ' . $keyLabel;
                    } elseif (is_array($additional)) {
                        $errors = array_merge(
                            $errors,
                            php_fan_ai_validate_json_schema_subset($entryValue, $additional, $path . '.' . $keyLabel, $rootSchema)
                        );
                    }
                }
            }
        }
    }

    if (in_array('array', $types, true) && is_array($value) && isset($schema['items']) && is_array($schema['items'])) {
        foreach ($value as $index => $item) {
            $errors = array_merge(
                $errors,
                php_fan_ai_validate_json_schema_subset($item, $schema['items'], $path . '[' . $index . ']', $rootSchema)
            );
        }
    }

    if (in_array('string', $types, true) && is_string($value)) {
        if (isset($schema['minLength']) && is_int($schema['minLength']) && strlen($value) < $schema['minLength']) {
            $errors[] = $path . ' must be at least ' . $schema['minLength'] . ' character(s).';
        }
        if (($schema['format'] ?? null) === 'date-time' && strtotime($value) === false) {
            $errors[] = $path . ' must be a date-time string.';
        }
    }

    if (in_array('integer', $types, true) && is_int($value) && isset($schema['minimum']) && is_int($schema['minimum']) && $value < $schema['minimum']) {
        $errors[] = $path . ' must be greater than or equal to ' . $schema['minimum'] . '.';
    }

    return $errors;
}

function php_fan_ai_resolve_json_schema_ref(array $rootSchema, string $ref): ?array
{
    if (!str_starts_with($ref, '#/')) {
        return null;
    }

    $target = $rootSchema;
    foreach (explode('/', substr($ref, 2)) as $segment) {
        $segment = strtr($segment, ['~1' => '/', '~0' => '~']);
        if (!is_array($target) || !array_key_exists($segment, $target)) {
            return null;
        }
        $target = $target[$segment];
    }

    return is_array($target) ? $target : null;
}

function php_fan_ai_json_schema_type_matches(mixed $value, mixed $type): bool
{
    foreach (php_fan_ai_json_schema_types($type) as $candidate) {
        if ($candidate === 'object' && is_array($value) && (!array_is_list($value) || $value === [])) {
            return true;
        }
        if ($candidate === 'array' && is_array($value) && array_is_list($value)) {
            return true;
        }
        if ($candidate === 'string' && is_string($value)) {
            return true;
        }
        if ($candidate === 'integer' && is_int($value)) {
            return true;
        }
        if ($candidate === 'boolean' && is_bool($value)) {
            return true;
        }
        if ($candidate === 'null' && $value === null) {
            return true;
        }
    }

    return false;
}

function php_fan_ai_json_schema_types(mixed $type): array
{
    if (is_string($type)) {
        return [$type];
    }
    if (!is_array($type)) {
        return [];
    }

    return array_values(array_filter($type, static fn(mixed $entry): bool => is_string($entry)));
}

function php_fan_ai_json_schema_type_label(mixed $type): string
{
    $types = php_fan_ai_json_schema_types($type);

    return $types === [] ? 'unknown' : implode('|', $types);
}

function php_fan_ai_json_schema_value_label(mixed $value): string
{
    $encoded = json_encode($value, JSON_UNESCAPED_SLASHES);

    return is_string($encoded) ? $encoded : gettype($value);
}

function php_fan_ai_validate_map_contract(string $root, array $map): array
{
    $errors = [];
    $schema = null;

    if (!is_file($root . '/.ai/map.schema.json')) {
        $errors[] = '.ai/map.schema.json is missing.';
    } else {
        $schema = json_decode((string)file_get_contents($root . '/.ai/map.schema.json'), true);
        if (!is_array($schema)) {
            $errors[] = '.ai/map.schema.json is not valid JSON.';
        }
    }

    if (is_array($schema)) {
        foreach (php_fan_ai_validate_json_schema_subset($map, $schema) as $schemaError) {
            $errors[] = 'AI map JSON schema: ' . $schemaError;
        }
    }

    foreach (['schema_version', 'generated_at', 'entrypoints', 'source_roots', 'test_roots', 'autoload', 'ai_docs', 'commands', 'counts', 'files', 'services', 'metadata', 'dynamic_boundaries', 'source_inventory', 'verification'] as $key) {
        if (!array_key_exists($key, $map)) {
            $errors[] = 'AI map is missing required key: ' . $key;
        }
    }

    if (($map['schema_version'] ?? null) !== 1) {
        $errors[] = 'AI map schema_version must be 1.';
    }
    if (!is_string($map['generated_at'] ?? null) || strtotime($map['generated_at']) === false) {
        $errors[] = 'AI map generated_at must be an ISO-like date string.';
    }

    foreach (['entrypoints', 'autoload', 'commands', 'counts', 'files', 'services', 'metadata', 'dynamic_boundaries', 'source_inventory', 'verification'] as $key) {
        if (isset($map[$key]) && !is_array($map[$key])) {
            $errors[] = 'AI map key must be an array: ' . $key;
        }
    }
    foreach (['source_roots', 'test_roots', 'ai_docs'] as $key) {
        if (isset($map[$key]) && !php_fan_ai_is_string_list($map[$key])) {
            $errors[] = 'AI map key must be a list of strings: ' . $key;
        }
    }
    foreach (($map['commands'] ?? []) as $name => $command) {
        if (!is_string($name) || !is_string($command) || $command === '') {
            $errors[] = 'AI map command entries must be non-empty strings.';
            break;
        }
    }

    foreach (['registered', 'aliases', 'referenced', 'referenced_locations', 'constants', 'descriptors'] as $key) {
        if (!isset($map['services'][$key]) || !is_array($map['services'][$key])) {
            $errors[] = 'AI map services section is missing array key: ' . $key;
        }
    }
    foreach (($map['services']['referenced_locations'] ?? []) as $id => $locations) {
        if (!is_string($id) || !php_fan_ai_is_source_location_list($locations)) {
            $errors[] = 'AI map referenced_locations entries must be source-location lists keyed by service id.';
            break;
        }
    }
    foreach (($map['services']['descriptors'] ?? []) as $id => $descriptor) {
        if (!is_string($id) || !is_array($descriptor)) {
            $errors[] = 'AI map service descriptor entries must be arrays keyed by service id.';
            continue;
        }
        if (($descriptor['id'] ?? null) !== $id) {
            $errors[] = 'AI map descriptor id mismatch for service: ' . $id;
        }
        foreach (['registrar_files', 'creator_methods', 'dependencies', 'aliases'] as $listKey) {
            if (!isset($descriptor[$listKey]) || !php_fan_ai_is_string_list($descriptor[$listKey])) {
                $errors[] = 'AI map descriptor "' . $id . '" must have string-list key: ' . $listKey;
            }
        }
        if (array_key_exists('shared', $descriptor) && !is_bool($descriptor['shared']) && $descriptor['shared'] !== null) {
            $errors[] = 'AI map descriptor "' . $id . '" shared must be boolean or null.';
        }
        foreach (['class', 'factory', 'config_key'] as $nullableStringKey) {
            if (!array_key_exists($nullableStringKey, $descriptor) || (!is_string($descriptor[$nullableStringKey]) && $descriptor[$nullableStringKey] !== null)) {
                $errors[] = 'AI map descriptor "' . $id . '" must have nullable string key: ' . $nullableStringKey;
            }
        }
        if (!isset($descriptor['lifetime_reason']) || !is_string($descriptor['lifetime_reason']) || $descriptor['lifetime_reason'] === '') {
            $errors[] = 'AI map descriptor "' . $id . '" must have non-empty lifetime_reason.';
        }
        if (!isset($descriptor['source_edges']) || !is_array($descriptor['source_edges'])) {
            $errors[] = 'AI map descriptor "' . $id . '" must have source_edges.';
        } else {
            foreach (['aliases', 'classes', 'dependencies', 'factories', 'config_keys', 'runtime_arguments', 'registrar_files', 'creator_methods'] as $listKey) {
                if (!isset($descriptor['source_edges'][$listKey]) || !php_fan_ai_is_string_list($descriptor['source_edges'][$listKey])) {
                    $errors[] = 'AI map descriptor "' . $id . '" source_edges must have string-list key: ' . $listKey;
                }
            }
        }
        if (!isset($descriptor['referenced_by']) || !is_array($descriptor['referenced_by'])) {
            $errors[] = 'AI map descriptor "' . $id . '" must have referenced_by.';
        } else {
            if (!isset($descriptor['referenced_by']['files']) || !php_fan_ai_is_string_list($descriptor['referenced_by']['files'])) {
                $errors[] = 'AI map descriptor "' . $id . '" referenced_by must have string-list key: files';
            }
            if (!isset($descriptor['referenced_by']['locations']) || !php_fan_ai_is_source_location_list($descriptor['referenced_by']['locations'])) {
                $errors[] = 'AI map descriptor "' . $id . '" referenced_by must have source-location-list key: locations';
            }
        }
        if (!isset($descriptor['source_locations']) || !is_array($descriptor['source_locations'])) {
            $errors[] = 'AI map descriptor "' . $id . '" must have source_locations.';
        } else {
            foreach (['registrations', 'creator_methods', 'classes', 'dependencies', 'factories', 'config_keys', 'runtime_arguments', 'aliases'] as $listKey) {
                if (!isset($descriptor['source_locations'][$listKey]) || !php_fan_ai_is_source_location_list($descriptor['source_locations'][$listKey])) {
                    $errors[] = 'AI map descriptor "' . $id . '" source_locations must have source-location-list key: ' . $listKey;
                }
            }
        }
        if (!isset($descriptor['factory_origin']) || !is_array($descriptor['factory_origin'])) {
            $errors[] = 'AI map descriptor "' . $id . '" must have factory_origin.';
        } else {
            foreach (['registrar_files', 'creator_methods'] as $listKey) {
                if (!isset($descriptor['factory_origin'][$listKey]) || !php_fan_ai_is_string_list($descriptor['factory_origin'][$listKey])) {
                    $errors[] = 'AI map descriptor "' . $id . '" factory_origin must have string-list key: ' . $listKey;
                }
            }
        }
        if (!isset($descriptor['factory_arguments']) || !is_array($descriptor['factory_arguments'])) {
            $errors[] = 'AI map descriptor "' . $id . '" must have factory_arguments.';
        } else {
            foreach (['container_dependencies', 'runtime_arguments'] as $listKey) {
                if (!isset($descriptor['factory_arguments'][$listKey]) || !php_fan_ai_is_string_list($descriptor['factory_arguments'][$listKey])) {
                    $errors[] = 'AI map descriptor "' . $id . '" factory_arguments must have string-list key: ' . $listKey;
                }
            }
        }
        if (!isset($descriptor['creator_method_arguments']) || !is_array($descriptor['creator_method_arguments'])) {
            $errors[] = 'AI map descriptor "' . $id . '" must have creator_method_arguments.';
        } else {
            foreach ($descriptor['creator_method_arguments'] as $methodName => $methodArguments) {
                if (!is_string($methodName) || !is_array($methodArguments)) {
                    $errors[] = 'AI map descriptor "' . $id . '" creator_method_arguments entries must be arrays keyed by method name.';
                    continue;
                }
                foreach (['parameters', 'runtime_arguments', 'container_dependencies', 'optional_arguments'] as $listKey) {
                    if (!isset($methodArguments[$listKey]) || !php_fan_ai_is_string_list($methodArguments[$listKey])) {
                        $errors[] = 'AI map descriptor "' . $id . '" creator method "' . $methodName . '" must have string-list key: ' . $listKey;
                    }
                }
            }
        }
    }

    if (($map['metadata']['meta_schema'] ?? null) !== '.ai/meta.schema.json') {
        $errors[] = 'AI map metadata.meta_schema must point to .ai/meta.schema.json.';
    }
    foreach (['extension_api', 'composition_roots', 'tooling_support', 'migration_debt'] as $category) {
        if (!isset($map['dynamic_boundaries']['category_reasons'][$category]) || !is_string($map['dynamic_boundaries']['category_reasons'][$category]) || $map['dynamic_boundaries']['category_reasons'][$category] === '') {
            $errors[] = 'AI map dynamic_boundaries.category_reasons must have non-empty reason for: ' . $category;
        }
    }
    if (!isset($map['dynamic_boundaries']['locations']) || !is_array($map['dynamic_boundaries']['locations'])) {
        $errors[] = 'AI map dynamic_boundaries.locations must be an array.';
    } else {
        foreach ($map['dynamic_boundaries']['locations'] as $file => $locations) {
            if (!is_string($file) || !php_fan_ai_is_dynamic_boundary_location_list($locations)) {
                $errors[] = 'AI map dynamic_boundaries.locations must be keyed by file and contain dynamic boundary locations.';
                break;
            }
        }
    }
    foreach (['terminal_composition_leaves', 'actionable_composition_roots'] as $key) {
        if (!isset($map['dynamic_boundaries'][$key]) || !php_fan_ai_is_string_list($map['dynamic_boundaries'][$key])) {
            $errors[] = 'AI map dynamic_boundaries.' . $key . ' must be a list of strings.';
        }
    }
    foreach (['actionable_named_migration_debt', 'intentional_named_compatibility_boundaries'] as $key) {
        if (!isset($map['dynamic_boundaries'][$key]) || !php_fan_ai_is_dynamic_boundary_named_debt_summary_list($map['dynamic_boundaries'][$key])) {
            $errors[] = 'AI map dynamic_boundaries.' . $key . ' must be a named-debt summary list.';
        }
    }
    if (!isset($map['source_inventory']['queues']) || !is_array($map['source_inventory']['queues'])) {
        $errors[] = 'AI map source_inventory.queues must be an array.';
    } else {
        foreach ($map['source_inventory']['queues'] as $queueId => $queue) {
            if (!is_string($queueId) || !php_fan_ai_is_source_inventory_queue($queue)) {
                $errors[] = 'AI map source_inventory.queues must contain source-inventory queues keyed by id.';
                break;
            }
            if (($queue['id'] ?? null) !== $queueId) {
                $errors[] = 'AI map source_inventory queue id mismatch for: ' . $queueId;
            }
        }
    }
    if (!isset($map['source_inventory']['next']) || !is_array($map['source_inventory']['next']) || !array_is_list($map['source_inventory']['next'])) {
        $errors[] = 'AI map source_inventory.next must be a list.';
    } else {
        foreach ($map['source_inventory']['next'] as $queue) {
            if (!php_fan_ai_is_source_inventory_queue($queue)) {
                $errors[] = 'AI map source_inventory.next must contain source-inventory queue summaries.';
                break;
            }
            if (($queue['classification'] ?? null) !== 'actionable' || (int)($queue['count'] ?? 0) <= 0) {
                $errors[] = 'AI map source_inventory.next must contain only actionable non-empty queues.';
                break;
            }
        }
    }
    $compositionLeafPolicy = $map['dynamic_boundaries']['composition_leaf_policy'] ?? null;
    if (!is_array($compositionLeafPolicy)) {
        $errors[] = 'AI map dynamic_boundaries.composition_leaf_policy must be an object.';
    } else {
        if (($compositionLeafPolicy['terminal_count'] ?? null) !== 1) {
            $errors[] = 'AI map dynamic_boundaries.composition_leaf_policy.terminal_count must be 1.';
        }
        if (($compositionLeafPolicy['actionable_min_count'] ?? null) !== 2) {
            $errors[] = 'AI map dynamic_boundaries.composition_leaf_policy.actionable_min_count must be 2.';
        }
        if (($compositionLeafPolicy['terminal_kind'] ?? null) !== \fan\core\ai\dynamic_boundary::TERMINAL_COMPOSITION_LEAF) {
            $errors[] = 'AI map dynamic_boundaries.composition_leaf_policy.terminal_kind must be terminal_composition_leaf.';
        }
        if (($compositionLeafPolicy['actionable_kind'] ?? null) !== \fan\core\ai\dynamic_boundary::ACTIONABLE_COMPOSITION_ROOT) {
            $errors[] = 'AI map dynamic_boundaries.composition_leaf_policy.actionable_kind must be actionable_composition_root.';
        }
        if (!is_string($compositionLeafPolicy['description'] ?? null) || $compositionLeafPolicy['description'] === '') {
            $errors[] = 'AI map dynamic_boundaries.composition_leaf_policy.description must be non-empty.';
        }
    }
    $namedMigrationDebtPolicy = $map['dynamic_boundaries']['named_migration_debt_policy'] ?? null;
    if (!is_array($namedMigrationDebtPolicy)) {
        $errors[] = 'AI map dynamic_boundaries.named_migration_debt_policy must be an object.';
    } else {
        if (($namedMigrationDebtPolicy['boundary_kind'] ?? null) !== \fan\core\ai\dynamic_boundary::NAMED_DEFAULT_CLOSURE) {
            $errors[] = 'AI map dynamic_boundaries.named_migration_debt_policy.boundary_kind must be named_default_closure_boundary.';
        }
        if (($namedMigrationDebtPolicy['category'] ?? null) !== 'migration_debt') {
            $errors[] = 'AI map dynamic_boundaries.named_migration_debt_policy.category must be migration_debt.';
        }
        if (($namedMigrationDebtPolicy['actionable_kind'] ?? null) !== \fan\core\ai\dynamic_boundary::ACTIONABLE_NAMED_MIGRATION_DEBT) {
            $errors[] = 'AI map dynamic_boundaries.named_migration_debt_policy.actionable_kind must be actionable_named_migration_debt.';
        }
        if (($namedMigrationDebtPolicy['intentional_kind'] ?? null) !== \fan\core\ai\dynamic_boundary::INTENTIONAL_NAMED_COMPATIBILITY) {
            $errors[] = 'AI map dynamic_boundaries.named_migration_debt_policy.intentional_kind must be intentional_named_compatibility_boundary.';
        }
        if (!is_string($namedMigrationDebtPolicy['description'] ?? null) || $namedMigrationDebtPolicy['description'] === '') {
            $errors[] = 'AI map dynamic_boundaries.named_migration_debt_policy.description must be non-empty.';
        }
    }
    foreach (['meta', 'templates'] as $key) {
        if (!isset($map['metadata'][$key]['files']) || !is_array($map['metadata'][$key]['files'])) {
            $errors[] = 'AI map metadata.' . $key . '.files must be an array.';
        }
    }

    $metaFiles = $map['files']['meta'] ?? [];
    $templateFiles = $map['files']['templates'] ?? [];
    if (!php_fan_ai_is_string_list($metaFiles)) {
        $errors[] = 'AI map files.meta must be a list of strings.';
        $metaFiles = [];
    }
    if (!php_fan_ai_is_string_list($templateFiles)) {
        $errors[] = 'AI map files.templates must be a list of strings.';
        $templateFiles = [];
    }

    $metadataMetaFiles = array_keys($map['metadata']['meta']['files'] ?? []);
    sort($metadataMetaFiles);
    $sortedMetaFiles = $metaFiles;
    sort($sortedMetaFiles);
    if ($metadataMetaFiles !== $sortedMetaFiles) {
        $errors[] = 'AI map metadata.meta.files keys must match files.meta.';
    }

    $metadataTemplateFiles = array_keys($map['metadata']['templates']['files'] ?? []);
    sort($metadataTemplateFiles);
    $sortedTemplateFiles = $templateFiles;
    sort($sortedTemplateFiles);
    if ($metadataTemplateFiles !== $sortedTemplateFiles) {
        $errors[] = 'AI map metadata.templates.files keys must match files.templates.';
    }

    foreach (($map['metadata']['meta']['files'] ?? []) as $metaFile => $entry) {
        if (!is_array($entry)) {
            $errors[] = 'AI map metadata meta entry must be an array: ' . $metaFile;
            continue;
        }
        foreach (['top_level_keys', 'own_keys', 'common_keys'] as $listKey) {
            if (!isset($entry[$listKey]) || !php_fan_ai_is_string_list($entry[$listKey])) {
                $errors[] = 'AI map metadata meta entry "' . $metaFile . '" must have string-list key: ' . $listKey;
            }
        }
        $pairedTemplate = $entry['paired_template'] ?? null;
        if ($pairedTemplate !== null && (!is_string($pairedTemplate) || !in_array($pairedTemplate, $templateFiles, true) || !is_file($root . '/' . $pairedTemplate))) {
            $errors[] = 'AI map paired template is invalid for meta file: ' . $metaFile;
        }
    }

    return $errors;
}

function php_fan_ai_is_string_list(mixed $value): bool
{
    if (!is_array($value) || !array_is_list($value)) {
        return false;
    }

    foreach ($value as $item) {
        if (!is_string($item)) {
            return false;
        }
    }

    return true;
}

function php_fan_ai_is_source_location_list(mixed $value): bool
{
    if (!is_array($value) || !array_is_list($value)) {
        return false;
    }

    foreach ($value as $location) {
        if (!is_array($location)) {
            return false;
        }
        if (!is_string($location['file'] ?? null) || $location['file'] === '') {
            return false;
        }
        if (!is_int($location['line'] ?? null) || $location['line'] < 1) {
            return false;
        }
        if (array_key_exists('method', $location) && (!is_string($location['method']) || $location['method'] === '')) {
            return false;
        }
        if (array_key_exists('value', $location) && !is_string($location['value'])) {
            return false;
        }
    }

    return true;
}

function php_fan_ai_is_dynamic_boundary_location_list(mixed $value): bool
{
    if (!is_array($value) || !array_is_list($value)) {
        return false;
    }

    foreach ($value as $location) {
        if (!is_array($location)) {
            return false;
        }
        if (!is_string($location['file'] ?? null) || $location['file'] === '') {
            return false;
        }
        if (!is_int($location['line'] ?? null) || $location['line'] < 1) {
            return false;
        }
        if (!is_string($location['pattern'] ?? null) || $location['pattern'] === '') {
            return false;
        }
        if (!is_string($location['value'] ?? null)) {
            return false;
        }
        if (!in_array($location['boundary_kind'] ?? null, ['method_body_or_runtime_boundary', 'named_default_closure_boundary'], true)) {
            return false;
        }
    }

    return true;
}

function php_fan_ai_is_dynamic_boundary_named_debt_summary_list(mixed $value): bool
{
    if (!is_array($value) || !array_is_list($value)) {
        return false;
    }

    foreach ($value as $entry) {
        if (!is_array($entry)) {
            return false;
        }
        if (!is_string($entry['file'] ?? null) || $entry['file'] === '') {
            return false;
        }
        if (!in_array($entry['category'] ?? null, ['extension_api', 'migration_debt'], true)) {
            return false;
        }
        if (($entry['boundary_kind'] ?? null) !== \fan\core\ai\dynamic_boundary::NAMED_DEFAULT_CLOSURE) {
            return false;
        }
        if (!in_array($entry['named_debt_kind'] ?? null, [
            \fan\core\ai\dynamic_boundary::ACTIONABLE_NAMED_MIGRATION_DEBT,
            \fan\core\ai\dynamic_boundary::INTENTIONAL_NAMED_COMPATIBILITY,
        ], true)) {
            return false;
        }
        if (!is_int($entry['count'] ?? null) || $entry['count'] < 1) {
            return false;
        }
        if (!php_fan_ai_is_string_list($entry['patterns'] ?? null)) {
            return false;
        }
        if (!is_array($entry['locations'] ?? null) || !array_is_list($entry['locations'])) {
            return false;
        }
        foreach ($entry['locations'] as $location) {
            if (!is_array($location)) {
                return false;
            }
            if (!php_fan_ai_is_dynamic_boundary_location_list([$location])) {
                return false;
            }
            if (($location['named_debt_kind'] ?? null) !== $entry['named_debt_kind']) {
                return false;
            }
        }
    }

    return true;
}

function php_fan_ai_is_source_inventory_queue(mixed $value): bool
{
    if (!is_array($value)) {
        return false;
    }

    foreach (['id', 'guard_kind', 'classification', 'reason'] as $key) {
        if (!is_string($value[$key] ?? null) || $value[$key] === '') {
            return false;
        }
    }
    if (!in_array($value['classification'], [
        'actionable',
        'clean',
        'bootstrap_boundary',
        'composition_boundary',
        'intentional_compatibility',
        'tooling_support',
        'entrypoint_boundary',
    ], true)) {
        return false;
    }
    if (!is_int($value['priority'] ?? null) || $value['priority'] < 0) {
        return false;
    }
    if (!is_int($value['count'] ?? null) || $value['count'] < 0) {
        return false;
    }
    if (!php_fan_ai_is_string_list($value['files'] ?? null)) {
        return false;
    }
    if (!php_fan_ai_is_source_inventory_location_list($value['locations'] ?? null)) {
        return false;
    }

    return true;
}

function php_fan_ai_is_source_inventory_location_list(mixed $value): bool
{
    if (!is_array($value) || !array_is_list($value)) {
        return false;
    }

    foreach ($value as $location) {
        if (!is_array($location)) {
            return false;
        }
        if (!is_string($location['file'] ?? null) || $location['file'] === '') {
            return false;
        }
        if (!is_int($location['line'] ?? null) || $location['line'] < 1) {
            return false;
        }
        if (!is_string($location['pattern'] ?? null) || $location['pattern'] === '') {
            return false;
        }
        if (!is_string($location['value'] ?? null)) {
            return false;
        }
    }

    return true;
}

function php_fan_ai_main(array $argv): int
{
    $root = dirname(__DIR__);
    $map = php_fan_ai_build_map($root);

    if (in_array('--validate', $argv, true)) {
        $errors = php_fan_ai_validate_map_contract($root, $map);
        $result = [
            'status' => $errors === [] ? 'pass' : 'fail',
            'errors' => $errors,
        ];
        if (in_array('--json', $argv, true)) {
            fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        } else {
            fwrite(STDOUT, 'AI map validation: ' . strtoupper($result['status']) . "\n");
            foreach ($errors as $error) {
                fwrite(STDOUT, '- ' . $error . "\n");
            }
        }

        return $errors === [] ? 0 : 1;
    }

    if (in_array('--write', $argv, true)) {
        $target = php_fan_ai_write_map($root, $map);
        fwrite(STDOUT, "wrote={$target}\n");

        return 0;
    }

    if (in_array('--json', $argv, true)) {
        fwrite(STDOUT, json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

        return 0;
    }

    fwrite(STDOUT, php_fan_ai_render_markdown($map));

    return 0;
}

if (PHP_SAPI === 'cli' && realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    exit(php_fan_ai_main($argv));
}
