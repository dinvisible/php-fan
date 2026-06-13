<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/tools/ai_map.php';

final class LegacyDiSourceInventoryTest extends TestCase
{
    #[DataProvider('forbiddenPatternProvider')]
    public function testProductionSourceDoesNotUseLegacyDiPattern(string $label, string $pattern): void
    {
        $matches = [];
        foreach ($this->productionPhpFiles() as $file) {
            $source = file_get_contents($file);
            $this->assertIsString($source);
            if (preg_match($pattern, $source) === 1) {
                $matches[] = $this->relativePath($file);
            }
        }

        $this->assertSame([], $matches, $label . ' found in: ' . implode(', ', $matches));
    }

    public static function forbiddenPatternProvider(): iterable
    {
        yield 'containerService gateway' => ['containerService gateway', '/\bcontainerService\s*\(/'];
        yield 'getContainerService gateway' => ['getContainerService gateway', '/\bgetContainerService\s*\(/'];
        yield 'blockService gateway' => ['blockService gateway', '/\bblockService\s*\(/'];
        yield 'generic serviceFactory' => ['generic serviceFactory', '/\bserviceFactory\b/'];
        yield 'base service resolver' => ['base service resolver', '/\bresolveService\s*\(/'];
        yield 'legacy service resolver' => ['legacy service resolver', '/\blegacyService\s*\(/'];
        yield 'service container setter' => ['service container setter', '/\bsetServiceContainer\s*\(/'];
        yield 'service container getter' => ['service container getter', '/\bgetServiceContainer\s*\(/'];
        yield 'container registry lookup' => ['container registry lookup', '/\bcontainer_registry::get\s*\(/'];
        yield 'static instance lookup' => ['static instance lookup', '/::instance\s*\(/'];
        yield 'INI parser fallback' => ['INI parser fallback', '/\bparse_ini_file\s*\(/'];
        yield 'legacy global bridge' => ['legacy global bridge', '/\blegacy_global_functions\b/'];
        yield 'legacy helper definitions' => [
            'legacy helper definitions',
            '/function\s+(?:ge|gr|se|le|role|msg|transfer_out|transfer_int|transfer_sham|d|l)\s*\(/',
        ];
        yield 'database registry lifecycle helpers' => [
            'database registry lifecycle helpers',
            '/\b(?:fixDatabaseInstances|closeDatabaseInstances)\s*\(/',
        ];
        yield 'legacy procedural callback dispatcher' => [
            'legacy procedural callback dispatcher',
            '/\bcall_user_func(?:_array)?\s*\(/',
        ];
        yield 'container provider static named constructors' => [
            'container provider static named constructors',
            '/\bcontainer_provider::(?:fromCallable|applicationDefault)\s*\(|public\s+static\s+function\s+(?:fromCallable|applicationDefault)\s*\(/',
        ];
        yield 'container registry direct provider static state' => [
            'container registry direct provider static state',
            '/private\s+static\s+\?container_provider\s+\$provider\b|private\s+static\s+\?\\\\Closure\s+\$providerFactory\b/',
        ];
        yield 'bootstrap direct context static state' => [
            'bootstrap direct context static state',
            '/private\s+static\s+\?\\\\fan\\\\core\\\\bootstrap\\\\context\s+\$context\b|private\s+static\s+\?\\\\Closure\s+\$contextFactory\b/',
        ];
        yield 'base service static listener bus' => [
            'base service static listener bus',
            '/private\s+static\s+array\s+\$listeners\b/',
        ];
        yield 'single service static instance registry' => [
            'single service static instance registry',
            '/private\s+static\s+\?array\s+\$instances\b/',
        ];
        yield 'loader router static keeper cache' => [
            'loader router static keeper cache',
            '/protected\s+static\s+\?\\\\fan\\\\core\\\\view\\\\keeper\\\\loader\\\\(?:json|text)\s+\$(?:json|text)\b/',
        ];
        yield 'meta maker static cache' => [
            'meta maker static cache',
            '/protected\s+static\s+array\s+\$metaCache\b/',
        ];
        yield 'spec image row static template cache' => [
            'spec image row static template cache',
            '/private\s+static\s+array\s+\$template\b/',
        ];
        yield 'database static parameter key table' => [
            'database static parameter key table',
            '/private\s+static\s+array\s+\$paramKeys\b/',
        ];
        yield 'compiled template loader static path cache' => [
            'compiled template loader static path cache',
            '/private\s+static\s+array\s+\$paths\b/',
        ];
        yield 'compiled template loader static registration flag' => [
            'compiled template loader static registration flag',
            '/private\s+static\s+bool\s+\$registered\b/',
        ];
        yield 'base data dynamic sub-data construction' => [
            'base data dynamic sub-data construction',
            '/new\s+(?:\$class|static)\s*\(\$value,\s*\$key,\s*\$this\)/',
        ];
    }

    #[DataProvider('rawCoreDiServiceIdProvider')]
    public function testCoreDiServiceIdsUseNamedConstants(string $label, string $pattern): void
    {
        $matches = [];
        foreach ($this->productionPhpFiles() as $file) {
            $relativePath = $this->relativePath($file);
            if (!str_starts_with($relativePath, 'core/di/')) {
                continue;
            }

            $source = file_get_contents($file);
            $this->assertIsString($source);
            if (preg_match($pattern, $source) === 1) {
                $matches[] = $relativePath;
            }
        }

        $this->assertSame([], $matches, $label . ' found in: ' . implode(', ', $matches));
    }

    public static function rawCoreDiServiceIdProvider(): iterable
    {
        yield 'raw factory service id' => [
            'raw factory service id',
            '/->\s*factory\s*\(\s*[\'"][^\'"]+[\'"]/',
        ];
        yield 'raw alias service id' => [
            'raw alias service id',
            '/->\s*alias\s*\(\s*[\'"][^\'"]+[\'"]/',
        ];
        yield 'raw container get service id' => [
            'raw container get service id',
            '/(?:\$container|\$this->container\(\)|\$this->context\(\)->container\(\))\s*->\s*get\s*\(\s*[\'"][^\'"]+[\'"]/',
        ];
    }

    public function testDynamicConstructionIsLimitedToExplicitFactoryBoundaries(): void
    {
        $allowedFiles = [
            'core/factory/service_factory_map.php' => true,
        ];
        $matches = [];

        foreach ($this->productionPhpFiles() as $file) {
            $relativePath = $this->relativePath($file);
            if (isset($allowedFiles[$relativePath])) {
                continue;
            }

            $source = file_get_contents($file);
            $this->assertIsString($source);
            if (preg_match('/new\s+\$[A-Za-z_][A-Za-z0-9_]*\s*\(/', $source) === 1) {
                $matches[] = $relativePath;
            }
        }

        $this->assertSame([], $matches, 'Dynamic construction outside explicit factory boundaries found in: ' . implode(', ', $matches));
    }

    public function testDynamicReflectionAndConfiguredServiceBoundariesAreInventoried(): void
    {
        $allowedFiles = array_fill_keys($this->dynamicReflectionBoundaryInventoryFiles(), true);
        $matches = [];

        foreach ($this->productionPhpFiles() as $file) {
            $relativePath = $this->relativePath($file);
            if (isset($allowedFiles[$relativePath])) {
                continue;
            }

            $source = file_get_contents($file);
            $this->assertIsString($source);
            $locations = array_filter(
                php_fan_ai_dynamic_boundary_locations_from_source($relativePath, $source),
                static fn(array $location): bool => in_array(
                    $location['pattern'] ?? null,
                    ['class_exists', 'ReflectionClass', 'configured_service_factory', 'configured_class_instantiator'],
                    true
                )
            );
            if ($locations !== []) {
                $matches[] = $relativePath;
            }
        }

        $this->assertSame([], $matches, 'Dynamic reflection/configured-service boundary outside inventory found in: ' . implode(', ', $matches));
    }

    public function testDynamicReflectionBoundaryInventoryHasAiMapCategories(): void
    {
        $categorized = [];
        foreach (php_fan_ai_dynamic_boundaries_map()['categories'] as $files) {
            foreach ($files as $file) {
                if (!str_contains($file, '*')) {
                    $categorized[$file] = true;
                }
            }
        }

        $missing = [];
        foreach ($this->dynamicReflectionBoundaryInventoryFiles() as $file) {
            if (!isset($categorized[$file])) {
                $missing[] = $file;
            }
        }

        $this->assertSame([], $missing, 'Dynamic boundary inventory files missing AI map category: ' . implode(', ', $missing));
    }

    public function testDiServiceCreatorsDoNotUseDirectMethodBodyProjectClassExistsChecks(): void
    {
        $matches = [];
        $files = glob(dirname(__DIR__, 2) . '/core/di/*_service_creator.php') ?: [];
        sort($files);

        foreach ($files as $file) {
            $source = file_get_contents($file);
            $this->assertIsString($source);
            if (str_contains($this->codeWithoutCommentsAndStrings($source), 'if (!class_exists($className))')) {
                $matches[] = $this->relativePath($file);
            }
        }

        $this->assertSame([], $matches, 'Direct method-body project class availability checks found in: ' . implode(', ', $matches));
    }

    public function testCoreCreatorUsesNamedProjectServiceAvailabilityBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_core_service_creator.php');

        $this->assertIsString($source);
        $this->assertSame(1, substr_count($source, 'class_exists($className)'));
        $this->assertStringNotContainsString('if (!class_exists($className))', $source);
        $this->assertStringContainsString('private \Closure $projectServiceClassExists;', $source);
        $this->assertStringContainsString('?callable $projectServiceClassExists = null', $source);
        $this->assertStringContainsString('private function projectServiceClassExists(string $className): bool', $source);
        $this->assertSame(10, substr_count($source, '$this->projectServiceClassExists($className)'));
    }

    public function testApplicationCreatorCommonDependenciesAreCompositionRootOnly(): void
    {
        require_once dirname(__DIR__, 2) . '/tools/ai_map.php';

        $root = dirname(__DIR__, 2);
        $map = php_fan_ai_build_map($root);
        $source = file_get_contents($root . '/core/di/application_creator_common_dependencies.php');
        $bootstrapSource = file_get_contents($root . '/core/di/application_creator_bootstrap_runtime_dependencies.php');
        $configCacheSource = file_get_contents($root . '/core/di/application_creator_config_cache_dependencies.php');
        $configSource = file_get_contents($root . '/core/di/application_creator_config_dependencies.php');
        $cacheFactorySource = file_get_contents($root . '/core/di/application_creator_cache_factory_dependencies.php');
        $coreCreatorSource = file_get_contents($root . '/core/di/application_core_service_creator.php');
        $navigationCreatorSource = file_get_contents($root . '/core/di/application_navigation_service_creator.php');

        $this->assertIsString($source);
        $this->assertIsString($bootstrapSource);
        $this->assertIsString($configCacheSource);
        $this->assertIsString($configSource);
        $this->assertIsString($cacheFactorySource);
        $this->assertIsString($coreCreatorSource);
        $this->assertIsString($navigationCreatorSource);
        $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file('core/di/application_creator_common_dependencies.php'));
        $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file('core/di/application_creator_bootstrap_runtime_dependencies.php'));
        $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file('core/di/application_creator_config_cache_dependencies.php'));
        $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file('core/di/application_creator_config_dependencies.php'));
        $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file('core/di/application_creator_cache_factory_dependencies.php'));
        $this->assertArrayHasKey('core/di/application_creator_bootstrap_runtime_dependencies.php', $map['dynamic_boundaries']['locations']);
        $this->assertArrayHasKey('core/di/application_creator_config_dependencies.php', $map['dynamic_boundaries']['locations']);
        $this->assertArrayHasKey('core/di/application_creator_cache_factory_dependencies.php', $map['dynamic_boundaries']['locations']);
        $this->assertStringContainsString('final class application_creator_common_dependencies', $source);
        $this->assertSame(0, substr_count($source, '$this->container->get('));
        $this->assertSame(1, substr_count($bootstrapSource, '$this->container->get('));
        $this->assertSame(0, substr_count($configCacheSource, '$this->container->get('));
        $this->assertSame(1, substr_count($configSource, '$this->container->get('));
        $this->assertSame(1, substr_count($cacheFactorySource, '$this->container->get('));
        $this->assertStringContainsString('commonDependencies(container_interface $container): application_creator_common_dependencies', $coreCreatorSource);
        $this->assertStringContainsString('commonDependencies(container_interface $container): application_creator_common_dependencies', $navigationCreatorSource);
    }

    public function testCoreServiceDependenciesAreCompositionRootOnly(): void
    {
        require_once dirname(__DIR__, 2) . '/tools/ai_map.php';

        $root = dirname(__DIR__, 2);
        $map = php_fan_ai_build_map($root);
        $source = file_get_contents($root . '/core/di/application_core_service_dependencies.php');
        $creatorSource = file_get_contents($root . '/core/di/application_core_service_creator.php');
        $groupCounts = [
            'core/di/application_core_project_application_context_dependencies.php' => 1,
            'core/di/application_core_project_dependencies.php' => 0,
            'core/di/application_core_project_error_dependencies.php' => 0,
            'core/di/application_core_project_error_context_dependencies.php' => 1,
            'core/di/application_core_project_error_file_storage_dependencies.php' => 1,
            'core/di/application_core_project_error_factory_service_dependencies.php' => 1,
            'core/di/application_core_project_error_log_writer_storage_dependencies.php' => 1,
            'core/di/application_core_project_error_service_dependencies.php' => 0,
            'core/di/application_core_project_error_storage_dependencies.php' => 0,
            'core/di/application_core_project_route_storage_dependencies.php' => 1,
            'core/di/application_core_project_reflection_class_factory_meta_dependencies.php' => 1,
            'core/di/application_core_project_reflection_meta_dependencies.php' => 0,
            'core/di/application_core_project_meta_file_storage_dependencies.php' => 1,
            'core/di/application_core_project_header_writer_response_loader_dependencies.php' => 1,
            'core/di/application_core_project_php_array_file_loader_response_loader_dependencies.php' => 1,
            'core/di/application_core_project_response_loader_dependencies.php' => 0,
            'core/di/application_core_project_storage_dependencies.php' => 0,
            'core/di/application_core_project_locale_context_dependencies.php' => 1,
            'core/di/application_core_project_tab_context_dependencies.php' => 0,
            'core/di/application_core_project_tab_dependencies.php' => 0,
            'core/di/application_core_project_tab_factory_service_context_dependencies.php' => 1,
            'core/di/application_core_project_tab_instance_service_context_dependencies.php' => 1,
            'core/di/application_core_project_tab_service_context_dependencies.php' => 0,
            'core/di/application_core_request_array_adducer_transform_helper_dependencies.php' => 1,
            'core/di/application_core_request_array_read_class_helper_dependencies.php' => 0,
            'core/di/application_core_request_array_transform_helper_dependencies.php' => 0,
            'core/di/application_core_request_array_value_reader_helper_dependencies.php' => 1,
            'core/di/application_core_request_class_name_resolver_helper_dependencies.php' => 1,
            'core/di/application_core_request_cookie_transport_factory_dependencies.php' => 1,
            'core/di/application_core_request_dependencies.php' => 0,
            'core/di/application_core_request_factory_dependencies.php' => 0,
            'core/di/application_core_request_helper_dependencies.php' => 0,
            'core/di/application_core_request_input_factory_dependencies.php' => 1,
            'core/di/application_core_request_json_transport_factory_dependencies.php' => 1,
            'core/di/application_core_request_matcher_factory_runtime_dependencies.php' => 1,
            'core/di/application_core_request_recursive_merger_transform_helper_dependencies.php' => 1,
            'core/di/application_core_request_request_factory_runtime_dependencies.php' => 1,
            'core/di/application_core_request_runtime_factory_dependencies.php' => 0,
            'core/di/application_core_request_transport_factory_dependencies.php' => 0,
            'core/di/application_core_user_current_identity_dependencies.php' => 1,
            'core/di/application_core_user_current_user_space_factory_session_space_dependencies.php' => 1,
            'core/di/application_core_user_data_factory_dependencies.php' => 0,
            'core/di/application_core_user_date_data_factory_dependencies.php' => 1,
            'core/di/application_core_user_entity_data_factory_dependencies.php' => 1,
            'core/di/application_core_user_identity_dependencies.php' => 0,
            'core/di/application_core_user_session_factory_session_space_dependencies.php' => 1,
            'core/di/application_core_user_session_space_dependencies.php' => 0,
            'core/di/application_core_user_session_dependencies.php' => 0,
        ];

        $this->assertIsString($source);
        $this->assertIsString($creatorSource);
        $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file('core/di/application_core_service_dependencies.php'));
        $this->assertStringContainsString('final class application_core_service_dependencies', $source);
        $this->assertSame(0, substr_count($source, '$this->container->get('));
        $this->assertStringContainsString('new application_core_request_dependencies($container)', $source);
        $this->assertStringContainsString('new application_core_user_session_dependencies($container)', $source);
        $this->assertStringContainsString('new application_core_project_dependencies($container)', $source);
        foreach ($groupCounts as $file => $count) {
            $groupSource = file_get_contents($root . '/' . $file);
            $this->assertIsString($groupSource);
            $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file($file));
            if ($count !== 0) {
                $this->assertArrayHasKey($file, $map['dynamic_boundaries']['locations']);
            }
            $this->assertSame($count, substr_count($groupSource, '$this->container->get('));
        }
        $this->assertStringContainsString('coreDependencies(container_interface $container): application_core_service_dependencies', $creatorSource);
        $this->assertStringContainsString('$coreDependencies = $this->coreDependencies($container);', $creatorSource);
        $this->assertStringNotContainsString('$container->get(service_id::REQUEST_INPUT)', $creatorSource);
        $this->assertStringNotContainsString('$container->get(service_id::ARRAY_ADDUCER)', $creatorSource);
        $this->assertStringNotContainsString('$container->get(service_id::REFLECTION_CLASS_FACTORY)', $creatorSource);
    }

    public function testUtilityServiceDependenciesAreCompositionRootOnly(): void
    {
        require_once dirname(__DIR__, 2) . '/tools/ai_map.php';

        $root = dirname(__DIR__, 2);
        $map = php_fan_ai_build_map($root);
        $source = file_get_contents($root . '/core/di/application_utility_service_dependencies.php');
        $creatorSource = file_get_contents($root . '/core/di/application_utility_service_creator.php');
        $groupFiles = [
            'core/di/application_utility_array_value_reader_helper_error_core_dependencies.php' => 1,
            'core/di/application_utility_cache_factory_config_cache_core_dependencies.php' => 1,
            'core/di/application_utility_config_cache_core_dependencies.php' => 0,
            'core/di/application_utility_config_config_cache_core_dependencies.php' => 1,
            'core/di/application_utility_core_dependencies.php' => 0,
            'core/di/application_utility_class_storage_dependencies.php' => 1,
            'core/di/application_utility_error_factory_helper_error_core_dependencies.php' => 1,
            'core/di/application_utility_file_storage_dependencies.php' => 0,
            'core/di/application_utility_helper_error_core_dependencies.php' => 0,
            'core/di/application_utility_image_canvas_operations_canvas_output_dependencies.php' => 1,
            'core/di/application_utility_image_canvas_output_dependencies.php' => 0,
            'core/di/application_utility_image_dependencies.php' => 0,
            'core/di/application_utility_image_metadata_reader_metadata_resource_dependencies.php' => 1,
            'core/di/application_utility_image_metadata_resource_dependencies.php' => 0,
            'core/di/application_utility_image_resource_factory_metadata_resource_dependencies.php' => 1,
            'core/di/application_utility_image_output_writer_canvas_output_dependencies.php' => 1,
            'core/di/application_utility_image_source_file_storage_image_storage_dependencies.php' => 1,
            'core/di/application_utility_image_storage_dependencies.php' => 0,
            'core/di/application_utility_obfuscator_file_storage_image_storage_dependencies.php' => 1,
            'core/di/application_utility_php_array_file_loader_file_storage_dependencies.php' => 1,
            'core/di/application_utility_bootstrap_runtime_runtime_core_dependencies.php' => 1,
            'core/di/application_utility_runtime_core_dependencies.php' => 0,
            'core/di/application_utility_php_runtime_settings_runtime_core_dependencies.php' => 1,
            'core/di/application_utility_soap_wsdl_file_storage_file_storage_dependencies.php' => 1,
            'core/di/application_utility_storage_dependencies.php' => 0,
        ];

        $this->assertIsString($source);
        $this->assertIsString($creatorSource);
        $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file('core/di/application_utility_service_dependencies.php'));
        $this->assertStringContainsString('final class application_utility_service_dependencies', $source);
        $this->assertSame(0, substr_count($source, '$this->container->get('));
        foreach ($groupFiles as $file => $expectedLookups) {
            $groupSource = file_get_contents($root . '/' . $file);
            $this->assertIsString($groupSource);
            $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file($file));
            if ($expectedLookups !== 0) {
                $this->assertArrayHasKey($file, $map['dynamic_boundaries']['locations']);
            }
            $this->assertSame($expectedLookups, substr_count($groupSource, '$this->container->get('));
        }
        $this->assertStringContainsString('utilityDependencies(container_interface $container): application_utility_service_dependencies', $creatorSource);
        $this->assertStringContainsString('$utilityDependencies = $this->utilityDependencies($container);', $creatorSource);
        $this->assertStringNotContainsString('$container->get(service_id::BOOTSTRAP_RUNTIME)', $creatorSource);
        $this->assertStringNotContainsString('$container->get(service_id::CONFIG)', $creatorSource);
        $this->assertStringNotContainsString('$container->get(service_id::ARRAY_VALUE_READER)', $creatorSource);
    }

    public function testClientServiceDependenciesAreCompositionRootOnly(): void
    {
        require_once dirname(__DIR__, 2) . '/tools/ai_map.php';

        $root = dirname(__DIR__, 2);
        $map = php_fan_ai_build_map($root);
        $source = file_get_contents($root . '/core/di/application_client_service_dependencies.php');
        $creatorSource = file_get_contents($root . '/core/di/application_client_service_creator.php');
        $groupFiles = [
            'core/di/application_client_array_adducer_payload_dependencies.php' => 1,
            'core/di/application_client_array_payload_dependencies.php' => 0,
            'core/di/application_client_array_value_reader_payload_dependencies.php' => 1,
            'core/di/application_client_bootstrap_runtime_dependencies.php' => 1,
            'core/di/application_client_cache_runtime_dependencies.php' => 1,
            'core/di/application_client_config_cache_runtime_dependencies.php' => 0,
            'core/di/application_client_config_runtime_dependencies.php' => 1,
            'core/di/application_client_curl_adapter_transport_dependencies.php' => 1,
            'core/di/application_client_curl_factory_transport_dependencies.php' => 1,
            'core/di/application_client_curl_transport_dependencies.php' => 0,
            'core/di/application_client_error_transport_dependencies.php' => 1,
            'core/di/application_client_payload_dependencies.php' => 0,
            'core/di/application_client_request_payload_dependencies.php' => 1,
            'core/di/application_client_runtime_dependencies.php' => 0,
            'core/di/application_client_cookie_writer_payload_dependencies.php' => 1,
            'core/di/application_client_serialization_payload_dependencies.php' => 0,
            'core/di/application_client_serialization_transport_dependencies.php' => 1,
            'core/di/application_client_serializer_operations_payload_dependencies.php' => 1,
            'core/di/application_client_transport_dependencies.php' => 0,
        ];

        $this->assertIsString($source);
        $this->assertIsString($creatorSource);
        $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file('core/di/application_client_service_dependencies.php'));
        $this->assertStringContainsString('final class application_client_service_dependencies', $source);
        $this->assertSame(0, substr_count($source, '$this->container->get('));
        foreach ($groupFiles as $file => $expectedLookups) {
            $groupSource = file_get_contents($root . '/' . $file);
            $this->assertIsString($groupSource);
            $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file($file));
            if ($expectedLookups !== 0) {
                $this->assertArrayHasKey($file, $map['dynamic_boundaries']['locations']);
            }
            $this->assertSame($expectedLookups, substr_count($groupSource, '$this->container->get('));
        }
        $this->assertStringContainsString('clientDependencies(container_interface $container): application_client_service_dependencies', $creatorSource);
        $this->assertStringContainsString('$clientDependencies = $this->clientDependencies($container);', $creatorSource);
        $this->assertStringNotContainsString('$container->get(service_id::BOOTSTRAP_RUNTIME)', $creatorSource);
        $this->assertStringNotContainsString('$container->get(service_id::SERIALIZER_OPERATIONS)', $creatorSource);
        $this->assertStringNotContainsString('$container->get(service_id::COOKIE_WRITER)', $creatorSource);
    }

    public function testBlockDependencyGroupsAreCompositionRootOnly(): void
    {
        require_once dirname(__DIR__, 2) . '/tools/ai_map.php';

        $root = dirname(__DIR__, 2);
        $map = php_fan_ai_build_map($root);
        $groupFiles = [
            'core/block/base_dependency_application_data_factory_group.php' => 0,
            'core/block/base_dependency_application_factory_group.php' => 0,
            'core/block/base_dependency_application_runtime_factory_group.php' => 0,
            'core/block/base_dependency_application_service_runtime_factory_group.php' => 1,
            'core/block/base_dependency_application_support_factory_group.php' => 0,
            'core/block/base_dependency_array_adducer_helper_group.php' => 1,
            'core/block/base_dependency_array_helper_group.php' => 0,
            'core/block/base_dependency_array_like_checker_helper_group.php' => 1,
            'core/block/base_dependency_array_read_helper_group.php' => 0,
            'core/block/base_dependency_array_transform_helper_group.php' => 0,
            'core/block/base_dependency_array_value_reader_helper_group.php' => 1,
            'core/block/base_dependency_block_exception_factory_group.php' => 1,
            'core/block/base_dependency_block_factory_group.php' => 1,
            'core/block/base_dependency_block_file_storage_block_group.php' => 1,
            'core/block/base_dependency_block_file_storage_group.php' => 0,
            'core/block/base_dependency_block_file_storage_meta_group.php' => 1,
            'core/block/base_dependency_block_factory_context_group.php' => 0,
            'core/block/base_dependency_class_helper_group.php' => 1,
            'core/block/base_dependency_config_application_runtime_factory_group.php' => 1,
            'core/block/base_dependency_context_group.php' => 0,
            'core/block/base_dependency_data_core_factory_group.php' => 0,
            'core/block/base_dependency_data_factory_group.php' => 0,
            'core/block/base_dependency_data_loader_factory_group.php' => 0,
            'core/block/base_dependency_data_loader_service_factory_group.php' => 1,
            'core/block/base_dependency_date_application_support_factory_group.php' => 1,
            'core/block/base_dependency_database_application_data_factory_group.php' => 1,
            'core/block/base_dependency_entity_data_core_factory_group.php' => 1,
            'core/block/base_dependency_error_log_media_error_helper_group.php' => 1,
            'core/block/base_dependency_error_application_support_factory_group.php' => 1,
            'core/block/base_dependency_factory_group.php' => 0,
            'core/block/base_dependency_helper_group.php' => 0,
            'core/block/base_dependency_image_metadata_media_error_helper_group.php' => 1,
            'core/block/base_dependency_image_modify_media_core_factory_group.php' => 1,
            'core/block/base_dependency_json_data_core_factory_group.php' => 1,
            'core/block/base_dependency_locale_factory_context_group.php' => 1,
            'core/block/base_dependency_matcher_factory_context_group.php' => 1,
            'core/block/base_dependency_media_core_factory_group.php' => 0,
            'core/block/base_dependency_media_error_helper_group.php' => 0,
            'core/block/base_dependency_media_transfer_factory_group.php' => 1,
            'core/block/base_dependency_meta_loader_group.php' => 0,
            'core/block/base_dependency_meta_maker_factory_group.php' => 1,
            'core/block/base_dependency_meta_maker_group.php' => 0,
            'core/block/base_dependency_meta_maker_state_group.php' => 1,
            'core/block/base_dependency_meta_row_factory_group.php' => 1,
            'core/block/base_dependency_meta_row_loader_group.php' => 0,
            'core/block/base_dependency_media_factory_group.php' => 0,
            'core/block/base_dependency_navigation_context_group.php' => 0,
            'core/block/base_dependency_obfuscator_media_core_factory_group.php' => 1,
            'core/block/base_dependency_pager_data_loader_factory_group.php' => 1,
            'core/block/base_dependency_php_array_file_loader_group.php' => 1,
            'core/block/base_dependency_project_file_storage_group.php' => 0,
            'core/block/base_dependency_project_tool_file_storage_group.php' => 1,
            'core/block/base_dependency_reflector_context_group.php' => 1,
            'core/block/base_dependency_request_factory_context_group.php' => 1,
            'core/block/base_dependency_request_context_group.php' => 0,
            'core/block/base_dependency_request_input_context_group.php' => 1,
            'core/block/base_dependency_request_role_context_group.php' => 0,
            'core/block/base_dependency_role_factory_context_group.php' => 1,
            'core/block/base_dependency_route_locale_context_group.php' => 0,
            'core/block/base_dependency_root_html_file_storage_group.php' => 1,
            'core/block/base_dependency_recursive_merger_helper_group.php' => 1,
            'core/block/base_dependency_runtime_context_group.php' => 0,
            'core/block/base_dependency_session_context_group.php' => 1,
            'core/block/base_dependency_bootstrap_runtime_context_group.php' => 1,
            'core/block/base_dependency_storage_group.php' => 0,
            'core/block/base_dependency_tab_runtime_context_group.php' => 0,
            'core/block/base_dependency_tab_service_runtime_context_group.php' => 1,
            'core/block/base_dependency_upload_limit_storage_group.php' => 1,
            'core/block/base_dependency_user_application_data_factory_group.php' => 1,
            'core/block/base_dependency_view_factory_loader_group.php' => 0,
            'core/block/base_dependency_view_loader_group.php' => 0,
            'core/block/base_dependency_view_parser_exception_factory_loader_group.php' => 1,
            'core/block/base_dependency_view_router_factory_loader_group.php' => 1,
            'core/block/base_dependency_view_state_loader_group.php' => 1,
            'core/block/base_dependency_view_meta_group.php' => 0,
        ];
        $resolverSource = file_get_contents($root . '/core/block/base_dependency_resolver.php');

        $this->assertIsString($resolverSource);
        $this->assertSame(0, substr_count($resolverSource, '->get('));
        foreach ($groupFiles as $file => $expectedLookups) {
            if (is_int($file)) {
                $file = $expectedLookups;
                $expectedLookups = null;
            }
            $source = file_get_contents($root . '/' . $file);
            $this->assertIsString($source);
            $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file($file));
            if ($expectedLookups !== 0) {
                $this->assertArrayHasKey($file, $map['dynamic_boundaries']['locations']);
            }
            $this->assertStringContainsString('final class ' . basename($file, '.php'), $source);
            if ($expectedLookups === null) {
                $this->assertStringContainsString('$this->container->get(', $source);
            } else {
                $this->assertSame($expectedLookups, substr_count($source, '$this->container->get('));
            }
        }
    }

    public function testNavigationTabDependenciesAreCompositionRootOnly(): void
    {
        require_once dirname(__DIR__, 2) . '/tools/ai_map.php';

        $root = dirname(__DIR__, 2);
        $map = php_fan_ai_build_map($root);
        $source = file_get_contents($root . '/core/di/application_navigation_tab_dependencies.php');
        $navigationCreatorSource = file_get_contents($root . '/core/di/application_navigation_service_creator.php');
        $coreSource = file_get_contents($root . '/core/di/application_navigation_tab_core_dependencies.php');
        $groupFiles = [
            'core/di/application_navigation_tab_alias_asset_dependencies.php' => 1,
            'core/di/application_navigation_tab_application_factory_application_debug_dependencies.php' => 1,
            'core/di/application_navigation_tab_block_file_storage_dependencies.php' => 1,
            'core/di/application_navigation_tab_block_meta_file_storage_dependencies.php' => 0,
            'core/di/application_navigation_tab_config_factory_config_header_dependencies.php' => 1,
            'core/di/application_navigation_tab_context_dependencies.php' => 0,
            'core/di/application_navigation_tab_cookie_payload_dependencies.php' => 1,
            'core/di/application_navigation_tab_core_dependencies.php' => 0,
            'core/di/application_navigation_tab_data_cookie_payload_dependencies.php' => 0,
            'core/di/application_navigation_tab_data_loader_payload_dependencies.php' => 1,
            'core/di/application_navigation_tab_data_model_factory_dependencies.php' => 0,
            'core/di/application_navigation_tab_date_factory_user_time_model_factory_dependencies.php' => 1,
            'core/di/application_navigation_tab_debug_factory_application_debug_dependencies.php' => 1,
            'core/di/application_navigation_tab_entity_model_factory_dependencies.php' => 1,
            'core/di/application_navigation_tab_error_factory_error_reflector_dependencies.php' => 1,
            'core/di/application_navigation_tab_error_log_writer_asset_dependencies.php' => 1,
            'core/di/application_navigation_tab_file_storage_dependencies.php' => 0,
            'core/di/application_navigation_tab_header_factory_config_header_dependencies.php' => 1,
            'core/di/application_navigation_tab_image_metadata_reader_asset_dependencies.php' => 1,
            'core/di/application_navigation_tab_image_modify_model_factory_dependencies.php' => 1,
            'core/di/application_navigation_tab_input_context_dependencies.php' => 1,
            'core/di/application_navigation_tab_json_payload_dependencies.php' => 1,
            'core/di/application_navigation_tab_locale_context_dependencies.php' => 1,
            'core/di/application_navigation_tab_locale_session_context_dependencies.php' => 0,
            'core/di/application_navigation_tab_matcher_routing_context_dependencies.php' => 1,
            'core/di/application_navigation_tab_media_error_asset_dependencies.php' => 0,
            'core/di/application_navigation_tab_media_model_factory_dependencies.php' => 0,
            'core/di/application_navigation_tab_meta_file_storage_dependencies.php' => 1,
            'core/di/application_navigation_tab_model_factory_dependencies.php' => 0,
            'core/di/application_navigation_tab_obfuscator_model_factory_dependencies.php' => 1,
            'core/di/application_navigation_tab_pager_model_factory_dependencies.php' => 1,
            'core/di/application_navigation_tab_project_tool_storage_dependencies.php' => 1,
            'core/di/application_navigation_tab_reflector_factory_error_reflector_dependencies.php' => 1,
            'core/di/application_navigation_tab_request_routing_context_dependencies.php' => 1,
            'core/di/application_navigation_tab_routing_context_dependencies.php' => 0,
            'core/di/application_navigation_tab_root_html_file_storage_dependencies.php' => 1,
            'core/di/application_navigation_tab_role_factory_role_transfer_dependencies.php' => 1,
            'core/di/application_navigation_tab_session_factory_context_dependencies.php' => 1,
            'core/di/application_navigation_tab_service_factory_application_debug_dependencies.php' => 0,
            'core/di/application_navigation_tab_service_factory_application_dependencies.php' => 0,
            'core/di/application_navigation_tab_service_factory_config_header_dependencies.php' => 0,
            'core/di/application_navigation_tab_service_factory_dependencies.php' => 0,
            'core/di/application_navigation_tab_service_factory_error_reflector_dependencies.php' => 0,
            'core/di/application_navigation_tab_service_factory_payload_dependencies.php' => 0,
            'core/di/application_navigation_tab_service_factory_role_transfer_dependencies.php' => 0,
            'core/di/application_navigation_tab_service_factory_runtime_dependencies.php' => 0,
            'core/di/application_navigation_tab_storage_dependencies.php' => 0,
            'core/di/application_navigation_tab_array_adducer_transform_dependencies.php' => 1,
            'core/di/application_navigation_tab_array_like_checker_read_check_dependencies.php' => 1,
            'core/di/application_navigation_tab_array_value_reader_read_check_dependencies.php' => 1,
            'core/di/application_navigation_tab_block_exception_factory_exception_meta_dependencies.php' => 1,
            'core/di/application_navigation_tab_block_factory_instance_block_factory_dependencies.php' => 1,
            'core/di/application_navigation_tab_meta_row_factory_exception_meta_dependencies.php' => 1,
            'core/di/application_navigation_tab_recursive_merger_transform_dependencies.php' => 1,
            'core/di/application_navigation_tab_support_array_helper_dependencies.php' => 0,
            'core/di/application_navigation_tab_support_array_read_check_dependencies.php' => 0,
            'core/di/application_navigation_tab_support_array_transform_dependencies.php' => 0,
            'core/di/application_navigation_tab_support_asset_dependencies.php' => 0,
            'core/di/application_navigation_tab_support_block_dependencies.php' => 0,
            'core/di/application_navigation_tab_support_block_exception_meta_dependencies.php' => 0,
            'core/di/application_navigation_tab_support_block_factory_dependencies.php' => 0,
            'core/di/application_navigation_tab_class_name_resolver_class_helper_dependencies.php' => 1,
            'core/di/application_navigation_tab_short_class_name_resolver_class_helper_dependencies.php' => 1,
            'core/di/application_navigation_tab_support_class_helper_dependencies.php' => 0,
            'core/di/application_navigation_tab_support_dependencies.php' => 0,
            'core/di/application_navigation_tab_support_helper_dependencies.php' => 0,
            'core/di/application_navigation_tab_support_loader_dependencies.php' => 1,
            'core/di/application_navigation_tab_tab_state_block_factory_dependencies.php' => 1,
            'core/di/application_navigation_tab_transfer_factory_role_transfer_dependencies.php' => 1,
            'core/di/application_navigation_tab_upload_limit_storage_dependencies.php' => 1,
            'core/di/application_navigation_tab_user_factory_user_time_model_factory_dependencies.php' => 1,
            'core/di/application_navigation_tab_user_time_model_factory_dependencies.php' => 0,
        ];

        $this->assertIsString($source);
        $this->assertIsString($navigationCreatorSource);
        $this->assertIsString($coreSource);
        $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file('core/di/application_navigation_tab_dependencies.php'));
        $this->assertStringContainsString('final class application_navigation_tab_dependencies', $source);
        $this->assertSame(0, substr_count($source, '$this->container->get('));
        $this->assertSame(0, substr_count($coreSource, '$this->container->get('));
        foreach ($groupFiles as $file => $expectedLookups) {
            if (is_int($file)) {
                $file = $expectedLookups;
                $expectedLookups = null;
            }
            $groupSource = file_get_contents($root . '/' . $file);
            $this->assertIsString($groupSource);
            $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file($file));
            if ($expectedLookups !== 0) {
                $this->assertArrayHasKey($file, $map['dynamic_boundaries']['locations']);
            }
            if ($expectedLookups === null) {
                $this->assertStringContainsString('$this->container->get(', $groupSource);
            } else {
                $this->assertSame($expectedLookups, substr_count($groupSource, '$this->container->get('));
            }
        }
        $this->assertStringContainsString('tabDependencies(container_interface $container): application_navigation_tab_dependencies', $navigationCreatorSource);
        $this->assertStringContainsString('$tabDependencies->matcher()', $navigationCreatorSource);
        $this->assertStringContainsString('$tabDependencies->uploadSizeLimitProvider()', $navigationCreatorSource);
    }

    public function testSessionServiceDependenciesAreCompositionRootOnly(): void
    {
        require_once dirname(__DIR__, 2) . '/tools/ai_map.php';

        $root = dirname(__DIR__, 2);
        $map = php_fan_ai_build_map($root);
        $source = file_get_contents($root . '/core/di/application_session_service_dependencies.php');
        $creatorSource = file_get_contents($root . '/core/di/application_session_service_creator.php');
        $groupFiles = [
            'core/di/application_session_application_context_dependencies.php' => 0,
            'core/di/application_session_application_instance_application_context_dependencies.php' => 1,
            'core/di/application_session_array_runtime_dependencies.php' => 1,
            'core/di/application_session_bootstrap_bootstrap_runtime_dependencies.php' => 1,
            'core/di/application_session_bootstrap_runtime_dependencies.php' => 0,
            'core/di/application_session_cache_factory_dependencies.php' => 1,
            'core/di/application_session_config_application_context_dependencies.php' => 1,
            'core/di/application_session_context_dependencies.php' => 0,
            'core/di/application_session_cookie_factory_state_factory_dependencies.php' => 1,
            'core/di/application_session_date_factory_support_factory_dependencies.php' => 1,
            'core/di/application_session_error_factory_support_factory_dependencies.php' => 1,
            'core/di/application_session_factory_dependencies.php' => 0,
            'core/di/application_session_header_context_dependencies.php' => 1,
            'core/di/application_session_native_runtime_dependencies.php' => 0,
            'core/di/application_session_native_session_native_runtime_dependencies.php' => 1,
            'core/di/application_session_pear_http_session_loader_native_runtime_dependencies.php' => 1,
            'core/di/application_session_php_runtime_settings_bootstrap_runtime_dependencies.php' => 1,
            'core/di/application_session_request_context_dependencies.php' => 0,
            'core/di/application_session_request_input_request_context_dependencies.php' => 1,
            'core/di/application_session_request_instance_request_context_dependencies.php' => 1,
            'core/di/application_session_runtime_dependencies.php' => 0,
            'core/di/application_session_session_factory_state_factory_dependencies.php' => 1,
            'core/di/application_session_state_factory_dependencies.php' => 0,
            'core/di/application_session_support_factory_dependencies.php' => 0,
        ];

        $this->assertIsString($source);
        $this->assertIsString($creatorSource);
        $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file('core/di/application_session_service_dependencies.php'));
        $this->assertStringContainsString('final class application_session_service_dependencies', $source);
        $this->assertSame(0, substr_count($source, '$this->container->get('));
        foreach ($groupFiles as $file => $expectedLookups) {
            $groupSource = file_get_contents($root . '/' . $file);
            $this->assertIsString($groupSource);
            $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file($file));
            if ($expectedLookups !== 0) {
                $this->assertArrayHasKey($file, $map['dynamic_boundaries']['locations']);
            }
            $this->assertSame($expectedLookups, substr_count($groupSource, '$this->container->get('));
        }
        $this->assertSame(0, substr_count($creatorSource, '$container->get('));
        $this->assertStringContainsString('sessionDependencies(container_interface $container): application_session_service_dependencies', $creatorSource);
        $this->assertStringContainsString('$sessionDependencies = $this->sessionDependencies($container);', $creatorSource);
        $this->assertStringContainsString('$config = $sessionDependencies->config();', $creatorSource);
        $this->assertStringContainsString('$sessionDependencies->arrayValueReader()', $creatorSource);
    }

    public function testUserServiceDependenciesAreCompositionRootOnly(): void
    {
        require_once dirname(__DIR__, 2) . '/tools/ai_map.php';

        $root = dirname(__DIR__, 2);
        $map = php_fan_ai_build_map($root);
        $source = file_get_contents($root . '/core/di/application_user_service_dependencies.php');
        $userCreatorSource = file_get_contents($root . '/core/di/application_user_service_creator.php');
        $groupFiles = [
            'core/di/application_user_application_factory_dependencies.php' => 0,
            'core/di/application_user_application_factory_application_request_input_factory_dependencies.php' => 1,
            'core/di/application_user_application_request_input_factory_dependencies.php' => 0,
            'core/di/application_user_application_instance_application_request_context_dependencies.php' => 1,
            'core/di/application_user_application_request_context_dependencies.php' => 0,
            'core/di/application_user_array_runtime_dependencies.php' => 1,
            'core/di/application_user_bootstrap_cache_runtime_dependencies.php' => 0,
            'core/di/application_user_bootstrap_runtime_bootstrap_cache_runtime_dependencies.php' => 1,
            'core/di/application_user_cache_factory_bootstrap_cache_runtime_dependencies.php' => 1,
            'core/di/application_user_config_factory_dependencies.php' => 1,
            'core/di/application_user_config_serialization_config_context_dependencies.php' => 1,
            'core/di/application_user_context_dependencies.php' => 0,
            'core/di/application_user_current_user_factory_identity_factory_dependencies.php' => 1,
            'core/di/application_user_error500_exception_factory_exception_session_context_dependencies.php' => 1,
            'core/di/application_user_exception_session_context_dependencies.php' => 0,
            'core/di/application_user_factory_dependencies.php' => 0,
            'core/di/application_user_identity_factory_dependencies.php' => 0,
            'core/di/application_user_request_input_factory_application_request_input_factory_dependencies.php' => 1,
            'core/di/application_user_request_application_request_context_dependencies.php' => 1,
            'core/di/application_user_runtime_dependencies.php' => 0,
            'core/di/application_user_serializer_operations_serialization_config_context_dependencies.php' => 1,
            'core/di/application_user_serialization_config_context_dependencies.php' => 0,
            'core/di/application_user_support_factory_dependencies.php' => 0,
            'core/di/application_user_error_factory_support_factory_dependencies.php' => 1,
            'core/di/application_user_entity_factory_support_factory_dependencies.php' => 1,
            'core/di/application_user_session_exception_session_context_dependencies.php' => 1,
            'core/di/application_user_session_factory_identity_factory_dependencies.php' => 1,
        ];

        $this->assertIsString($source);
        $this->assertIsString($userCreatorSource);
        $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file('core/di/application_user_service_dependencies.php'));
        $this->assertStringContainsString('final class application_user_service_dependencies', $source);
        $this->assertSame(0, substr_count($source, '$this->container->get('));
        foreach ($groupFiles as $file => $expectedLookups) {
            $groupSource = file_get_contents($root . '/' . $file);
            $this->assertIsString($groupSource);
            $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file($file));
            if ($expectedLookups !== 0) {
                $this->assertArrayHasKey($file, $map['dynamic_boundaries']['locations']);
            }
            $this->assertSame($expectedLookups, substr_count($groupSource, '$this->container->get('));
        }
        $this->assertStringContainsString('userDependencies(container_interface $container): application_user_service_dependencies', $userCreatorSource);
        $this->assertStringContainsString('$userDependencies->serializerOperations()', $userCreatorSource);
        $this->assertStringContainsString('$userDependencies->arrayAdducer()', $userCreatorSource);
    }

    public function testInfrastructureConfigCacheDependenciesAreCompositionRootOnly(): void
    {
        require_once dirname(__DIR__, 2) . '/tools/ai_map.php';

        $root = dirname(__DIR__, 2);
        $map = php_fan_ai_build_map($root);
        $source = file_get_contents($root . '/core/di/application_infrastructure_config_cache_dependencies.php');
        $creatorSource = file_get_contents($root . '/core/di/application_infrastructure_service_creator.php');
        $groupFiles = [
            'core/di/application_infrastructure_config_cache_cache_factory_dependencies.php' => 0,
            'core/di/application_infrastructure_config_cache_bootstrap_runtime_support_dependencies.php' => 1,
            'core/di/application_infrastructure_config_cache_cache_source_file_metadata_storage_dependencies.php' => 1,
            'core/di/application_infrastructure_config_cache_class_helper_dependencies.php' => 1,
            'core/di/application_infrastructure_config_cache_config_cache_factory_dependencies.php' => 1,
            'core/di/application_infrastructure_config_cache_config_factory_dependencies.php' => 0,
            'core/di/application_infrastructure_config_cache_config_instance_dependencies.php' => 1,
            'core/di/application_infrastructure_config_cache_config_source_file_storage_dependencies.php' => 1,
            'core/di/application_infrastructure_config_cache_core_fatal_exception_factory_dependencies.php' => 1,
            'core/di/application_infrastructure_config_cache_error_factory_runtime_support_dependencies.php' => 1,
            'core/di/application_infrastructure_config_cache_exception_dependencies.php' => 0,
            'core/di/application_infrastructure_config_cache_error500_exception_factory_dependencies.php' => 1,
            'core/di/application_infrastructure_config_cache_exception_factory_dependencies.php' => 0,
            'core/di/application_infrastructure_config_cache_factory_dependencies.php' => 0,
            'core/di/application_infrastructure_config_cache_header_writer_dependencies.php' => 1,
            'core/di/application_infrastructure_config_cache_loader_serializer_dependencies.php' => 0,
            'core/di/application_infrastructure_config_cache_php_array_file_loader_dependencies.php' => 1,
            'core/di/application_infrastructure_config_cache_request_header_dependencies.php' => 0,
            'core/di/application_infrastructure_config_cache_request_input_dependencies.php' => 1,
            'core/di/application_infrastructure_config_cache_runtime_support_dependencies.php' => 0,
            'core/di/application_infrastructure_config_cache_serializer_operations_dependencies.php' => 1,
            'core/di/application_infrastructure_config_cache_storage_dependencies.php' => 0,
            'core/di/application_infrastructure_config_cache_support_dependencies.php' => 0,
            'core/di/application_infrastructure_config_cache_type_cache_factory_dependencies.php' => 1,
            'core/di/application_infrastructure_config_cache_typed_config_factory_dependencies.php' => 1,
            'core/di/application_infrastructure_runtime_bootstrap_runtime_error_dependencies.php' => 1,
            'core/di/application_infrastructure_runtime_cache_factory_dependencies.php' => 1,
            'core/di/application_infrastructure_runtime_config_cache_dependencies.php' => 0,
            'core/di/application_infrastructure_runtime_config_dependencies.php' => 1,
            'core/di/application_infrastructure_runtime_dependencies.php' => 0,
            'core/di/application_infrastructure_runtime_error_dependencies.php' => 0,
            'core/di/application_infrastructure_runtime_error_factory_dependencies.php' => 1,
            'core/di/application_infrastructure_service_dependencies.php' => 0,
            'core/di/application_infrastructure_storage_dependencies.php' => 1,
        ];

        $this->assertIsString($source);
        $this->assertIsString($creatorSource);
        $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file('core/di/application_infrastructure_config_cache_dependencies.php'));
        $this->assertStringContainsString('final class application_infrastructure_config_cache_dependencies', $source);
        $this->assertSame(0, substr_count($source, '$this->container->get('));
        foreach ($groupFiles as $file => $expectedLookups) {
            $groupSource = file_get_contents($root . '/' . $file);
            $this->assertIsString($groupSource);
            $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file($file));
            if ($expectedLookups !== 0) {
                $this->assertArrayHasKey($file, $map['dynamic_boundaries']['locations']);
            }
            $this->assertSame($expectedLookups, substr_count($groupSource, '$this->container->get('));
        }
        $this->assertStringContainsString('configCacheDependencies(container_interface $container): application_infrastructure_config_cache_dependencies', $creatorSource);
        $this->assertStringContainsString('serviceDependencies(container_interface $container): application_infrastructure_service_dependencies', $creatorSource);
        $this->assertStringContainsString('$infrastructureDependencies->fileSystemStorage()', $creatorSource);
        $this->assertStringContainsString('$infrastructureDependencies->config()', $creatorSource);
        $this->assertStringContainsString('$infrastructureDependencies->cacheFactory()', $creatorSource);
    }

    public function testTerminalCompositionLeavesArePinnedAsAtomicInventory(): void
    {
        require_once dirname(__DIR__, 2) . '/tools/ai_map.php';

        $root = dirname(__DIR__, 2);
        $map = php_fan_ai_build_map($root);
        $terminalLeaves = $map['dynamic_boundaries']['terminal_composition_leaves'];
        $examples = [
            'core/block/base_dependency_application_service_runtime_factory_group.php',
            'core/block/base_dependency_array_adducer_helper_group.php',
            'core/block/base_dependency_array_like_checker_helper_group.php',
            'core/block/base_dependency_array_value_reader_helper_group.php',
            'core/block/base_dependency_block_exception_factory_group.php',
        ];

        $this->assertSame([], $map['dynamic_boundaries']['actionable_composition_roots']);

        foreach ($examples as $file) {
            $this->assertContains($file, $terminalLeaves);
            $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file($file));
            $this->assertSame(1, count($map['dynamic_boundaries']['locations'][$file] ?? []));
            $this->assertSame(
                'terminal_composition_leaf',
                php_fan_ai_dynamic_boundary_summary_for_file($map, $file)['composition_leaf_kind']
            );
        }
    }

    public function testInfrastructureCreatorUsesNamedProjectServiceAvailabilityBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_infrastructure_service_creator.php');

        $this->assertIsString($source);
        $this->assertSame(1, substr_count($source, 'class_exists($className)'));
        $this->assertStringNotContainsString('if (!class_exists($className))', $source);
        $this->assertStringContainsString('private \Closure $projectServiceClassExists;', $source);
        $this->assertStringContainsString('?callable $projectServiceClassExists = null', $source);
        $this->assertStringContainsString('private function projectServiceClassExists(string $className): bool', $source);
        $this->assertGreaterThanOrEqual(5, substr_count($source, '$this->projectServiceClassExists($className)'));
    }

    public function testContentServiceDependenciesAreCompositionRootOnly(): void
    {
        require_once dirname(__DIR__, 2) . '/tools/ai_map.php';

        $root = dirname(__DIR__, 2);
        $map = php_fan_ai_build_map($root);
        $source = file_get_contents($root . '/core/di/application_content_service_dependencies.php');
        $creatorSource = file_get_contents($root . '/core/di/application_content_service_creator.php');
        $groupFiles = [
            'core/di/application_content_bootstrap_runtime_dependencies.php' => 1,
            'core/di/application_content_block_context_dependencies.php' => 1,
            'core/di/application_content_cache_runtime_dependencies.php' => 1,
            'core/di/application_content_config_cache_runtime_dependencies.php' => 0,
            'core/di/application_content_config_runtime_dependencies.php' => 1,
            'core/di/application_content_context_dependencies.php' => 0,
            'core/di/application_content_error_block_context_dependencies.php' => 0,
            'core/di/application_content_error_context_dependencies.php' => 1,
            'core/di/application_content_locale_context_dependencies.php' => 1,
            'core/di/application_content_localization_context_dependencies.php' => 0,
            'core/di/application_content_matcher_context_dependencies.php' => 1,
            'core/di/application_content_request_input_context_dependencies.php' => 1,
            'core/di/application_content_request_matcher_context_dependencies.php' => 0,
            'core/di/application_content_runtime_dependencies.php' => 0,
            'core/di/application_content_php_array_file_loader_storage_dependencies.php' => 1,
            'core/di/application_content_storage_dependencies.php' => 0,
            'core/di/application_content_tab_factory_context_dependencies.php' => 1,
            'core/di/application_content_translation_file_storage_dependencies.php' => 1,
        ];

        $this->assertIsString($source);
        $this->assertIsString($creatorSource);
        $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file('core/di/application_content_service_dependencies.php'));
        $this->assertStringContainsString('final class application_content_service_dependencies', $source);
        $this->assertSame(0, substr_count($source, '$this->container->get('));
        foreach ($groupFiles as $file => $expectedLookups) {
            $groupSource = file_get_contents($root . '/' . $file);
            $this->assertIsString($groupSource);
            $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file($file));
            if ($expectedLookups !== 0) {
                $this->assertArrayHasKey($file, $map['dynamic_boundaries']['locations']);
            }
            $this->assertSame($expectedLookups, substr_count($groupSource, '$this->container->get('));
        }
        $this->assertSame(0, substr_count($creatorSource, '$container->get('));
        $this->assertStringContainsString('contentDependencies(container_interface $container): application_content_service_dependencies', $creatorSource);
        $this->assertStringContainsString('$contentDependencies = $this->contentDependencies($container);', $creatorSource);
        $this->assertStringContainsString('$contentDependencies->translationFileStorage()', $creatorSource);
    }

    public function testContentCreatorUsesNamedProjectServiceAvailabilityBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_content_service_creator.php');

        $this->assertIsString($source);
        $this->assertSame(1, substr_count($source, 'class_exists($className)'));
        $this->assertStringNotContainsString('if (!class_exists($className))', $source);
        $this->assertStringContainsString('private \Closure $projectServiceClassExists;', $source);
        $this->assertStringContainsString('?callable $projectServiceClassExists = null', $source);
        $this->assertStringContainsString('private function projectServiceClassExists(string $className): bool', $source);
        $this->assertStringContainsString('$this->projectServiceClassExists($className)', $source);
    }

    public function testControllerServiceDependenciesAreCompositionRootOnly(): void
    {
        require_once dirname(__DIR__, 2) . '/tools/ai_map.php';

        $root = dirname(__DIR__, 2);
        $map = php_fan_ai_build_map($root);
        $source = file_get_contents($root . '/core/di/application_controller_service_dependencies.php');
        $creatorSource = file_get_contents($root . '/core/di/application_controller_service_creator.php');
        $groupFiles = [
            'core/di/application_controller_bootstrap_runtime_dependencies.php' => 1,
            'core/di/application_controller_cache_runtime_dependencies.php' => 1,
            'core/di/application_controller_config_cache_runtime_dependencies.php' => 0,
            'core/di/application_controller_config_runtime_dependencies.php' => 1,
            'core/di/application_controller_handler_dependencies.php' => 0,
            'core/di/application_controller_header_plain_route_dependencies.php' => 1,
            'core/di/application_controller_matcher_plain_route_dependencies.php' => 1,
            'core/di/application_controller_obfuscator_factory_handler_dependencies.php' => 1,
            'core/di/application_controller_obfuscator_handler_dependencies.php' => 0,
            'core/di/application_controller_plain_config_dependencies.php' => 1,
            'core/di/application_controller_plain_dependencies.php' => 0,
            'core/di/application_controller_plain_file_handler_dependencies.php' => 1,
            'core/di/application_controller_plain_route_dependencies.php' => 0,
            'core/di/application_controller_request_handler_dependencies.php' => 1,
            'core/di/application_controller_runtime_dependencies.php' => 0,
        ];

        $this->assertIsString($source);
        $this->assertIsString($creatorSource);
        $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file('core/di/application_controller_service_dependencies.php'));
        $this->assertStringContainsString('final class application_controller_service_dependencies', $source);
        $this->assertSame(0, substr_count($source, '$this->container->get('));
        foreach ($groupFiles as $file => $expectedLookups) {
            $groupSource = file_get_contents($root . '/' . $file);
            $this->assertIsString($groupSource);
            $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file($file));
            if ($expectedLookups !== 0) {
                $this->assertArrayHasKey($file, $map['dynamic_boundaries']['locations']);
            }
            $this->assertSame($expectedLookups, substr_count($groupSource, '$this->container->get('));
        }
        $this->assertSame(0, substr_count($creatorSource, '$container->get('));
        $this->assertStringContainsString('controllerDependencies(container_interface $container): application_controller_service_dependencies', $creatorSource);
        $this->assertStringContainsString('$controllerDependencies = $this->controllerDependencies($container);', $creatorSource);
        $this->assertStringContainsString('$controllerDependencies->plainFileContext()', $creatorSource);
    }

    public function testControllerCreatorUsesNamedProjectServiceAvailabilityBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_controller_service_creator.php');

        $this->assertIsString($source);
        $this->assertSame(1, substr_count($source, 'class_exists($className)'));
        $this->assertStringNotContainsString('if (!class_exists($className))', $source);
        $this->assertStringContainsString('private \Closure $projectServiceClassExists;', $source);
        $this->assertStringContainsString('?callable $projectServiceClassExists = null', $source);
        $this->assertStringContainsString('private function projectServiceClassExists(string $className): bool', $source);
        $this->assertStringContainsString('$this->projectServiceClassExists($className)', $source);
    }

    public function testClientCreatorUsesNamedProjectServiceAvailabilityBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_client_service_creator.php');

        $this->assertIsString($source);
        $this->assertSame(1, substr_count($source, 'class_exists($className)'));
        $this->assertStringNotContainsString('if (!class_exists($className))', $source);
        $this->assertStringContainsString('private \Closure $projectServiceClassExists;', $source);
        $this->assertStringContainsString('?callable $projectServiceClassExists = null', $source);
        $this->assertStringContainsString('private function projectServiceClassExists(string $className): bool', $source);
        $this->assertSame(3, substr_count($source, '$this->projectServiceClassExists($className)'));
    }

    public function testSessionCreatorUsesNamedProjectServiceAvailabilityBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_session_service_creator.php');

        $this->assertIsString($source);
        $this->assertSame(1, substr_count($source, 'class_exists($className)'));
        $this->assertStringNotContainsString('if (!class_exists($className))', $source);
        $this->assertStringContainsString('private \Closure $projectServiceClassExists;', $source);
        $this->assertStringContainsString('?callable $projectServiceClassExists = null', $source);
        $this->assertStringContainsString('private function projectServiceClassExists(string $className): bool', $source);
        $this->assertStringContainsString('$this->projectServiceClassExists($className)', $source);
    }

    public function testPagerCreatorUsesNamedProjectServiceAvailabilityBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_pager_service_creator.php');

        $this->assertIsString($source);
        $this->assertSame(1, substr_count($source, 'class_exists($className)'));
        $this->assertStringNotContainsString('if (!class_exists($className))', $source);
        $this->assertStringContainsString('private \Closure $projectServiceClassExists;', $source);
        $this->assertStringContainsString('?callable $projectServiceClassExists = null', $source);
        $this->assertStringContainsString('private function projectServiceClassExists(string $className): bool', $source);
        $this->assertStringContainsString('$this->projectServiceClassExists($className)', $source);
    }

    public function testPagerServiceDependenciesAreCompositionRootOnly(): void
    {
        require_once dirname(__DIR__, 2) . '/tools/ai_map.php';

        $root = dirname(__DIR__, 2);
        $map = php_fan_ai_build_map($root);
        $source = file_get_contents($root . '/core/di/application_pager_service_dependencies.php');
        $creatorSource = file_get_contents($root . '/core/di/application_pager_service_creator.php');
        $groupFiles = [
            'core/di/application_pager_bootstrap_runtime_dependencies.php' => 1,
            'core/di/application_pager_cache_factory_config_cache_runtime_dependencies.php' => 1,
            'core/di/application_pager_config_cache_runtime_dependencies.php' => 0,
            'core/di/application_pager_config_config_cache_runtime_dependencies.php' => 1,
            'core/di/application_pager_context_dependencies.php' => 0,
            'core/di/application_pager_entity_context_dependencies.php' => 1,
            'core/di/application_pager_exception_dependencies.php' => 1,
            'core/di/application_pager_request_context_dependencies.php' => 1,
            'core/di/application_pager_runtime_dependencies.php' => 0,
            'core/di/application_pager_service_dependencies.php' => 0,
            'core/di/application_pager_tab_context_dependencies.php' => 1,
        ];

        $this->assertIsString($source);
        $this->assertIsString($creatorSource);
        $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file('core/di/application_pager_service_dependencies.php'));
        $this->assertStringContainsString('final class application_pager_service_dependencies', $source);
        foreach ($groupFiles as $file => $expectedLookups) {
            $groupSource = file_get_contents($root . '/' . $file);
            $this->assertIsString($groupSource);
            $this->assertSame('composition_roots', php_fan_ai_dynamic_boundary_category_for_file($file));
            if ($expectedLookups !== 0) {
                $this->assertArrayHasKey($file, $map['dynamic_boundaries']['locations']);
            }
            $this->assertSame($expectedLookups, substr_count($groupSource, '$this->container->get('));
        }
        $this->assertStringContainsString('pagerDependencies(container_interface $container): application_pager_service_dependencies', $creatorSource);
        $this->assertStringContainsString('$pagerDependencies = $this->pagerDependencies($container);', $creatorSource);
        $this->assertStringContainsString('$pagerDependencies->tab()', $creatorSource);
        $this->assertStringContainsString('$pagerDependencies->entityFactory()', $creatorSource);
        $this->assertSame(0, substr_count($creatorSource, '$container->get('));
    }

    public function testNavigationCreatorUsesNamedProjectServiceAvailabilityBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_navigation_service_creator.php');

        $this->assertIsString($source);
        $this->assertSame(1, substr_count($source, 'class_exists($className)'));
        $this->assertStringNotContainsString('if (!class_exists($className))', $source);
        $this->assertStringContainsString('private \Closure $projectServiceClassExists;', $source);
        $this->assertStringContainsString('?callable $projectServiceClassExists = null', $source);
        $this->assertStringContainsString('private function projectServiceClassExists(string $className): bool', $source);
        $this->assertStringContainsString('$this->projectServiceClassExists($className)', $source);
    }

    public function testUserCreatorUsesNamedProjectServiceAvailabilityBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_user_service_creator.php');

        $this->assertIsString($source);
        $this->assertSame(1, substr_count($source, 'class_exists($className)'));
        $this->assertStringNotContainsString('if (!class_exists($className))', $source);
        $this->assertStringContainsString('private \Closure $projectServiceClassExists;', $source);
        $this->assertStringContainsString('?callable $projectServiceClassExists = null', $source);
        $this->assertStringContainsString('private function projectServiceClassExists(string $className): bool', $source);
        $this->assertStringContainsString('$this->projectServiceClassExists($className)', $source);
    }

    public function testUtilityCreatorUsesNamedProjectServiceAvailabilityBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_utility_service_creator.php');

        $this->assertIsString($source);
        $this->assertSame(1, substr_count($source, 'class_exists($className)'));
        $this->assertStringNotContainsString('if (!class_exists($className))', $source);
        $this->assertStringContainsString('private \Closure $projectServiceClassExists;', $source);
        $this->assertStringContainsString('?callable $projectServiceClassExists = null', $source);
        $this->assertStringContainsString('private function projectServiceClassExists(string $className): bool', $source);
        $this->assertSame(4, substr_count($source, '$this->projectServiceClassExists($className)'));
    }

    public function testModelServiceLocatorUsageIsPinnedToMigrationAllowlist(): void
    {
        $allowedCounts = [];
        $actualCounts = [];

        foreach ($this->productionPhpFiles() as $file) {
            $source = file_get_contents($file);
            $this->assertIsString($source);
            $count = preg_match_all('/->\s*getService\s*\(/', $source);
            if ($count > 0) {
                $actualCounts[$this->relativePath($file)] = $count;
            }
        }
        ksort($actualCounts);

        $this->assertSame($allowedCounts, $actualCounts);
    }

    public function testModelServicePropertyAccessIsPinnedToEntityBoundary(): void
    {
        $violations = [];
        foreach ($this->productionPhpFiles() as $file) {
            $relativePath = $this->relativePath($file);
            if (!str_starts_with($relativePath, 'core/base/model/')) {
                continue;
            }

            $lines = file($file);
            $this->assertIsArray($lines);
            foreach ($lines as $lineNumber => $line) {
                if (!str_contains($line, '->service')) {
                    continue;
                }
                if ($relativePath === 'core/base/model/entity.php' && preg_match('/\$this->service\s*=\s*\$service\s*;/', $line) === 1) {
                    continue;
                }
                if ($relativePath === 'core/base/model/entity.php' && preg_match('/return\s+\$this->service\s*;/', $line) === 1) {
                    continue;
                }

                $violations[] = $relativePath . ':' . ($lineNumber + 1);
            }
        }

        $this->assertSame([], $violations, 'Model service property access outside entity constructor found in: ' . implode(', ', $violations));
    }

    public function testBaseServiceLayerDoesNotUseServiceStateLocatorAliases(): void
    {
        $violations = [];
        foreach ($this->productionPhpFiles() as $file) {
            $relativePath = $this->relativePath($file);
            if (!str_starts_with($relativePath, 'core/base/service/')) {
                continue;
            }

            $lines = file($file);
            $this->assertIsArray($lines);
            foreach ($lines as $lineNumber => $line) {
                if (preg_match('/->\s*service\b|\$service\b/', $line) !== 1) {
                    continue;
                }

                $violations[] = $relativePath . ':' . ($lineNumber + 1);
            }
        }

        $this->assertSame([], $violations, 'Base service layer service-state locator aliases found in: ' . implode(', ', $violations));
    }

    public function testRootBaseServiceStateShapesArePinnedToInjectedStateBoundaries(): void
    {
        $source = file(dirname(__DIR__, 2) . '/core/base/service.php');
        $this->assertIsArray($source);
        $violations = [];

        foreach ($source as $lineNumber => $line) {
            if (preg_match('/service_listener_state|service_single_state|\$listeners\b|\$instances\b|->service\b|\$service\b/', $line) !== 1) {
                continue;
            }
            if (preg_match('/^use fan\\\\core\\\\service\\\\service_(?:listener|single)_state;$/', trim($line)) === 1) {
                continue;
            }
            if (preg_match('/private \?service_(?:listener|single)_state \$service(?:Listener|Single)State = null;/', $line) === 1) {
                continue;
            }
            if (preg_match('/\?service_(?:listener|single)_state \$service(?:Listener|Single)State = null,?/', $line) === 1) {
                continue;
            }
            if (preg_match('/protected function (?:_singleState|serviceListenerState)\(\): service_(?:listener|single)_state/', $line) === 1) {
                continue;
            }

            $violations[] = 'core/base/service.php:' . ($lineNumber + 1);
        }

        $this->assertSame([], $violations, 'Root base service state shapes outside injected boundaries found in: ' . implode(', ', $violations));
    }

    public function testMigratedBlockArrayHelpersUseInjectedDependencies(): void
    {
        $migratedFiles = [
            'core/block/admin/base.php',
            'core/block/admin/data.php',
            'core/block/admin/data_form.php',
            'core/block/admin/data_table.php',
            'core/block/common/html_nav.php',
            'core/block/loader/base.php',
        ];
        $matches = [];

        foreach ($migratedFiles as $relativePath) {
            $source = file_get_contents(dirname(__DIR__, 2) . '/' . $relativePath);
            $this->assertIsString($source);
            if (preg_match('/\b(?:adduceToArray|array_merge_recursive_alt|array_val)\s*\(/', $source) === 1) {
                $matches[] = $relativePath;
            }
        }

        $this->assertSame([], $matches, 'Migrated block files still call global array helpers: ' . implode(', ', $matches));
    }

    public function testSessionStateBufferLookupUsesInjectedArrayValueReader(): void
    {
        $stateSource = file_get_contents(dirname(__DIR__, 2) . '/core/service/session_state.php');
        $registrySource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_state_registry.php');

        $this->assertIsString($stateSource);
        $this->assertIsString($registrySource);
        $this->assertStringContainsString('public function __construct(callable $arrayValueReader)', $stateSource);
        $this->assertStringContainsString('($this->arrayValueReader)($this->bufferData, $key, $default)', $stateSource);
        $this->assertStringNotContainsString('array_val(', $stateSource);
        $this->assertStringContainsString(
            'new session_state($container->get(service_id::ARRAY_VALUE_READER))',
            $registrySource
        );
    }

    public function testRequestInputConcreteConstructionIsLimitedToExplicitBoundaries(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\service\\\\request_input\s*\(/',
            [
                'core/factory/runtime/request_input_factory.php' => true,
            ],
            'Request input concrete construction outside explicit boundaries'
        );
    }

    public function testDatabaseExceptionConstructionIsLimitedToExplicitFactory(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\project\\\\exception\\\\service\\\\database\s*\(/',
            [],
            'Database exception concrete construction outside explicit factory'
        );
    }

    public function testDateExceptionConstructionIsLimitedToExplicitFactory(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\project\\\\exception\\\\service\\\\date\s*\(/',
            [
                'core/factory/date_exception_factory.php' => true,
            ],
            'Date exception concrete construction outside injected exception factory'
        );
    }

    public function testCacheConfigFatalConstructionUsesInjectedFactory(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/cache.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private \Closure $configCacheFatalExceptionFactory;', $source);
        $this->assertStringContainsString('$this->configCacheFatalExceptionFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('if (!isset($this->configCacheFatalExceptionFactory)) {', $source);
        $this->assertStringContainsString('$this->createConfigCacheFatalException(', $source);
        $this->assertStringContainsString('private function createConfigCacheFatalException(', $source);
        $this->assertStringNotContainsString('private ?\Closure $configCacheFatalExceptionFactory', $source);
        $this->assertStringNotContainsString('$this->configCacheFatalExceptionFactory === null', $source);
        $this->assertStringNotContainsString('new \Exception', $source);
    }

    public function testConfigRowConstructionIsLimitedToExplicitFactory(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\(?:core|project)\\\\service\\\\config\\\\row\s*\(/',
            [
                'core/factory/config_row_factory.php' => true,
            ],
            'Config row concrete construction outside explicit factory'
        );
    }

    public function testBlockLocalExceptionConstructionIsNotDoneDirectly(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\project\\\\exception\\\\block\\\\local\s*\(/',
            [],
            'Block local exception concrete construction outside injected exception factory'
        );
    }

    public function testBlockFatalExceptionConstructionIsNotDoneDirectly(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\project\\\\exception\\\\block\\\\fatal\s*\(/',
            [],
            'Block fatal exception concrete construction outside injected exception factory'
        );
    }

    public function testServiceFatalExceptionConstructionIsLimitedToKnownBoundariesAndPendingLeafThrowSites(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\project\\\\exception\\\\service\\\\fatal\s*\(/',
            [
                'core/factory/service_exception_factory.php' => true,
            ],
            'Service fatal exception concrete construction outside injected exception factory'
        );
    }

    public function testTemplateExceptionLayerIsRemoved(): void
    {
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/exception/template/fatal.php');
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/di/template_exception_factory.php');
    }

    public function testTemplateExceptionFactoryIsNotRegistered(): void
    {
        $registrarSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_support_service_registrar.php');

        $this->assertIsString($registrarSource);
        $this->assertStringNotContainsString('template_exception_factory', $registrarSource);
    }

    public function testPlainFatalExceptionConstructionIsLimitedToExplicitFactory(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\project\\\\exception\\\\plain\\\\fatal\s*\(/',
            [
                'core/factory/plain_exception_factory.php' => true,
            ],
            'Plain fatal exception concrete construction outside injected exception factory'
        );
    }

    public function testPlainFatalUsesInjectedClassNameResolver(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/exception/plain/fatal.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/plain_exception_factory.php');
        $registrarSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_support_service_registrar.php');
        $classNameResolverDependencySource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_support_class_name_resolver_registrar_dependencies.php');

        $this->assertIsString($source);
        $this->assertIsString($factorySource);
        $this->assertIsString($registrarSource);
        $this->assertIsString($classNameResolverDependencySource);
        $this->assertStringContainsString('?callable $classNameResolver = null', $source);
        $this->assertStringContainsString('private function className(object $object, \Closure $classNameResolver): string', $source);
        $this->assertStringContainsString('$this->className($controller, $classNameResolver)', $source);
        $this->assertStringNotContainsString('get_class_alt(', $source);
        $this->assertStringContainsString('private mixed $classNameResolver = null', $factorySource);
        $this->assertStringContainsString('static fn(object $object): string => \get_class_alt($object) ?? get_class($object)', $factorySource);
        $this->assertStringContainsString('$dependencies->classNameResolver()', $registrarSource);
        $this->assertStringNotContainsString('$container->get(service_id::CLASS_NAME_RESOLVER)', $registrarSource);
        $this->assertStringContainsString('$this->container->get(service_id::CLASS_NAME_RESOLVER)', $classNameResolverDependencySource);
    }

    public function testProjectFatalExceptionConstructionIsLimitedToExplicitFactory(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\project\\\\exception\\\\fatal\s*\(/',
            [
                'core/factory/fatal_exception_factory.php' => true,
            ],
            'Project fatal exception concrete construction outside injected exception factory'
        );
    }

    public function testCoreFatalExceptionConstructionIsLimitedToExplicitFactory(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\exception\\\\fatal\s*\(/',
            [
                'core/factory/core_fatal_exception_factory.php' => true,
            ],
            'Core fatal exception concrete construction outside injected exception factory'
        );
    }

    public function testCoreError500ExceptionConstructionIsNotDoneDirectly(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\exception\\\\error500\s*\(/',
            [],
            'Core error500 exception concrete construction outside injected exception factory'
        );
    }

    public function testError500ExceptionConstructionIsLimitedToExplicitBoundaries(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\project\\\\exception\\\\error500\s*\(/',
            [
                'core/factory/error500_exception_factory.php' => true,
            ],
            'Error500 exception concrete construction outside explicit DI/composition boundaries'
        );
    }

    public function testRequestInputFileLoadIsLimitedToExplicitFactories(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/require_once\s+[^;]*request_input(?:_source|_globals|_native_environment)?\.php\s*[\'"]?\s*;/',
            [
                'core/factory/request_input_source_defaults_provider_factory.php' => true,
            ],
            'Request input file load outside explicit factories'
        );
    }

    public function testProductionLoadingStatementsAreLimitedToExplicitBoundaries(): void
    {
        $allowedFiles = [
            'core/adapter/bootstrap_loader_file_storage.php' => true,
            'core/adapter/compiled_template_loader.php' => true,
            'core/adapter/error_demonstrator_loader.php' => true,
            'core/adapter/php_array_file.php' => true,
            'core/adapter/php_template_file.php' => true,
            'core/adapter/project_tool_loader.php' => true,
            'core/adapter/zend_autoloader.php' => true,
            'core/application/application.php' => true,
            'core/factory/application_container_defaults_provider_factory.php' => true,
            'core/factory/application_container_creator_defaults_provider_factory.php' => true,
            'core/factory/application_container_factory_callable_factory.php' => true,
            'core/factory/application_container_factory_provider_defaults_provider_factory.php' => true,
            'core/factory/application_container_operations_defaults_provider_factory.php' => true,
            'core/factory/application_container_registrar_defaults_provider_factory.php' => true,
            'core/factory/application_container_registry_defaults_provider_factory.php' => true,
            'core/factory/bootstrap_autoloader_defaults_provider_factory.php' => true,
            'core/factory/bootstrap_loader_defaults_provider_factory.php' => true,
            'core/factory/bootstrap_object_defaults_provider_factory.php' => true,
            'core/factory/bootstrap_runtime_service_defaults_provider_factory.php' => true,
            'core/factory/bootstrap_runtime_state_defaults_provider_factory.php' => true,
            'core/factory/bootstrap_runtime_operations_defaults_provider_factory.php' => true,
            'core/factory/bootstrap_runtime_service_factory_defaults_provider_factory.php' => true,
            'core/factory/bootstrap_runtime_state_factory_defaults_provider_factory.php' => true,
            'core/factory/context_core_defaults_provider_factory.php' => true,
            'core/factory/context_factory.php' => true,
            'core/factory/context_error_handling_defaults_provider_factory.php' => true,
            'core/factory/context_support_defaults_provider_factory.php' => true,
            'core/factory/error_logger_defaults_provider_factory.php' => true,
            'core/application/loader.php' => true,
            'core/factory/bootstrap_operations_defaults_provider_factory.php' => true,
            'core/factory/request_input_source_defaults_provider_factory.php' => true,
            'core/factory/request_input_defaults_provider_factory.php' => true,
            'core/factory/bootstrap_state_defaults_factory.php' => true,
            'core/factory/application_container_factory.php' => true,
            'core/di/application_adapter_registry_defaults_provider.php' => true,
            'core/factory/application_core_service_factory_defaults_provider_factory.php' => true,
            'core/factory/application_deferred_service_factory_provider_factory.php' => true,
            'core/factory/application_factory_provider_defaults_provider_factory.php' => true,
            'core/factory/application_model_factory_defaults_provider_factory.php' => true,
            'core/factory/application_registry_defaults_provider_factory.php' => true,
            'core/factory/application_runtime_factory_defaults_provider_factory.php' => true,
            'core/factory/application_service_creator_defaults_provider_factory.php' => true,
            'core/factory/application_service_engine_factory_defaults_provider_factory.php' => true,
            'core/factory/application_service_factory_defaults_provider_factory.php' => true,
            'core/factory/application_service_factory_defaults_provider.php' => true,
            'core/factory/application_service_registrar_defaults_provider_factory.php' => true,
            'core/factory/application_service_sub_factory_defaults_provider_factory.php' => true,
            'htdocs/index.php' => true,
        ];
        $matches = [];

        foreach ($this->productionPhpFiles() as $file) {
            $relativePath = $this->relativePath($file);
            if (isset($allowedFiles[$relativePath])) {
                continue;
            }

            $source = file_get_contents($file);
            $this->assertIsString($source);
            if ($this->hasLoadingStatement($source)) {
                $matches[] = $relativePath;
            }
        }

        $this->assertSame([], $matches, 'Loading statements outside explicit boundaries found in: ' . implode(', ', $matches));
    }

    public function testApplicationServiceFactoryRegistryDoesNotOwnConcreteFactoryLoading(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/service_factory_registry.php');

        $this->assertIsString($source);
        $this->assertFalse($this->hasLoadingStatement($source));
        $this->assertDoesNotMatchRegularExpression('/new\s+\\\\fan\\\\core\\\\di\\\\[A-Za-z_][A-Za-z0-9_]*_factory\s*\(/', $source);
    }

    public function testRestorePasswordLogStorageConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\restore_password_log_storage\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
            ],
            'Restore-password log storage concrete construction outside application composition root'
        );
    }

    public function testBootstrapConcreteDefaultsAreLimitedToExplicitFactories(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\service\\\\bootstrap_runtime\s*\(/',
            [
                'core/factory/bootstrap_runtime_service_defaults_provider_factory.php' => true,
            ],
            'Bootstrap runtime concrete construction outside explicit composition roots'
        );

        foreach ([
            '/new\s+\\\\fan\\\\core\\\\service\\\\service_listener_state\s*\(/' => 'Bootstrap runtime service listener state construction outside runtime state defaults root',
            '/new\s+\\\\fan\\\\core\\\\service\\\\service_single_state\s*\(/' => 'Bootstrap runtime service single state construction outside runtime state defaults root',
        ] as $pattern => $message) {
            $this->assertPatternOnlyAppearsInAllowedFiles(
                $pattern,
                [
                    'core/factory/bootstrap_runtime_state_defaults_provider_factory.php' => true,
                ],
                $message
            );
        }

        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\zend_autoloader_loader\s*\(/',
            [
                'core/factory/bootstrap_autoloader_defaults_provider_factory.php' => true,
            ],
            'Zend autoloader loader concrete construction outside explicit factory'
        );

        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\bootstrap_loader_file_storage\s*\(/',
            [
                'core/factory/bootstrap_loader_defaults_provider_factory.php' => true,
            ],
            'Bootstrap loader file storage concrete construction outside bootstrap composition root'
        );

        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\di\\\\configured_service_factory\s*\(/',
            [
                'core/factory/bootstrap_object_defaults_provider_factory.php' => true,
            ],
            'Bootstrap configured service factory construction outside bootstrap object defaults root'
        );

        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\di\\\\configured_class_instantiator\s*\(/',
            [
                'core/factory/bootstrap_object_defaults_provider_factory.php' => true,
            ],
            'Bootstrap configured class instantiator construction outside bootstrap object defaults root'
        );

        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+bootstrap_object_factory\s*\(/',
            [
                'core/factory/bootstrap_object_defaults_provider_factory.php' => true,
            ],
            'Bootstrap object factory construction outside bootstrap object defaults root'
        );

        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+bootstrap_config_loader\s*\(/',
            [
                'core/factory/bootstrap_config_defaults_factory.php' => true,
            ],
            'Bootstrap config loader construction outside bootstrap config defaults root'
        );

        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+state\s*\(/',
            [
                'core/factory/bootstrap_state_defaults_factory.php' => true,
            ],
            'Bootstrap state construction outside bootstrap state defaults root'
        );
    }

    public function testConfiguredConstructionDefaultsUseSplitProviderBoundaries(): void
    {
        $allowedFiles = [
            'core/factory/application_runtime_class_instantiator_provider.php',
            'core/factory/application_runtime_configured_service_provider.php',
            'core/factory/bootstrap_object_class_instantiator_provider.php',
            'core/factory/bootstrap_object_configured_service_provider.php',
        ];
        $filesWithConfiguredConstruction = [];

        foreach ($this->productionPhpFiles() as $file) {
            $source = file_get_contents($file);
            $this->assertIsString($source);
            if (preg_match('/\bnew\s+(?:\\\\?fan\\\\core\\\\di\\\\)?configured_(?:service_factory|class_instantiator)\s*\(/', $this->codeWithoutCommentsAndStrings($source)) === 1) {
                $filesWithConfiguredConstruction[] = $this->relativePath($file);
            }
        }

        sort($filesWithConfiguredConstruction);
        $expectedFiles = $allowedFiles;
        sort($expectedFiles);

        $this->assertSame($expectedFiles, $filesWithConfiguredConstruction);

        $runtimeSource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_runtime_factory_defaults_provider_factory.php');
        $bootstrapSource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/bootstrap_object_defaults_provider_factory.php');
        $runtimeConfiguredSource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_runtime_configured_service_provider.php');
        $runtimeClassInstantiatorSource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_runtime_class_instantiator_provider.php');
        $bootstrapConfiguredSource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/bootstrap_object_configured_service_provider.php');
        $bootstrapClassInstantiatorSource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/bootstrap_object_class_instantiator_provider.php');

        foreach ([
            $runtimeSource,
            $bootstrapSource,
            $runtimeConfiguredSource,
            $runtimeClassInstantiatorSource,
            $bootstrapConfiguredSource,
            $bootstrapClassInstantiatorSource,
        ] as $source) {
            $this->assertIsString($source);
            $this->assertDoesNotMatchRegularExpression(
                '/new\s+configured_service_factory\s*\(\s*new\s+configured_class_instantiator/s',
                $source
            );
        }

        $this->assertStringContainsString('?? new application_runtime_configured_service_provider()', $runtimeSource);
        $this->assertStringContainsString('?? new application_runtime_class_instantiator_provider()', $runtimeSource);
        $this->assertStringContainsString('?? new bootstrap_object_configured_service_provider()', $bootstrapSource);
        $this->assertStringContainsString('?? new bootstrap_object_class_instantiator_provider()', $bootstrapSource);
        $this->assertStringContainsString('new configured_service_factory($classInstantiator)', $runtimeConfiguredSource);
        $this->assertStringContainsString('new configured_class_instantiator(new reflection_class_factory())', $runtimeClassInstantiatorSource);
        $this->assertStringContainsString('new configured_service_factory($classInstantiator)', $bootstrapConfiguredSource);
        $this->assertStringContainsString('new configured_class_instantiator(new reflection_class_factory())', $bootstrapClassInstantiatorSource);
    }

    public function testBootstrapLoaderFilesystemOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/application/loader.php');
        $applicationSource = file_get_contents(dirname(__DIR__, 2) . '/core/application/application.php');

        $this->assertIsString($source);
        $this->assertIsString($applicationSource);
        $this->assertStringContainsString('private ?object $fileStorage = null;', $source);
        $this->assertStringContainsString('$this->fileStorage()->isReadable($convPath)', $source);
        $this->assertStringContainsString('$this->fileStorage()->isFile($path)', $source);
        $this->assertStringContainsString('$fileStorage->isDirectory($path)', $source);
        $this->assertStringContainsString('$fileStorage->realPath($path)', $source);
        $this->assertStringContainsString('$this->fileStorage()->scanDirectory($dir)', $source);
        $this->assertStringContainsString('callable $arrayValueReader', $source);
        $this->assertStringContainsString('$this->cntAliasArg = (int)($this->arrayValueReader)($config, \'cnt_alias_arg\', 3);', $source);
        $this->assertStringContainsString('private \Closure $fatalExceptionFactory;', $source);
        $this->assertStringContainsString('$this->fatalExceptionFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('if (!isset($this->fatalExceptionFactory)) {', $source);
        $this->assertStringNotContainsString('$this->fatalExceptionFactory = $fatalExceptionFactory === null ? null : \Closure::fromCallable($fatalExceptionFactory);', $source);
        $this->assertStringNotContainsString('private $fatalExceptionFactory = null;', $source);
        $this->assertStringContainsString("\$arguments[] = \$this->container()->get('array_value_reader');", $applicationSource);
        $this->assertStringNotContainsString('array_val(', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:is_readable|is_file|is_dir|realpath|scandir)\s*\(/',
            $source
        );
    }

    public function testBootstrapEntryFilesystemOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/application/application.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('$bootstrapFileStorage = $this->context()->bootstrapLoaderFileStorage();', $source);
        $this->assertStringContainsString('$bootstrapFileStorage->realPath(CORE_DIR . \'/../project\')', $source);
        $this->assertStringNotContainsString('$composerAutoload = dirname(CORE_DIR) . \'/vendor/autoload.php\';', $source);
        $this->assertStringNotContainsString('$bootstrapFileStorage->isReadable($composerAutoload)', $source);
        $this->assertStringNotContainsString('$bootstrapFileStorage->isReadable(PROJECT_DIR . \'/functions.php\')', $source);
        $this->assertStringNotContainsString('require_once CORE_DIR . \'/functions.php\';', $source);
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/bootstrap.php');
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:realpath|is_readable)\s*\(/',
            $source
        );
    }

    public function testRootWebEntrypointDelegatesToWebInitializer(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/htdocs/index.php');
        $initializerSource = file_get_contents(dirname(__DIR__, 2) . '/core/application/web_application_initializer.php');
        $initializerDefaultsSource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/web_application_initializer_defaults_factory.php');
        $runnerSource = file_get_contents(dirname(__DIR__, 2) . '/core/application/request_runner.php');
        $defaultsSource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/request_runner_defaults_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($initializerSource);
        $this->assertIsString($initializerDefaultsSource);
        $this->assertIsString($runnerSource);
        $this->assertIsString($defaultsSource);
        $this->assertStringContainsString("require_once __DIR__ . '/../vendor/autoload.php';", $source);
        $this->assertStringContainsString('((new web_application_initializer_defaults_factory())())->run();', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/autoload.php';", $source);
        $this->assertStringNotContainsString('$requestRunnerDefaults = new request_runner_defaults_factory();', $source);
        $this->assertStringNotContainsString('$requestRunner = $requestRunnerDefaults();', $source);
        $this->assertStringNotContainsString('$requestRunner = (new \fan\core\bootstrap\request_runner_defaults_factory())();', $source);
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/htdocs/autoload.php');
        $this->assertStringContainsString('return $this->requestRunner->run($this->configPath, $isEcho);', $initializerSource);
        $this->assertStringContainsString('$requestRunnerDefaults = new request_runner_defaults_factory();', $initializerDefaultsSource);
        $this->assertStringContainsString("dirname(__DIR__, 2) . '/htdocs'", $initializerDefaultsSource);
        $this->assertStringNotContainsString('contextBridge', $runnerSource);
        $this->assertStringContainsString('return $application->run($configPath, $isEcho, $errorHandler);', $runnerSource);
        $this->assertStringContainsString('new request_runner(', $defaultsSource);
        $this->assertStringNotContainsString('require_once', $defaultsSource);
        $this->assertStringNotContainsString('$applicationDefaultsProvider = (new bootstrap_application_defaults_provider_factory())();', $defaultsSource);
        $this->assertStringContainsString('$applicationDefaults = (new bootstrap_application_defaults_factory())->applicationDefaults();', $defaultsSource);
        $this->assertStringNotContainsString('new bootstrap_context_setter()', $defaultsSource);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_context_setter.php';", $defaultsSource);
        $this->assertStringNotContainsString('\bootstrap::setContext($context)', $defaultsSource);
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/application/bootstrap_context_setter.php');
        $this->assertStringNotContainsString("require_once __DIR__ . '/../core/bootstrap.php';", $source);
        $this->assertStringNotContainsString('\bootstrap::run', $source);
        $this->assertStringNotContainsString('new \fan\core\bootstrap\application()', $source);
    }

    public function testBootstrapConfigLoaderFilesystemOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/application/bootstrap_config_loader.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('$context->bootstrapLoaderFileStorage()->exists((string)$configPath)', $source);
        $this->assertStringContainsString('$context->loadPhpArrayFile((string)$configPath, [])', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\bfile_exists\s*\(/',
            $source
        );
    }

    public function testProjectToolFileStorageConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\project_tool_file_storage\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
            ],
            'Project tool file storage concrete construction outside application composition root'
        );
    }

    public function testBootstrapLayerDoesNotOwnSelfDefaultFactories(): void
    {
        $matches = [];

        foreach ($this->productionPhpFiles() as $file) {
            $relativePath = $this->relativePath($file);
            if (!str_starts_with($relativePath, 'core/application/')) {
                continue;
            }

            $source = file_get_contents($file);
            $this->assertIsString($source);
            if (preg_match('/self::default[A-Za-z0-9_]*\s*\(|private\s+static\s+function\s+default[A-Za-z0-9_]*\s*\(/', $source) === 1) {
                $matches[] = $relativePath;
            }
        }

        $this->assertSame([], $matches, 'Bootstrap self-default factories found in: ' . implode(', ', $matches));
    }

    public function testRequestServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/request_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('?callable $classNameResolver = null', $source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('public function __construct(callable $configuredServiceFactory, ?object $reflectionClassFactory = null)', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testRequestServiceUsesInjectedFatalExceptionFactory(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/request.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('$this->createServiceFatalException(', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
    }

    public function testRequestServiceArrayOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/request.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/request_service_factory.php');
        $creatorSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_core_service_creator.php');

        $this->assertIsString($source);
        $this->assertIsString($factorySource);
        $this->assertIsString($creatorSource);
        $this->assertStringContainsString('private function arrayAdducer(): callable', $source);
        $this->assertStringContainsString('private function recursiveMerger(): callable', $source);
        $this->assertStringContainsString('protected function arrayValueReader(): callable', $source);
        $this->assertStringNotContainsString('array_val(', $source);
        $this->assertStringNotContainsString('adduceToArray(', $source);
        $this->assertStringNotContainsString('array_merge_recursive_alt(', $source);
        $this->assertStringContainsString('static fn(mixed $value): array => \adduceToArray($value)', $factorySource);
        $this->assertStringContainsString('static fn(mixed ...$values): mixed => \array_merge_recursive_alt(...$values)', $factorySource);
        $this->assertStringContainsString('static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => \array_val($array, $key, $default)', $factorySource);
        $this->assertStringContainsString('$coreDependencies->arrayAdducer()', $creatorSource);
        $this->assertStringContainsString('$coreDependencies->recursiveMerger()', $creatorSource);
        $this->assertStringContainsString('$coreDependencies->arrayValueReader()', $creatorSource);
    }

    public function testServiceEngineFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/service_engine_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
    }

    public function testCurlServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/curl_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('public function __construct(callable $configuredServiceFactory, ?object $reflectionClassFactory = null)', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testCurlServiceArrayOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/curl.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/curl_service_factory.php');
        $creatorSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_client_service_creator.php');

        $this->assertIsString($source);
        $this->assertIsString($factorySource);
        $this->assertIsString($creatorSource);
        $this->assertStringContainsString('private function arrayAdducer(): callable', $source);
        $this->assertStringContainsString('private function curlArrayValueReader(): callable', $source);
        $this->assertStringNotContainsString('array_val(', $source);
        $this->assertStringNotContainsString('adduceToArray(', $source);
        $this->assertStringContainsString('static fn(mixed $value): array => \adduceToArray($value)', $factorySource);
        $this->assertStringContainsString('static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => \array_val($array, $key, $default)', $factorySource);
        $this->assertStringContainsString('$clientDependencies->arrayAdducer()', $creatorSource);
        $this->assertStringContainsString('$clientDependencies->arrayValueReader()', $creatorSource);
    }

    public function testNativeCurlCallsAreLimitedToCurlAdapterBoundary(): void
    {
        $allowedFiles = [
            'core/adapter/curl_adapter.php' => true,
        ];
        $matches = [];

        foreach ($this->productionPhpFiles() as $file) {
            $relativePath = $this->relativePath($file);
            if (isset($allowedFiles[$relativePath])) {
                continue;
            }

            $source = file_get_contents($file);
            $this->assertIsString($source);
            if (preg_match('/\bcurl_(?:init|setopt|exec|getinfo|error|close)\s*\(/', $source) === 1) {
                $matches[] = $relativePath;
            }
        }

        $this->assertSame([], $matches, 'Native cURL calls outside curl adapter found in: ' . implode(', ', $matches));
    }

    public function testDebugServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/debug_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testDebugMetaFileChecksAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/debug.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/debug_service_factory.php');
        $creatorSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_core_service_creator.php');

        $this->assertIsString($source);
        $this->assertIsString($factorySource);
        $this->assertIsString($creatorSource);
        $this->assertStringContainsString('private ?object $metaFileStorage = null;', $source);
        $this->assertStringContainsString('private \Closure $arrayAdducer;', $source);
        $this->assertStringContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringContainsString('$this->arrayAdducer = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->metaFileStorage()->exists($file)', $source);
        $this->assertStringContainsString('private function arrayAdducer(): callable', $source);
        $this->assertStringContainsString('private function reflectionClass(object|string $object): \ReflectionClass', $source);
        $this->assertStringContainsString('if (!isset($this->arrayAdducer)) {', $source);
        $this->assertStringContainsString('($this->arrayAdducer())($meta)', $source);
        $this->assertStringContainsString('$this->reflectionClass($block)', $source);
        $this->assertStringContainsString('$this->reflectionClassFactory->create($object)', $source);
        $this->assertStringContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('private ?\Closure $arrayAdducer', $source);
        $this->assertStringNotContainsString('private ?\Closure $reflectionClassFactory', $source);
        $this->assertStringNotContainsString('$this->arrayAdducer !== null', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory !== null', $source);
        $this->assertStringNotContainsString('adduceToArray(', $source);
        $this->assertStringContainsString('static fn(mixed $value): array => \adduceToArray($value)', $factorySource);
        $this->assertStringContainsString('object $reflectionClassFactory', $factorySource);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className, ?object $reflectionClassFactory): \ReflectionClass', $factorySource);
        $this->assertStringNotContainsString('$reflectionClassFactory->create($className)', $factorySource);
        $this->assertStringNotContainsString('method_exists($reflectionClassFactory, \'create\')', $factorySource);
        $this->assertStringNotContainsString('static fn(object|string $object): \ReflectionClass => new \ReflectionClass($object)', $factorySource);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $factorySource);
        $this->assertStringContainsString('$coreDependencies->arrayAdducer()', $creatorSource);
        $this->assertStringContainsString('$coreDependencies->reflectionClassFactory()', $creatorSource);
        $this->assertStringNotContainsString('new \ReflectionClass($block)', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\bis_file\s*\(/',
            $source
        );
    }

    public function testReflectorServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/reflector_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
    }

    public function testReflectorServiceUsesInjectedReflectionClassFactory(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/reflector.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/reflector_service_factory.php');
        $creatorSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_core_service_creator.php');

        $this->assertIsString($source);
        $this->assertIsString($factorySource);
        $this->assertIsString($creatorSource);
        $this->assertStringContainsString('private object $reflectionClassFactory;', $source);
        $this->assertStringContainsString('object $reflectionClassFactory', $source);
        $this->assertStringContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('\Closure::fromCallable', $source);
        $this->assertStringNotContainsString('private ?\Closure $reflectionClassFactory', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory !== null', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
        $this->assertStringContainsString('object $reflectionClassFactory', $factorySource);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className, object $reflectionClassFactory): \ReflectionClass', $factorySource);
        $this->assertStringNotContainsString('$reflectionClassFactory->create($className)', $factorySource);
        $this->assertStringNotContainsString('method_exists($reflectionClassFactory, \'create\')', $factorySource);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $factorySource);
        $this->assertStringNotContainsString('static fn(object|string $object): \ReflectionClass => new \ReflectionClass($object)', $factorySource);
        $this->assertStringContainsString('$coreDependencies->reflectionClassFactory()', $creatorSource);
    }

    public function testModelEntityUsesInjectedReflectionClassFactory(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/base/model/entity.php');
        $dependenciesSource = file_get_contents(dirname(__DIR__, 2) . '/core/base/model/entity_dependencies.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/model_entity_factory.php');
        $defaultsFactorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_model_factory_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($dependenciesSource);
        $this->assertIsString($factorySource);
        $this->assertIsString($defaultsFactorySource);
        $this->assertStringContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringContainsString('private \Closure $modelClassExists;', $source);
        $this->assertStringContainsString('?object $reflectionClassFactory = null', $source);
        $this->assertStringContainsString('?callable $modelClassExists = null', $source);
        $this->assertStringContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringContainsString('private function modelClassExists(string $className): bool', $source);
        $this->assertStringContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringContainsString('$this->modelClassExists($className)', $source);
        $this->assertStringContainsString('static fn(string $className): bool => class_exists($className)', $dependenciesSource);
        $this->assertStringContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('private function defaultReflectionClassFactory(): \Closure', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory = $this->defaultReflectionClassFactory();', $source);
        $this->assertStringNotContainsString('($this->reflectionClassFactory())($className)', $source);
        $this->assertStringNotContainsString('private ?\Closure $reflectionClassFactory', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
        $this->assertStringNotContainsString('!class_exists($className)', $source);
        $this->assertStringNotContainsString('=> class_exists($className)', $source);
        $this->assertStringContainsString('private ?object $reflectionClassFactory = null;', $factorySource);
        $this->assertStringContainsString('?object $reflectionClassFactory = null', $factorySource);
        $this->assertStringNotContainsString('static fn(object|string $object): \ReflectionClass => new \ReflectionClass($object)', $factorySource);
        $this->assertStringContainsString('new reflection_class_factory()', $defaultsFactorySource);
    }

    public function testBlockContextUsesInjectedReflectionClassFactory(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/block_context.php');
        $registrarSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_support_service_registrar.php');
        $tabResolverDependencySource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_support_tab_resolver_registrar_dependencies.php');
        $reflectionClassFactoryDependencySource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_support_reflection_class_factory_registrar_dependencies.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/adapter/reflection_class_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($registrarSource);
        $this->assertIsString($tabResolverDependencySource);
        $this->assertIsString($reflectionClassFactoryDependencySource);
        $this->assertIsString($factorySource);
        $this->assertStringContainsString('private object $reflectionClassFactory', $source);
        $this->assertStringContainsString('private \Closure $tabFactory;', $source);
        $this->assertStringContainsString('private \Closure $bootstrapRuntimeFactory;', $source);
        $this->assertStringContainsString('private \Closure $projectTabClassExists;', $source);
        $this->assertStringContainsString('private function tab(): object', $source);
        $this->assertStringContainsString('private function bootstrapRuntime(): object', $source);
        $this->assertStringContainsString('private function reflectionClass(object|string $object): \ReflectionClass', $source);
        $this->assertStringContainsString('private function projectTabClassExists(string $className): bool', $source);
        $this->assertStringContainsString('$this->reflectionClassFactory->create($object)', $source);
        $this->assertStringContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('container_interface', $source);
        $this->assertStringNotContainsString('$this->container->get', $source);
        $this->assertStringNotContainsString('private ?\Closure $reflectionClassFactory', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory !== null', $source);
        $this->assertStringNotContainsString('class_exists(\'\fan\project\service\tab\', false)', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($block)', $source);
        $this->assertStringContainsString('new block_context(', $registrarSource);
        $this->assertStringContainsString('$dependencies->tabResolver()', $registrarSource);
        $this->assertStringContainsString('$dependencies->bootstrapRuntimeResolver()', $registrarSource);
        $this->assertStringContainsString('service_id::REFLECTION_CLASS_FACTORY', $registrarSource);
        $this->assertStringContainsString('$dependencies->reflectionClassFactory()', $registrarSource);
        $this->assertStringNotContainsString('$container->get(service_id::REFLECTION_CLASS_FACTORY)', $registrarSource);
        $this->assertStringContainsString('$this->container->get(service_id::TAB)', $tabResolverDependencySource);
        $this->assertStringContainsString('$this->container->get(service_id::REFLECTION_CLASS_FACTORY)', $reflectionClassFactoryDependencySource);
        $this->assertStringContainsString('final class reflection_class_factory', $factorySource);
        $this->assertStringContainsString('return new \ReflectionClass($object);', $factorySource);
    }

    public function testFileSystemServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/file_system_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('object $storage', $source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('public function __construct(callable $configuredServiceFactory, ?object $reflectionClassFactory = null)', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testFileSystemServiceFilesystemOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/file_system.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private ?object $storage = null;', $source);
        $this->assertStringContainsString('$this->storage()', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:file_exists|is_file|is_readable|fopen|fread|feof|fclose)\s*\(/',
            $source
        );
    }

    public function testFileSystemStorageConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\file_system_storage\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
            ],
            'File-system storage concrete construction outside application composition root'
        );
    }

    public function testPlainImageFilesystemOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/plain/image.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('$this->context()->fileStorage()->isFile($data[\'filePath\'])', $source);
        $this->assertStringContainsString('$this->context()->fileStorage()->isReadable($nailStub)', $source);
        $this->assertStringContainsString('$this->context()->fileStorage()->size($nailStub)', $source);
        $this->assertStringContainsString('$this->context()->fileStorage()->modifiedTime($nailStub)', $source);
        $this->assertStringContainsString('$this->context()->fileStorage()->makeDirectory($nailDir, 0744, true)', $source);
        $this->assertStringContainsString('$this->createPlainFatalException(', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\plain\fatal as fatalException;', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:is_file|is_readable|filesize|filemtime|is_dir|mkdir|is_writable)\s*\(/',
            $source
        );
    }

    public function testPlainDbFileFilesystemOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/plain/db_file.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('$this->context()->fileStorage()->rewindStream($this->streamId)', $source);
        $this->assertStringContainsString('$this->context()->fileStorage()->passThroughStream($this->streamId)', $source);
        $this->assertStringContainsString('$this->context()->fileStorage()->outputFile($this->filePath)', $source);
        $this->assertStringContainsString('$this->context()->fileStorage()->isReadable($data[\'filePath\'])', $source);
        $this->assertStringContainsString('$this->context()->fileStorage()->modifiedTime($data[\'filePath\'])', $source);
        $this->assertStringContainsString('$this->context()->fileStorage()->size($data[\'filePath\'])', $source);
        $this->assertStringContainsString('protected function createPlainFatalException(', $source);
        $this->assertStringContainsString('$this->context()->createPlainFatalException($this, $message, $code, $previous)', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:is_readable|filesize|filemtime|readfile|rewind|fpassthru)\s*\(/',
            $source
        );
    }

    public function testPlainFileStorageConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\plain_file_storage\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
            ],
            'Plain file storage concrete construction outside application composition root'
        );
    }

    public function testMatcherItemFilesystemOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/matcher/item.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private ?object $routeFileStorage = null;', $source);
        $this->assertStringContainsString('$this->routeFileStorage()->isFile($path . \'/\' . $v . \'.php\')', $source);
        $this->assertStringContainsString('$this->routeFileStorage()->isDirectory($path . \'/\' . $v)', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:is_file|is_dir)\s*\(/',
            $source
        );
    }

    public function testMatcherRouteFileStorageConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\matcher_route_file_storage\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
            ],
            'Matcher route file storage concrete construction outside application composition root'
        );
    }

    public function testHeaderServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/header_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringContainsString('?callable $recursiveMerger = null', $source);
        $this->assertStringNotContainsString('?object $reflectionClassFactory = null', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testHeaderServiceUsesInjectedFatalExceptionFactory(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/header.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/header_service_factory.php');
        $creatorSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_core_service_creator.php');

        $this->assertIsString($source);
        $this->assertIsString($factorySource);
        $this->assertIsString($creatorSource);
        $this->assertStringContainsString('$this->createServiceFatalException(', $source);
        $this->assertStringContainsString('private \Closure $recursiveMerger;', $source);
        $this->assertStringContainsString('$this->recursiveMerger = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('if (!isset($this->recursiveMerger)) {', $source);
        $this->assertStringContainsString('private function recursiveMerger(): callable', $source);
        $this->assertStringContainsString('($this->recursiveMerger())(', $source);
        $this->assertStringNotContainsString('array_merge_recursive_alt(', $source);
        $this->assertStringNotContainsString('private mixed $recursiveMerger = null;', $source);
        $this->assertStringNotContainsString('$recursiveMerger === null ? null : \Closure::fromCallable($recursiveMerger)', $source);
        $this->assertStringContainsString('static fn(mixed ...$values): mixed => \array_merge_recursive_alt(...$values)', $factorySource);
        $this->assertStringContainsString('$coreDependencies->recursiveMerger()', $creatorSource);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
    }

    public function testLogFileStorageConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\log_file_storage\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
            ],
            'Log file storage concrete construction outside application composition root'
        );
    }

    public function testErrorServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/error_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('object $fileStorage', $source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('public function __construct(callable $configuredServiceFactory, ?object $reflectionClassFactory = null)', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testErrorServiceFilesystemOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/error.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('protected ?object $fileStorage = null;', $source);
        $this->assertStringContainsString('$this->fileStorage()->exists($file)', $source);
        $this->assertStringContainsString('$this->fileStorage()->scanDirectory($dirName)', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:is_dir|realpath|file_exists|file_put_contents|scandir|is_file|is_writable|unlink|chmod)\s*\(/',
            $source
        );
    }

    public function testErrorFileStorageConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\error_file_storage\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
            ],
            'Error file storage concrete construction outside application composition root'
        );
    }

    public function testErrorDemonstratorFilesystemOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/error/demonstrator.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private ?object $fileStorage = null;', $source);
        $this->assertStringContainsString('$this->fileStorage()->exists($v)', $source);
        $this->assertStringContainsString('$this->fileStorage()->exists($this->dataFile)', $source);
        $this->assertStringContainsString('$this->fileStorage()->read($this->tplFile)', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:file_exists|file_get_contents)\s*\(/',
            $source
        );
    }

    public function testErrorDemonstratorFileStorageConstructionIsLimitedToCompositionRoots(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\error_demonstrator_file_storage\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
            ],
            'Error demonstrator file storage concrete construction outside application composition root'
        );
    }

    public function testErrorDemonstratorFactoryFilesystemOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/runtime/error_demonstrator_factory.php');
        $loaderSource = file_get_contents(dirname(__DIR__, 2) . '/core/adapter/error_demonstrator_loader.php');

        $this->assertIsString($source);
        $this->assertIsString($loaderSource);
        $this->assertStringContainsString('private object $fileStorage', $source);
        $this->assertStringContainsString('private object $demonstratorLoader', $source);
        $this->assertStringContainsString('$demonstrator = $this->demonstratorLoader()->create($errMsg, $tplName, $input);', $source);
        $this->assertStringNotContainsString('include ', $source);
        $this->assertStringNotContainsString('require_once ', $source);
        $this->assertStringNotContainsString('new \fan\core\adapter\error_demonstrator_file_storage()', $source);
        $this->assertStringContainsString('$this->fileStorage()->isFile($projectPath)', $loaderSource);
        $this->assertStringContainsString('new error_demonstrator($errMsg, $tplName, $input)', $loaderSource);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\bis_file\s*\(/',
            $source . "\n" . $loaderSource
        );
    }

    public function testDateServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/date_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('public function __construct(callable $configuredServiceFactory, ?object $reflectionClassFactory = null)', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testDateModifyUsesInjectedInstanceFactory(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/date.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/date_service_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($factorySource);
        $this->assertStringContainsString('private mixed $dateInstanceFactory = null;', $source);
        $this->assertStringContainsString('private function createDateInstance(', $source);
        $this->assertStringContainsString('return $this->createDateInstance(', $source);
        $this->assertStringNotContainsString('new static(', $source);
        $this->assertStringContainsString('$dateInstanceFactory ??= function (', $factorySource);
        $this->assertStringContainsString('$this->__invoke(', $factorySource);
    }

    public function testCookieServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/cookie_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('public function __construct(callable $configuredServiceFactory, ?object $reflectionClassFactory = null)', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testCookieStateDependencyIsInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/cookie.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('Cookie state is not configured for cookie service.', $source);
        $this->assertStringContainsString('$this->state = $state ?? throw new \RuntimeException', $source);
        $this->assertStringContainsString('private \Closure $cookieValueEncoder;', $source);
        $this->assertStringContainsString('private \Closure $cookieValueDecoder;', $source);
        $this->assertStringContainsString('$this->cookieValueEncoder = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->cookieValueDecoder = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('if (!isset($this->cookieValueEncoder)) {', $source);
        $this->assertStringContainsString('if (!isset($this->cookieValueDecoder)) {', $source);
        $this->assertStringContainsString('$this->createServiceFatalException(', $source);
        $this->assertStringNotContainsString('private mixed $cookieValueEncoder = null;', $source);
        $this->assertStringNotContainsString('private mixed $cookieValueDecoder = null;', $source);
        $this->assertStringNotContainsString('$this->cookieValueEncoder = $cookieValueEncoder === null ? null : \Closure::fromCallable($cookieValueEncoder);', $source);
        $this->assertStringNotContainsString('$this->cookieValueDecoder = $cookieValueDecoder === null ? null : \Closure::fromCallable($cookieValueDecoder);', $source);
        $this->assertStringNotContainsString('new cookie_state()', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
    }

    public function testCookieStateConstructionIsLimitedToCompositionAndTests(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\?fan\\\\core\\\\service\\\\cookie_state\s*\(|new\s+cookie_state\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
                'unit/core/service/CookieStateTest.php' => true,
                'unit/core/service/CookieTest.php' => true,
                'unit/core/di/CookieServiceFactoryTest.php' => true,
            ],
            'Cookie state construction outside explicit composition/test boundaries'
        );
    }

    public function testUserStateDependencyIsInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/user.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/user_service_factory.php');
        $creatorSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_user_service_creator.php');
        $dependenciesSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_user_service_dependencies.php');
        $runtimeDependenciesSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_user_runtime_dependencies.php');
        $arrayRuntimeDependenciesSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_user_array_runtime_dependencies.php');

        $this->assertIsString($source);
        $this->assertIsString($factorySource);
        $this->assertIsString($creatorSource);
        $this->assertIsString($dependenciesSource);
        $this->assertIsString($runtimeDependenciesSource);
        $this->assertIsString($arrayRuntimeDependenciesSource);
        $this->assertStringContainsString('User state is not configured for user service.', $source);
        $this->assertStringContainsString('$this->userState = $userState ?? throw new \RuntimeException', $source);
        $this->assertStringContainsString('public function createUserFatalException(', $source);
        $this->assertStringContainsString('return $this->createServiceFatalException($message, $code, $previous);', $source);
        $this->assertStringContainsString('$this->createUserFatalException(', $source);
        $this->assertStringContainsString('private \Closure $userEngineFactory;', $source);
        $this->assertStringContainsString('private \Closure $instanceKeyEncoder;', $source);
        $this->assertStringContainsString('private \Closure $snapshotEncoder;', $source);
        $this->assertStringContainsString('private \Closure $snapshotDecoder;', $source);
        $this->assertStringContainsString('private \Closure $arrayAdducer;', $source);
        $this->assertStringContainsString('$this->userEngineFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->instanceKeyEncoder = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->snapshotEncoder = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->snapshotDecoder = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->arrayAdducer = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('if (!isset($this->userEngineFactory)) {', $source);
        $this->assertStringContainsString('if (!isset($this->instanceKeyEncoder)) {', $source);
        $this->assertStringContainsString('if (!isset($this->snapshotEncoder)) {', $source);
        $this->assertStringContainsString('if (!isset($this->snapshotDecoder)) {', $source);
        $this->assertStringContainsString('if (!isset($this->arrayAdducer)) {', $source);
        $this->assertStringContainsString('private function instanceKeyEncoder(): callable', $source);
        $this->assertStringContainsString('private function snapshotEncoder(): callable', $source);
        $this->assertStringContainsString('private function snapshotDecoder(): callable', $source);
        $this->assertStringContainsString('private function arrayAdducer(): callable', $source);
        $this->assertStringContainsString('($this->arrayAdducer())($role)', $source);
        $this->assertStringNotContainsString('private ?\Closure $userEngineFactory', $source);
        $this->assertStringNotContainsString('private mixed $instanceKeyEncoder = null;', $source);
        $this->assertStringNotContainsString('private mixed $snapshotEncoder = null;', $source);
        $this->assertStringNotContainsString('private mixed $snapshotDecoder = null;', $source);
        $this->assertStringNotContainsString('private ?\Closure $arrayAdducer', $source);
        $this->assertStringNotContainsString('$this->userEngineFactory === null', $source);
        $this->assertStringNotContainsString('$this->instanceKeyEncoder = $instanceKeyEncoder === null ? null : \Closure::fromCallable($instanceKeyEncoder);', $source);
        $this->assertStringNotContainsString('$this->snapshotEncoder = $snapshotEncoder === null ? null : \Closure::fromCallable($snapshotEncoder);', $source);
        $this->assertStringNotContainsString('$this->snapshotDecoder = $snapshotDecoder === null ? null : \Closure::fromCallable($snapshotDecoder);', $source);
        $this->assertStringNotContainsString('$this->arrayAdducer !== null', $source);
        $this->assertStringNotContainsString('adduceToArray(', $source);
        $this->assertStringContainsString('static fn(mixed $value): array => \adduceToArray($value)', $factorySource);
        $this->assertStringContainsString('$userDependencies->arrayAdducer()', $creatorSource);
        $this->assertStringContainsString('$this->runtime->arrayAdducer()', $dependenciesSource);
        $this->assertStringContainsString('$this->array->arrayAdducer()', $runtimeDependenciesSource);
        $this->assertStringContainsString('$this->container->get(service_id::ARRAY_ADDUCER)', $arrayRuntimeDependenciesSource);
        $this->assertStringNotContainsString('new user_state()', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
    }

    public function testUserEntityEngineUsesFacadeFatalExceptionFactory(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/user/entity.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/user_engine_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($factorySource);
        $this->assertStringContainsString('$this->createUserFatalException(', $source);
        $this->assertStringContainsString('$this->arrayValueReader()', $source);
        $this->assertStringContainsString('$this->arrayAdducer()', $source);
        $this->assertStringContainsString('$this->className($row)', $source);
        $this->assertStringNotContainsString('array_val(', $source);
        $this->assertStringNotContainsString('adduceToArray(', $source);
        $this->assertStringNotContainsString('get_class_alt(', $source);
        $this->assertStringContainsString('static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => \array_val($array, $key, $default)', $factorySource);
        $this->assertStringContainsString('static fn(mixed $value): array => \adduceToArray($value)', $factorySource);
        $this->assertStringContainsString('static fn(object $object): string => \get_class_alt($object) ?? get_class($object)', $factorySource);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
    }

    public function testUserBaseEngineUsesFacadeFatalExceptionFactory(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/user/base.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/user_engine_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($factorySource);
        $this->assertStringContainsString('protected function createUserFatalException(', $source);
        $this->assertStringContainsString('$this->createUserFatalException(', $source);
        $this->assertStringContainsString('private \Closure $snapshotEncoder;', $source);
        $this->assertStringContainsString('private \Closure $snapshotDecoder;', $source);
        $this->assertStringContainsString('private \Closure $arrayValueReader;', $source);
        $this->assertStringContainsString('private \Closure $arrayAdducer;', $source);
        $this->assertStringContainsString('private \Closure $classNameResolver;', $source);
        $this->assertStringContainsString('$this->snapshotEncoder = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->snapshotDecoder = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->arrayValueReader = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->arrayAdducer = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->classNameResolver = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('if (!isset($this->snapshotEncoder)) {', $source);
        $this->assertStringContainsString('if (!isset($this->snapshotDecoder)) {', $source);
        $this->assertStringContainsString('if (!isset($this->arrayValueReader)) {', $source);
        $this->assertStringContainsString('if (!isset($this->arrayAdducer)) {', $source);
        $this->assertStringContainsString('if (!isset($this->classNameResolver)) {', $source);
        $this->assertStringContainsString('private function snapshotEncoder(): callable', $source);
        $this->assertStringContainsString('private function snapshotDecoder(): callable', $source);
        $this->assertStringContainsString('protected function arrayValueReader(): callable', $source);
        $this->assertStringContainsString('protected function arrayAdducer(): callable', $source);
        $this->assertStringContainsString('protected function className(object $object): string', $source);
        $this->assertStringNotContainsString('private mixed $snapshotEncoder = null;', $source);
        $this->assertStringNotContainsString('private mixed $snapshotDecoder = null;', $source);
        $this->assertStringNotContainsString('private mixed $arrayValueReader = null;', $source);
        $this->assertStringNotContainsString('private mixed $arrayAdducer = null;', $source);
        $this->assertStringNotContainsString('private mixed $classNameResolver = null;', $source);
        $this->assertStringNotContainsString('$this->snapshotEncoder = $snapshotEncoder === null ? null : \Closure::fromCallable($snapshotEncoder);', $source);
        $this->assertStringNotContainsString('$this->snapshotDecoder = $snapshotDecoder === null ? null : \Closure::fromCallable($snapshotDecoder);', $source);
        $this->assertStringNotContainsString('$this->arrayValueReader = $arrayValueReader === null ? null : \Closure::fromCallable($arrayValueReader);', $source);
        $this->assertStringNotContainsString('$this->arrayAdducer = $arrayAdducer === null ? null : \Closure::fromCallable($arrayAdducer);', $source);
        $this->assertStringNotContainsString('$this->classNameResolver = $classNameResolver === null ? null : \Closure::fromCallable($classNameResolver);', $source);
        $this->assertStringNotContainsString('array_val(', $source);
        $this->assertStringNotContainsString('adduceToArray(', $source);
        $this->assertStringContainsString('static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => \array_val($array, $key, $default)', $factorySource);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
    }

    public function testUserConfigEngineUsesFacadeFatalExceptionFactory(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/user/config.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/user_engine_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($factorySource);
        $this->assertStringContainsString('$this->createUserFatalException(', $source);
        $this->assertStringContainsString('$this->arrayValueReader()', $source);
        $this->assertStringNotContainsString('array_val(', $source);
        $this->assertStringContainsString('static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => \array_val($array, $key, $default)', $factorySource);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
    }

    public function testUserStateConstructionIsLimitedToCompositionAndTests(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\?fan\\\\core\\\\service\\\\user_state\s*\(|new\s+user_state\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
                'unit/core/service/UserStateTest.php' => true,
                'unit/core/service/UserTest.php' => true,
                'unit/core/di/UserServiceFactoryTest.php' => true,
            ],
            'User state construction outside explicit composition/test boundaries'
        );
    }

    public function testBaseServiceStateDependenciesAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/base/service.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('Service listener state is not configured for', $source);
        $this->assertStringContainsString('Service single state is not configured for', $source);
        $this->assertStringContainsString('private $classNameResolver = null;', $source);
        $this->assertStringContainsString('private $arrayValueReader = null;', $source);
        $this->assertStringContainsString('protected function serviceClassName(): string', $source);
        $this->assertStringContainsString('protected function arrayValueReader(): callable', $source);
        $this->assertStringNotContainsString('get_class_name(', $source);
        $this->assertStringNotContainsString('array_val(', $source);
        $this->assertStringNotContainsString('new \fan\core\service\service_listener_state()', $source);
        $this->assertStringNotContainsString('new \fan\core\service\service_single_state()', $source);
    }

    public function testBootstrapRuntimeStateDependenciesAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/bootstrap_runtime.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('Bootstrap runtime service listener state is not configured.', $source);
        $this->assertStringContainsString('Bootstrap runtime service single state is not configured.', $source);
        $this->assertStringContainsString('Bootstrap runtime view loader state is not configured.', $source);
        $this->assertStringContainsString('Bootstrap runtime meta maker state is not configured.', $source);
        $this->assertStringContainsString('Bootstrap runtime spec-file image row state is not configured.', $source);
        $this->assertStringContainsString('Class name resolver is not configured.', $source);
        $this->assertStringContainsString('Array value reader is not configured.', $source);
        $this->assertStringContainsString('public function classNameResolver(): callable', $source);
        $this->assertStringContainsString('public function arrayValueReader(): callable', $source);
        $this->assertStringNotContainsString('new service_listener_state()', $source);
        $this->assertStringNotContainsString('new service_single_state()', $source);
        $this->assertStringNotContainsString('new \fan\core\view\router\loader_state()', $source);
        $this->assertStringNotContainsString('new \fan\core\base\meta\maker_state()', $source);
        $this->assertStringNotContainsString('new \fan\core\base\model\spec_file\image\row_state()', $source);
    }

    public function testJsonServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/json_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('public function __construct(callable $configuredServiceFactory, ?object $reflectionClassFactory = null)', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testObfuscatorServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/obfuscator_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('object $fileStorage', $source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('public function __construct(callable $configuredServiceFactory, ?object $reflectionClassFactory = null)', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testObfuscatorFilesystemOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/obfuscator.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private ?object $fileStorage = null;', $source);
        $this->assertStringContainsString('$this->fileStorage()', $source);
        $this->assertStringContainsString('$this->createServiceFatalException(', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\core\exception\service\fatal as fatalException;', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:is_file|is_readable|is_dir|mkdir|filesize|filemtime|file_get_contents|file_put_contents)\s*\(/',
            $source
        );
    }

    public function testObfuscatorFileStorageConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\obfuscator_file_storage\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
            ],
            'Obfuscator file storage concrete construction outside application composition root'
        );
    }

    public function testPagerServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/pager_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('public function __construct(callable $configuredServiceFactory, ?object $reflectionClassFactory = null)', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testRoleServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/role_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('public function __construct(callable $configuredServiceFactory, ?object $reflectionClassFactory = null)', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testTranslationServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/translation_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('object $fileStorage', $source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('public function __construct(callable $configuredServiceFactory, ?object $reflectionClassFactory = null)', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testTranslationFilesystemOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/translation.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private ?object $translationFileStorage = null;', $source);
        $this->assertStringContainsString('$this->translationFileStorage()', $source);
        $this->assertStringContainsString('$this->createServiceFatalException(', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use project\exception\service\fatal as fatalException;', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:is_readable|file_put_contents)\s*\(/',
            $source
        );
    }

    public function testTranslationFileStorageConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\translation_file_storage\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
            ],
            'Translation file storage concrete construction outside application composition root'
        );
    }

    public function testLocaleServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/locale_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('public function __construct(callable $configuredServiceFactory, ?object $reflectionClassFactory = null)', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testLocaleServiceUsesInjectedArrayAdducer(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/locale.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/locale_service_factory.php');
        $creatorSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_core_service_creator.php');

        $this->assertIsString($source);
        $this->assertIsString($factorySource);
        $this->assertIsString($creatorSource);
        $this->assertStringContainsString('private function arrayAdducer(): callable', $source);
        $this->assertStringContainsString('$this->arrayAdducer()($availableLng)', $source);
        $this->assertStringNotContainsString('adduceToArray(', $source);
        $this->assertStringContainsString('static fn(mixed $value): array => \adduceToArray($value)', $factorySource);
        $this->assertStringContainsString('$coreDependencies->arrayAdducer()', $creatorSource);
    }

    public function testRestServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/rest_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('public function __construct(callable $configuredServiceFactory, ?object $reflectionClassFactory = null)', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testRestServiceDependenciesAreInjectedWithoutNullableClosureState(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/rest.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private \Closure $jsonFactory;', $source);
        $this->assertStringContainsString('private \Closure $curlFactory;', $source);
        $this->assertStringContainsString('private \Closure $errorFactory;', $source);
        $this->assertStringContainsString('$this->jsonFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->curlFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->errorFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('if (!isset($this->jsonFactory)) {', $source);
        $this->assertStringContainsString('if (!isset($this->curlFactory)) {', $source);
        $this->assertStringContainsString('if (!isset($this->errorFactory)) {', $source);
        $this->assertStringNotContainsString('private ?\Closure $jsonFactory', $source);
        $this->assertStringNotContainsString('private ?\Closure $curlFactory', $source);
        $this->assertStringNotContainsString('private ?\Closure $errorFactory', $source);
        $this->assertStringNotContainsString('$this->jsonFactory === null', $source);
        $this->assertStringNotContainsString('$this->curlFactory === null', $source);
        $this->assertStringNotContainsString('$this->errorFactory === null', $source);
        $this->assertStringNotContainsString('containerService(', $source);
        $this->assertStringNotContainsString('getContainerService(', $source);
    }

    public function testFormServiceIsRemovedFromCoreServiceComposition(): void
    {
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/service/form.php');
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/service/form_state.php');
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/di/form_service_factory.php');
    }

    public function testFormStateConstructionIsNotUsed(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\?fan\\\\core\\\\service\\\\form_state\s*\(|new\s+form_state\s*\(/',
            [],
            'Form state construction outside explicit composition/test boundaries'
        );
    }

    public function testApplicationServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('public function __construct(callable $configuredServiceFactory, ?object $reflectionClassFactory = null)', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testApplicationServiceUsesInjectedArrayAdducer(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/application.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_service_factory.php');
        $creatorSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_core_service_creator.php');

        $this->assertIsString($source);
        $this->assertIsString($factorySource);
        $this->assertIsString($creatorSource);
        $this->assertStringContainsString('private \Closure $arrayAdducer;', $source);
        $this->assertStringContainsString('$this->arrayAdducer = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('private function arrayAdducer(): callable', $source);
        $this->assertStringContainsString('($this->arrayAdducer())($usedNames)', $source);
        $this->assertStringNotContainsString('private ?\Closure $arrayAdducer', $source);
        $this->assertStringNotContainsString('$this->arrayAdducer !== null', $source);
        $this->assertStringNotContainsString('adduceToArray(', $source);
        $this->assertStringContainsString('static fn(mixed $value): array => \adduceToArray($value)', $factorySource);
        $this->assertStringContainsString('$coreDependencies->arrayAdducer()', $creatorSource);
    }

    public function testBlockExceptionFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/block_exception_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
    }

    public function testBlockFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/block_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
    }

    public function testCacheEngineFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/cache_engine_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private object $fileStorage', $source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testCacheBaseEngineUsesInjectedFatalExceptionFactory(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/cache/base.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('protected function createCacheFatalException(', $source);
        $this->assertStringContainsString('$this->facade->createCacheFatalException($message, $code, $previous)', $source);
        $this->assertStringContainsString('$this->createCacheFatalException(', $source);
        $this->assertStringContainsString('private \Closure $payloadEncoder;', $source);
        $this->assertStringContainsString('private \Closure $payloadDecoder;', $source);
        $this->assertStringContainsString('private \Closure $jsonPayloadChecker;', $source);
        $this->assertStringContainsString('$this->payloadEncoder = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->payloadDecoder = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->jsonPayloadChecker = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('if (!isset($this->payloadEncoder)) {', $source);
        $this->assertStringContainsString('if (!isset($this->payloadDecoder)) {', $source);
        $this->assertStringContainsString('if (!isset($this->jsonPayloadChecker)) {', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
        $this->assertStringNotContainsString('private mixed $payloadEncoder = null;', $source);
        $this->assertStringNotContainsString('private mixed $payloadDecoder = null;', $source);
        $this->assertStringNotContainsString('private mixed $jsonPayloadChecker = null;', $source);
        $this->assertStringNotContainsString('$this->payloadEncoder = $payloadEncoder === null ? null : \Closure::fromCallable($payloadEncoder);', $source);
        $this->assertStringNotContainsString('$this->payloadDecoder = $payloadDecoder === null ? null : \Closure::fromCallable($payloadDecoder);', $source);
        $this->assertStringNotContainsString('$this->jsonPayloadChecker = $jsonPayloadChecker === null ? null : \Closure::fromCallable($jsonPayloadChecker);', $source);
    }

    public function testCacheFileEngineFilesystemOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/cache/file.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private ?object $fileStorage = null;', $source);
        $this->assertStringContainsString('$this->fileStorage()', $source);
        $this->assertStringContainsString('$this->createCacheFatalException(', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:file_exists|is_dir|is_writable|is_file|mkdir|file_get_contents|file_put_contents|unlink)\s*\(/',
            $source
        );
    }

    public function testCacheMemcacheEngineUsesFacadeFatalExceptionFactory(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/cache/memcache.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/cache_engine_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($factorySource);
        $this->assertStringContainsString('$this->createCacheFatalException(', $source);
        $this->assertStringContainsString('$this->createConfigFatalException(', $source);
        $this->assertStringContainsString('$arrayValueReader = $this->arrayValueReader();', $source);
        $this->assertStringContainsString('$keeper = ($this->memcacheKeeperFactory())();', $source);
        $this->assertStringContainsString('private mixed $memcacheKeeperFactory = null,', $source);
        $this->assertStringContainsString('private mixed $memcacheAvailabilityChecker = null', $source);
        $this->assertStringNotContainsString('array_val(', $source);
        $this->assertStringNotContainsString('new \Memcache', $source);
        $this->assertStringNotContainsString("class_exists('\\Memcache')", $source);
        $this->assertStringContainsString('static fn(): object => new \Memcache()', $factorySource);
        $this->assertStringContainsString('static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => \array_val($array, $key, $default)', $factorySource);
        $this->assertStringNotContainsString('new \fan\core\exception\fatal', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\Memcache\s*\(/',
            [
                'core/factory/cache_engine_factory.php' => true,
            ],
            'Memcache concrete construction outside explicit cache engine factory'
        );
    }

    public function testCacheServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/cache_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('?object $sourceFileMetadata = null', $source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('public function __construct(callable $configuredServiceFactory, ?object $reflectionClassFactory = null)', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testCacheSourceFileMetadataOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/cache.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private ?object $sourceFileMetadata = null;', $source);
        $this->assertStringContainsString('private \Closure $errorFactory;', $source);
        $this->assertStringContainsString('private \Closure $configCacheFatalExceptionFactory;', $source);
        $this->assertStringContainsString('$this->errorFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->configCacheFatalExceptionFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('if (!isset($this->errorFactory)) {', $source);
        $this->assertStringContainsString('$this->sourceFileMetadata()', $source);
        $this->assertStringContainsString('$this->createServiceFatalException(', $source);
        $this->assertStringNotContainsString('private ?\Closure $errorFactory', $source);
        $this->assertStringNotContainsString('private ?\Closure $configCacheFatalExceptionFactory', $source);
        $this->assertStringNotContainsString('$this->errorFactory === null', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:is_file|filesize|filemtime)\s*\(/',
            $source
        );
    }

    public function testCacheSourceFileMetadataConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\cache_source_file_metadata\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
            ],
            'Cache source file metadata concrete construction outside application composition root'
        );
    }

    public function testFileDataCacheWrapperFileMetadataOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/cache/wrapper/file_data.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private \Closure $cacheFactory;', $source);
        $this->assertStringContainsString('private \Closure $entityFactory;', $source);
        $this->assertStringContainsString('$this->cacheFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->entityFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('if (!isset($this->cacheFactory)) {', $source);
        $this->assertStringContainsString('if (!isset($this->entityFactory)) {', $source);
        $this->assertStringContainsString('private ?object $fileMetadata = null;', $source);
        $this->assertStringContainsString('$this->fileMetadata()->modifiedTime($filePath)', $source);
        $this->assertStringNotContainsString('private ?\Closure $cacheFactory', $source);
        $this->assertStringNotContainsString('private ?\Closure $entityFactory', $source);
        $this->assertStringNotContainsString('$this->cacheFactory === null', $source);
        $this->assertStringNotContainsString('$this->entityFactory === null', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:filemtime|filesize)\s*\(/',
            $source
        );
    }

    public function testConfigServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/config_service_factory.php');
        $creatorSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_infrastructure_service_creator.php');
        $dependencySource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_infrastructure_config_cache_dependencies.php');
        $supportDependencySource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_infrastructure_config_cache_support_dependencies.php');
        $classHelperDependencySource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_infrastructure_config_cache_class_helper_dependencies.php');
        $supportRegistrarSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_support_service_registrar.php');

        $this->assertIsString($source);
        $this->assertIsString($creatorSource);
        $this->assertIsString($dependencySource);
        $this->assertIsString($supportDependencySource);
        $this->assertIsString($classHelperDependencySource);
        $this->assertIsString($supportRegistrarSource);
        $this->assertStringContainsString('object $sourceFileMetadata', $source);
        $this->assertStringContainsString('object $sourceFileStorage', $source);
        $this->assertStringContainsString('private \Closure $shortClassNameResolver;', $source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('?object $reflectionClassFactory = null', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringContainsString('static fn(object|string $object): string => \get_class_name($object)', $source);
        $this->assertStringContainsString('$infrastructureDependencies->shortClassNameResolver()', $creatorSource);
        $this->assertStringContainsString('$this->support->shortClassNameResolver()', $dependencySource);
        $this->assertStringContainsString('$this->classHelper->shortClassNameResolver()', $supportDependencySource);
        $this->assertStringContainsString('$this->container->get(service_id::SHORT_CLASS_NAME_RESOLVER)', $classHelperDependencySource);
        $this->assertStringContainsString('service_id::SHORT_CLASS_NAME_RESOLVER', $supportRegistrarSource);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
    }

    public function testConfigServiceSourceFileMetadataOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/config.php');
        $rowSource = file_get_contents(dirname(__DIR__, 2) . '/core/service/config/row.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/config_row_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($rowSource);
        $this->assertIsString($factorySource);
        $this->assertStringContainsString('private ?object $sourceFileMetadata = null;', $source);
        $this->assertStringContainsString('private ?object $sourceFileStorage = null;', $source);
        $this->assertStringContainsString('private mixed $shortClassNameResolver = null;', $source);
        $this->assertStringContainsString('private function shortClassName(object|string $object): string', $source);
        $this->assertStringContainsString('$name = $this->shortClassName($service);', $source);
        $this->assertStringContainsString('private function shortClassName(object|string $object): string', $rowSource);
        $this->assertStringContainsString('$name = $this->shortClassName($service);', $rowSource);
        $this->assertStringContainsString('static fn(object|string $object): string => \get_class_name($object)', $factorySource);
        $this->assertStringContainsString('$this->sourceFileMetadata()->size($filePath)', $source);
        $this->assertStringContainsString('$engine->setFileStorage($this->sourceFileStorage());', $source);
        $this->assertStringNotContainsString('get_class_name(', $source);
        $this->assertStringNotContainsString('get_class_name(', $rowSource);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\bfilesize\s*\(/',
            $source
        );
    }

    public function testConfigBaseFilesystemOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/config/base.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private ?object $fileStorage = null;', $source);
        $this->assertStringContainsString('public function setFileStorage(object $fileStorage): static', $source);
        $this->assertStringContainsString('$this->fileStorage()->exists($filePath)', $source);
        $this->assertStringContainsString('private function createConfigFatalException(', $source);
        $this->assertStringContainsString('$this->facade->createConfigFatalException($message, $code, $previous)', $source);
        $this->assertStringContainsString('$this->createConfigFatalException(', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\bfile_exists\s*\(/',
            $source
        );
    }

    public function testConfigSourceFileStorageConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\config_source_file_storage\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
            ],
            'Config source file storage concrete construction outside application composition root'
        );
    }

    public function testDatabaseEngineFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/di/database_engine_factory.php');
    }

    public function testRuntimeAdapterConstructionIsLimitedToExplicitCompositionRoots(): void
    {
        foreach ([
            'data_loader' => [
                'core/factory/application_registry_defaults_provider_factory.php' => true,
            ],
            'error_log_writer' => [
                'core/factory/error_logger_defaults_provider_factory.php' => true,
                'core/factory/application_registry_defaults_provider_factory.php' => true,
            ],
            'header_writer' => [
                'core/factory/application_registry_defaults_provider_factory.php' => true,
            ],
            'cookie_writer' => [
                'core/factory/application_registry_defaults_provider_factory.php' => true,
            ],
            'curl_adapter' => [
                'core/factory/application_registry_defaults_provider_factory.php' => true,
            ],
        ] as $adapterClass => $allowedFiles) {
            $this->assertPatternOnlyAppearsInAllowedFiles(
                '/new\s+\\\\fan\\\\core\\\\adapter\\\\' . preg_quote($adapterClass, '/') . '\s*\(/',
                $allowedFiles,
                $adapterClass . ' construction outside explicit composition roots'
            );
        }
    }

    public function testDataLoaderArrayOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/adapter/data_loader.php');
        $runtimeDefaultsProviderSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_runtime_adapter_defaults_provider.php');
        $registryDefaultsProviderFactorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_registry_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($runtimeDefaultsProviderSource);
        $this->assertIsString($registryDefaultsProviderFactorySource);
        $this->assertStringContainsString('callable $arrayAdducer', $source);
        $this->assertStringContainsString('callable $recursiveMerger', $source);
        $this->assertStringContainsString('$this->arrayAdducer', $source);
        $this->assertStringContainsString('$this->recursiveMerger', $source);
        $this->assertStringNotContainsString('adduceToArray(', $source);
        $this->assertStringNotContainsString('array_merge_recursive_alt(', $source);
        $this->assertStringContainsString('static fn(mixed $value): array => \adduceToArray($value)', $registryDefaultsProviderFactorySource);
        $this->assertStringContainsString('static fn(mixed ...$values): mixed => \array_merge_recursive_alt(...$values)', $registryDefaultsProviderFactorySource);
        $this->assertStringNotContainsString('adduceToArray(', $runtimeDefaultsProviderSource);
        $this->assertStringNotContainsString('array_merge_recursive_alt(', $runtimeDefaultsProviderSource);
    }

    public function testBlockLoaderArrayOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/block/loader/base.php');
        $blockBaseSource = file_get_contents(dirname(__DIR__, 2) . '/core/block/base.php');
        $blockDependencyResolverSource = file_get_contents(dirname(__DIR__, 2) . '/core/block/base_dependency_resolver.php');
        $blockDependencyHelperSource = file_get_contents(dirname(__DIR__, 2) . '/core/block/base_dependency_helper_group.php');
        $blockDependencyArrayHelperSource = file_get_contents(dirname(__DIR__, 2) . '/core/block/base_dependency_array_helper_group.php');
        $blockDependencyArrayTransformHelperSource = file_get_contents(dirname(__DIR__, 2) . '/core/block/base_dependency_array_transform_helper_group.php');
        $blockDependencyArrayAdducerHelperSource = file_get_contents(dirname(__DIR__, 2) . '/core/block/base_dependency_array_adducer_helper_group.php');
        $blockDependencyRecursiveMergerHelperSource = file_get_contents(dirname(__DIR__, 2) . '/core/block/base_dependency_recursive_merger_helper_group.php');
        $blockDependencyArrayReadHelperSource = file_get_contents(dirname(__DIR__, 2) . '/core/block/base_dependency_array_read_helper_group.php');
        $blockDependencyArrayValueReaderHelperSource = file_get_contents(dirname(__DIR__, 2) . '/core/block/base_dependency_array_value_reader_helper_group.php');
        $blockDependencyArrayLikeCheckerHelperSource = file_get_contents(dirname(__DIR__, 2) . '/core/block/base_dependency_array_like_checker_helper_group.php');
        $blockDependencyClassHelperSource = file_get_contents(dirname(__DIR__, 2) . '/core/block/base_dependency_class_helper_group.php');
        $tabSource = file_get_contents(dirname(__DIR__, 2) . '/core/service/tab.php');
        $tabFactorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/tab_service_factory.php');
        $supportRegistrarSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_support_service_registrar.php');

        $this->assertIsString($source);
        $this->assertIsString($blockBaseSource);
        $this->assertIsString($blockDependencyResolverSource);
        $this->assertIsString($blockDependencyHelperSource);
        $this->assertIsString($blockDependencyArrayHelperSource);
        $this->assertIsString($blockDependencyArrayTransformHelperSource);
        $this->assertIsString($blockDependencyArrayAdducerHelperSource);
        $this->assertIsString($blockDependencyRecursiveMergerHelperSource);
        $this->assertIsString($blockDependencyArrayReadHelperSource);
        $this->assertIsString($blockDependencyArrayValueReaderHelperSource);
        $this->assertIsString($blockDependencyArrayLikeCheckerHelperSource);
        $this->assertIsString($blockDependencyClassHelperSource);
        $this->assertIsString($tabSource);
        $this->assertIsString($tabFactorySource);
        $this->assertIsString($supportRegistrarSource);
        $this->assertStringContainsString('$this->arrayAdducer()', $source);
        $this->assertStringContainsString('$this->recursiveMerger()', $source);
        $this->assertStringNotContainsString('adduceToArray(', $source);
        $this->assertStringNotContainsString('array_merge_recursive_alt(', $source);
        $this->assertStringContainsString('new base_dependency_helper_group($container)', $blockDependencyResolverSource);
        $this->assertStringContainsString('new base_dependency_array_helper_group($container)', $blockDependencyHelperSource);
        $this->assertStringContainsString('new base_dependency_class_helper_group($container)', $blockDependencyHelperSource);
        $this->assertStringContainsString('new base_dependency_array_transform_helper_group($container)', $blockDependencyArrayHelperSource);
        $this->assertStringContainsString('new base_dependency_array_read_helper_group($container)', $blockDependencyArrayHelperSource);
        $this->assertStringContainsString('new base_dependency_array_adducer_helper_group($container)', $blockDependencyArrayTransformHelperSource);
        $this->assertStringContainsString('new base_dependency_recursive_merger_helper_group($container)', $blockDependencyArrayTransformHelperSource);
        $this->assertStringContainsString('new base_dependency_array_value_reader_helper_group($container)', $blockDependencyArrayReadHelperSource);
        $this->assertStringContainsString('new base_dependency_array_like_checker_helper_group($container)', $blockDependencyArrayReadHelperSource);
        $this->assertStringContainsString("'arrayAdducer' => \$this->container->get('array_adducer')", $blockDependencyArrayAdducerHelperSource);
        $this->assertStringContainsString("'recursiveMerger' => \$this->container->get('recursive_merger')", $blockDependencyRecursiveMergerHelperSource);
        $this->assertStringContainsString("'arrayValueReader' => \$this->container->get('array_value_reader')", $blockDependencyArrayValueReaderHelperSource);
        $this->assertStringContainsString("'arrayLikeChecker' => \$this->container->get('array_like_checker')", $blockDependencyArrayLikeCheckerHelperSource);
        $this->assertStringContainsString("'shortClassNameResolver' => \$this->container->get('short_class_name_resolver')", $blockDependencyClassHelperSource);
        $this->assertStringNotContainsString("'arrayAdducer' => \$container->get('array_adducer')", $blockBaseSource);
        $this->assertStringContainsString('private function shortClassName(object|string $object): string', $blockBaseSource);
        $this->assertStringContainsString('$this->shortClassName($class)', $blockBaseSource);
        $this->assertStringNotContainsString('get_class_name(', $blockBaseSource);
        $this->assertStringContainsString("'arrayAdducer' => \$this->arrayAdducer()", $tabSource);
        $this->assertStringContainsString("'recursiveMerger' => \$this->recursiveMerger()", $tabSource);
        $this->assertStringContainsString("'arrayValueReader' => \$this->arrayValueReader()", $tabSource);
        $this->assertStringContainsString("'arrayLikeChecker' => \$this->arrayLikeChecker()", $tabSource);
        $this->assertStringContainsString("'shortClassNameResolver' => \$this->shortClassNameResolver()", $tabSource);
        $this->assertStringContainsString('static fn(mixed $value): array => \adduceToArray($value)', $tabFactorySource);
        $this->assertStringContainsString('static fn(mixed ...$values): mixed => \array_merge_recursive_alt(...$values)', $tabFactorySource);
        $this->assertStringContainsString('static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => \array_val($array, $key, $default)', $tabFactorySource);
        $this->assertStringContainsString('static fn(mixed $value): bool => \is_array_alt($value)', $tabFactorySource);
        $this->assertStringContainsString('static fn(object|string $object): string => \get_class_name($object) ?? (is_object($object) ? get_class($object) : $object)', $tabFactorySource);
        $this->assertStringContainsString('service_id::ARRAY_ADDUCER', $supportRegistrarSource);
        $this->assertStringContainsString('service_id::RECURSIVE_MERGER', $supportRegistrarSource);
        $this->assertStringContainsString('service_id::ARRAY_VALUE_READER', $supportRegistrarSource);
    }

    public function testTabServiceArrayOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/tab.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/tab_service_factory.php');
        $supportRegistrarSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_support_service_registrar.php');

        $this->assertIsString($source);
        $this->assertIsString($factorySource);
        $this->assertIsString($supportRegistrarSource);
        $this->assertStringContainsString('protected function arrayValueReader(): callable', $source);
        $this->assertStringContainsString('$arrayValueReader = $this->arrayValueReader();', $source);
        $this->assertStringNotContainsString('array_val(', $source);
        $this->assertStringNotContainsString('is_array_alt(', $source);
        $this->assertStringContainsString('static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => \array_val($array, $key, $default)', $factorySource);
        $this->assertStringContainsString('service_id::ARRAY_VALUE_READER', $supportRegistrarSource);
    }

    public function testTabUrlMakerArrayOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/tab/delegate/urlMaker.php');
        $tabSource = file_get_contents(dirname(__DIR__, 2) . '/core/service/tab.php');
        $delegateFactorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/tab_delegate_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($tabSource);
        $this->assertIsString($delegateFactorySource);
        $this->assertStringContainsString('private function arrayValueReader(): callable', $source);
        $this->assertStringNotContainsString('array_val(', $source);
        $this->assertStringContainsString('$this->arrayValueReader()', $tabSource);
        $this->assertStringContainsString('callable $arrayValueReader', $delegateFactorySource);
        $this->assertStringContainsString('$arrayValueReader', $delegateFactorySource);
    }

    public function testViewRouterArrayOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/view/router.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/view_router_factory.php');
        $supportRegistrarSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_support_service_registrar.php');
        $viewKeeperDependencySource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_support_view_keeper_factory_registrar_dependencies.php');
        $arrayAdducerDependencySource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_support_array_adducer_registrar_dependencies.php');

        $this->assertIsString($source);
        $this->assertIsString($factorySource);
        $this->assertIsString($supportRegistrarSource);
        $this->assertIsString($viewKeeperDependencySource);
        $this->assertIsString($arrayAdducerDependencySource);
        $this->assertStringContainsString('private \Closure $keeperFactory;', $source);
        $this->assertStringContainsString('private \Closure $blockExceptionFactory;', $source);
        $this->assertStringContainsString('private \Closure $arrayAdducer;', $source);
        $this->assertStringContainsString('$this->keeperFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->blockExceptionFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->arrayAdducer = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('private function keeperFactory(): callable', $source);
        $this->assertStringContainsString('private function blockExceptionFactory(): callable', $source);
        $this->assertStringContainsString('private function arrayAdducer(): callable', $source);
        $this->assertStringContainsString('if (!isset($this->keeperFactory)) {', $source);
        $this->assertStringContainsString('if (!isset($this->blockExceptionFactory)) {', $source);
        $this->assertStringContainsString('if (!isset($this->arrayAdducer)) {', $source);
        $this->assertStringNotContainsString('adduceToArray(', $source);
        $this->assertStringNotContainsString('private $keeperFactory;', $source);
        $this->assertStringNotContainsString('private $blockExceptionFactory;', $source);
        $this->assertStringNotContainsString('private $arrayAdducer;', $source);
        $this->assertStringNotContainsString('$this->keeperFactory = $keeperFactory === null ? null : \Closure::fromCallable($keeperFactory);', $source);
        $this->assertStringNotContainsString('$this->blockExceptionFactory = $blockExceptionFactory === null ? null : \Closure::fromCallable($blockExceptionFactory);', $source);
        $this->assertStringNotContainsString('$this->arrayAdducer = $arrayAdducer === null ? null : \Closure::fromCallable($arrayAdducer);', $source);
        $this->assertStringContainsString('private \Closure $arrayAdducer;', $factorySource);
        $this->assertStringContainsString('static fn(mixed $value): array => \adduceToArray($value)', $factorySource);
        $this->assertStringContainsString('$dependencies->viewKeeperFactory()', $supportRegistrarSource);
        $this->assertStringContainsString('$dependencies->arrayAdducer()', $supportRegistrarSource);
        $this->assertStringContainsString('$this->container->get(service_id::VIEW_KEEPER_FACTORY)', $viewKeeperDependencySource);
        $this->assertStringContainsString('$this->container->get(service_id::ARRAY_ADDUCER)', $arrayAdducerDependencySource);
    }

    public function testTemplateServiceLayerIsRemoved(): void
    {
        $root = dirname(__DIR__, 2);
        $creatorSource = file_get_contents($root . '/core/di/application_content_service_creator.php');
        $registrarSource = file_get_contents($root . '/core/di/application_content_service_registrar.php');

        $this->assertIsString($creatorSource);
        $this->assertIsString($registrarSource);
        foreach ([
            '/core/service/template.php',
            '/core/service/template/type/base.php',
            '/core/service/template/type/image.php',
            '/core/service/template/parser/base.php',
            '/core/service/template/parser/main.php',
            '/core/service/template/parser/image.php',
            '/core/di/template_service_factory.php',
            '/core/di/template_type_factory.php',
            '/core/di/template_exception_factory.php',
            '/core/exception/template/fatal.php',
        ] as $path) {
            $this->assertFileDoesNotExist($root . $path);
        }
        $this->assertStringNotContainsString('createTemplateService', $creatorSource);
        $this->assertStringNotContainsString('template_exception_factory', $creatorSource);
        $this->assertStringNotContainsString("'template'", $registrarSource);
    }


    public function testFormServiceArrayOperationsAreRemovedWithFormService(): void
    {
        $creatorSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_pager_service_creator.php');
        $supportRegistrarSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_support_service_registrar.php');

        $this->assertIsString($creatorSource);
        $this->assertIsString($supportRegistrarSource);
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/service/form.php');
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/di/form_service_factory.php');
        $this->assertStringNotContainsString('createFormService', $creatorSource);
        $this->assertStringNotContainsString("'form'", $creatorSource);
        $this->assertStringContainsString('service_id::CLASS_NAME_RESOLVER', $supportRegistrarSource);
    }

    public function testFormValidatorLayerIsRemovedWithFormService(): void
    {
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/service/form/validator/base.php');
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/service/form/validator/number.php');
    }

    public function testDatabaseEngineBaseDoesNotOwnLegacyMysqlGlobalConstants(): void
    {
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/service/database/base.php');
    }

    public function testMysqlDatabaseEnginesUseInjectedResultTypeConstants(): void
    {
        foreach ([
            'core/service/database/mysql.php',
            'core/service/database/mysqlImproved.php',
            'core/service/database/mysqlPdo.php',
        ] as $relativePath) {
            $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/' . $relativePath);
        }
    }

    public function testEmailEngineFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/di/email_engine_factory.php');
    }

    public function testEmailServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/di/email_service_factory.php');
    }

    public function testEmailServiceDependenciesAreInjectedWithoutNullableClosureState(): void
    {
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/service/email.php');
    }

    public function testEmailPlainTemplateFilesystemOperationsAreInjected(): void
    {
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/service/email.php');
    }

    public function testEmailTemplateFileStorageConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\email_template_file_storage\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
            ],
            'Email template file storage concrete construction outside application composition root'
        );
    }

    public function testEntityDesignerFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/entity_designer_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('?callable $classNameResolver = null', $source);
        $this->assertStringContainsString('$arguments = [$entity];', $source);
        $this->assertStringContainsString('$arguments[] = $classNameResolver;', $source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
    }

    public function testEntityEncapsulantFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/entity_encapsulant_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
    }


    public function testEntityDescriptionFileStorageConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\entity_description_file_storage\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
            ],
            'Entity description file storage concrete construction outside application composition root'
        );
    }

    public function testEntityFileDiscoveryConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\entity_file_discovery\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
            ],
            'Entity file discovery concrete construction outside application composition root'
        );
    }

    public function testRootHtmlFilesystemOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/block/root/html.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private ?object $rootHtmlFileStorage = null;', $source);
        $this->assertStringContainsString('$this->rootHtmlFileStorage()->isFile($filePath)', $source);
        $this->assertStringContainsString('$this->rootHtmlFileStorage()->read($jsFilePath)', $source);
        $this->assertStringContainsString('private \Closure $shortClassNameResolver;', $source);
        $this->assertStringContainsString('private \Closure $arrayLikeChecker;', $source);
        $this->assertStringContainsString('$this->shortClassName($main)', $source);
        $this->assertStringContainsString('$this->isArrayLike($meta)', $source);
        $this->assertStringNotContainsString('get_class_name(', $source);
        $this->assertStringNotContainsString('is_array_alt(', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:is_file|is_readable|file_get_contents)\s*\(/',
            $source
        );
    }

    public function testRootHtmlFileStorageConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\root_html_file_storage\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
            ],
            'Root HTML file storage concrete construction outside application composition root'
        );
    }

    public function testBlockBaseFilesystemOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/block/base.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private ?object $fileStorage = null;', $source);
        $this->assertStringContainsString('$this->fileStorage()->isFile($templatePath)', $source);
        $this->assertStringContainsString('$this->fileStorage()->exists($currentPath . \'meta.php\')', $source);
        $this->assertStringContainsString('private function createBlockFatalException(', $source);
        $this->assertStringContainsString('($this->blockExceptionFactory)($class, $this, $logErrMsg, $code, $previous);', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\block\fatal as fatalException;', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:is_file|file_exists)\s*\(/',
            $source
        );
    }

    public function testBlockFileStorageConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\block_file_storage\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
            ],
            'Block file storage concrete construction outside application composition root'
        );
    }

    public function testMetaMakerFilesystemOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/base/meta/maker.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/meta_maker_factory.php');
        $registrarSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_support_service_registrar.php');
        $recursiveMergerDependencySource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_support_recursive_merger_registrar_dependencies.php');
        $arrayAdducerDependencySource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_support_array_adducer_registrar_dependencies.php');
        $classNameResolverDependencySource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_support_class_name_resolver_registrar_dependencies.php');

        $this->assertIsString($source);
        $this->assertIsString($factorySource);
        $this->assertIsString($registrarSource);
        $this->assertIsString($recursiveMergerDependencySource);
        $this->assertIsString($arrayAdducerDependencySource);
        $this->assertIsString($classNameResolverDependencySource);
        $this->assertStringContainsString('private ?object $fileStorage = null;', $source);
        $this->assertStringContainsString('$this->fileStorage()->exists($folderPath)', $source);
        $this->assertStringContainsString('$this->fileStorage()->exists($metaPath)', $source);
        $this->assertStringContainsString('private \Closure $recursiveMerger;', $source);
        $this->assertStringContainsString('private \Closure $arrayAdducer;', $source);
        $this->assertStringContainsString('private \Closure $classNameResolver;', $source);
        $this->assertStringContainsString('$this->recursiveMerger = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->arrayAdducer = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->classNameResolver = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('if (!isset($this->recursiveMerger)) {', $source);
        $this->assertStringContainsString('if (!isset($this->arrayAdducer)) {', $source);
        $this->assertStringContainsString('if (!isset($this->classNameResolver)) {', $source);
        $this->assertStringContainsString('private function recursiveMerger(): callable', $source);
        $this->assertStringContainsString('private function arrayAdducer(): callable', $source);
        $this->assertStringContainsString('private function className(object $object): string', $source);
        $this->assertStringNotContainsString('array_merge_recursive_alt(', $source);
        $this->assertStringNotContainsString('adduceToArray(', $source);
        $this->assertStringNotContainsString('get_class_alt(', $source);
        $this->assertStringNotContainsString('private mixed $recursiveMerger = null;', $source);
        $this->assertStringNotContainsString('private mixed $arrayAdducer = null;', $source);
        $this->assertStringNotContainsString('private mixed $classNameResolver = null;', $source);
        $this->assertStringNotContainsString('$this->recursiveMerger = $recursiveMerger === null ? null : \Closure::fromCallable($recursiveMerger);', $source);
        $this->assertStringNotContainsString('$this->arrayAdducer = $arrayAdducer === null ? null : \Closure::fromCallable($arrayAdducer);', $source);
        $this->assertStringNotContainsString('$this->classNameResolver = $classNameResolver === null ? null : \Closure::fromCallable($classNameResolver);', $source);
        $this->assertStringContainsString('static fn(mixed ...$values): mixed => \array_merge_recursive_alt(...$values)', $factorySource);
        $this->assertStringContainsString('static fn(mixed $value): array => \adduceToArray($value)', $factorySource);
        $this->assertStringContainsString('static fn(object $object): string => \get_class_alt($object) ?? get_class($object)', $factorySource);
        $this->assertStringContainsString('$dependencies->recursiveMerger()', $registrarSource);
        $this->assertStringContainsString('$dependencies->arrayAdducer()', $registrarSource);
        $this->assertStringContainsString('$dependencies->classNameResolver()', $registrarSource);
        $this->assertStringNotContainsString('$container->get(service_id::RECURSIVE_MERGER)', $registrarSource);
        $this->assertStringNotContainsString('$container->get(service_id::ARRAY_ADDUCER)', $registrarSource);
        $this->assertStringNotContainsString('$container->get(service_id::CLASS_NAME_RESOLVER)', $registrarSource);
        $this->assertStringContainsString('$this->container->get(service_id::RECURSIVE_MERGER)', $recursiveMergerDependencySource);
        $this->assertStringContainsString('$this->container->get(service_id::ARRAY_ADDUCER)', $arrayAdducerDependencySource);
        $this->assertStringContainsString('$this->container->get(service_id::CLASS_NAME_RESOLVER)', $classNameResolverDependencySource);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\bfile_exists\s*\(/',
            $source
        );
    }

    public function testMetaFileStorageConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\meta_file_storage\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
            ],
            'Meta file storage concrete construction outside application composition root'
        );
    }

    public function testImageModifyServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/image_modify_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('public function __construct(callable $configuredServiceFactory, ?object $reflectionClassFactory = null)', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testImageModifyStateDependencyIsInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/image_modify.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('Image modify state is not configured for image modify service.', $source);
        $this->assertStringContainsString('$this->state = $state ?? throw new \RuntimeException', $source);
        $this->assertStringNotContainsString('new image_modify_state()', $source);
    }

    public function testImageModifyStateConstructionIsLimitedToCompositionAndTests(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\?fan\\\\core\\\\service\\\\image_modify_state\s*\(|new\s+image_modify_state\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
                'unit/core/service/ImageModifyStateTest.php' => true,
                'unit/core/service/ImageModifyTest.php' => true,
                'unit/core/di/ImageModifyServiceFactoryTest.php' => true,
            ],
            'Image modify state construction outside explicit composition/test boundaries'
        );
    }

    public function testImageModifySourceFileChecksAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/image_modify.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('$this->imageSourceFileStorage()->exists($sourcePath)', $source);
        $this->assertStringContainsString('$this->imageSourceFileStorage()->isReadable($sourcePath)', $source);
        $this->assertStringContainsString('$this->imageSourceFileStorage()->rename(', $source);
        $this->assertStringContainsString('$this->createServiceFatalException(', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:file_exists|is_readable|rename)\s*\(/',
            $source
        );
    }

    public function testImageModifyRuntimeAdaptersAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/image_modify.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('Image resource factory is not configured for image modify service.', $source);
        $this->assertStringContainsString('Image canvas operations are not configured for image modify service.', $source);
        $this->assertStringContainsString('Image output writer is not configured for image modify service.', $source);
        $this->assertStringNotContainsString('new \fan\core\adapter\image_resource_factory()', $source);
        $this->assertStringNotContainsString('new \fan\core\adapter\image_canvas_operations()', $source);
        $this->assertStringNotContainsString('new \fan\core\adapter\image_output_writer()', $source);
    }

    public function testImageServicesArrayOperationsAreInjected(): void
    {
        $modifySource = file_get_contents(dirname(__DIR__, 2) . '/core/service/image_modify.php');
        $drawSource = file_get_contents(dirname(__DIR__, 2) . '/core/service/image_draw.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/image_modify_service_factory.php');
        $creatorSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_utility_service_creator.php');

        $this->assertIsString($modifySource);
        $this->assertIsString($drawSource);
        $this->assertIsString($factorySource);
        $this->assertIsString($creatorSource);
        $this->assertStringContainsString('protected mixed $arrayValueReader = null;', $modifySource);
        $this->assertStringContainsString('protected function arrayValueReader(): callable', $modifySource);
        $this->assertStringNotContainsString('array_val(', $modifySource);
        $this->assertStringContainsString('$this->arrayValueReader()', $drawSource);
        $this->assertStringNotContainsString('array_val(', $drawSource);
        $this->assertStringContainsString('static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => \array_val($array, $key, $default)', $factorySource);
        $this->assertStringContainsString('$utilityDependencies->arrayValueReader()', $creatorSource);
    }

    public function testSpecFileImageRowFilesystemOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/base/model/spec_file/image/row.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private ?object $imageSourceFileStorage = null;', $source);
        $this->assertStringContainsString('$this->imageSourceFileStorage()->isFile($path)', $source);
        $this->assertStringContainsString('$this->imageSourceFileStorage()->isReadable($path)', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:is_file|is_readable)\s*\(/',
            $source
        );
    }

    public function testSpecFileImageRowStateDependencyIsInjected(): void
    {
        $rowSource = file_get_contents(dirname(__DIR__, 2) . '/core/base/model/spec_file/image/row.php');

        $this->assertIsString($rowSource);
        $this->assertStringContainsString('Spec-file image row state is not configured for spec-file image row.', $rowSource);
        $this->assertStringNotContainsString('new row_state()', $rowSource);
    }

    public function testSpecFileImageRowStateConstructionIsLimitedToRuntimeCompositionAndTests(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\?fan\\\\core\\\\base\\\\model\\\\spec_file\\\\image\\\\row_state\s*\(|new\s+row_state\s*\(/',
            [
                'core/factory/bootstrap_runtime_state_defaults_provider_factory.php' => true,
                'core/factory/application_container_factory.php' => true,
                'unit/core/base/model/spec_file/image/RowStateTest.php' => true,
                'unit/core/base/model/spec_file/image/RowTest.php' => true,
                'unit/core/di/EntityServiceFactoryTest.php' => true,
                'unit/core/service/BootstrapRuntimeTest.php' => true,
                'unit/core/service/EntityTest.php' => true,
            ],
            'Spec-file image row state construction outside explicit runtime composition/test boundaries'
        );
    }

    public function testImageSourceFileStorageConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\image_source_file_storage\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
            ],
            'Image source file storage construction outside application composition root'
        );
    }

    public function testMatcherItemComponentFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/matcher_item_component_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
    }

    public function testMatcherServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/matcher_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('public function __construct(callable $configuredServiceFactory, ?object $reflectionClassFactory = null)', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testModelEntityFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/model_entity_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
    }

    public function testModelEntityFactoryUsesNamedEntityDependenciesInsteadOfPositionalTail(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/model_entity_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('use fan\core\base\model\entity_dependencies;', $source);
        $this->assertStringContainsString('$entityDependencies = new entity_dependencies(', $source);
        $this->assertStringContainsString('$entityDependencies' . "\n" . '        ]);', $source);
        $factoryCall = strstr($source, 'return ($this->configuredServiceFactory)(');
        $this->assertIsString($factoryCall);
        foreach ([
            '$entityIdDecoder',
            '$entityLookup',
            '$designerFactory',
            '$descriptionProvider',
            '$rowDependenciesProvider',
            '$fileDataRowDependenciesProvider',
            '$specFileImageRowDependenciesProvider',
            '$relatedEntityRowFactory',
        ] as $positionalDependency) {
            $this->assertStringNotContainsString($positionalDependency, $factoryCall);
        }
    }

    public function testBaseModelEntityFatalExceptionConstructionIsInjected(): void
    {
        $entitySource = file_get_contents(dirname(__DIR__, 2) . '/core/base/model/entity.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/model_entity_factory.php');
        $exceptionFactorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/model_entity_exception_factory.php');

        $this->assertIsString($entitySource);
        $this->assertIsString($factorySource);
        $this->assertIsString($exceptionFactorySource);
        $this->assertStringContainsString('private mixed $modelEntityExceptionFactory = null;', $entitySource);
        $this->assertStringContainsString('private function createModelEntityFatalException(', $entitySource);
        $this->assertStringContainsString('public function createDescriptionFatalException(', $entitySource);
        $this->assertStringContainsString('return $this->createModelEntityFatalException($message, $code, $previous);', $entitySource);
        $this->assertStringContainsString('public function createRowsetFatalException(', $entitySource);
        $this->assertStringContainsString('public function createRequestFatalException(', $entitySource);
        $this->assertStringContainsString('public function createDesignerFatalException(', $entitySource);
        $this->assertStringContainsString('private \Closure $namespaceResolver;', $entitySource);
        $this->assertStringContainsString('callable|entity_dependencies|null $namespaceResolver = null', $entitySource);
        $this->assertStringContainsString('$this->namespaceResolver = $this->defaultNamespaceResolver();', $entitySource);
        $this->assertStringContainsString('$ns     = $this->namespaceName($this, 2);', $entitySource);
        $this->assertStringContainsString('private function namespaceName(object|string $object, int $depth = 1): string', $entitySource);
        $this->assertStringContainsString('private function namespaceResolver(): callable', $entitySource);
        $this->assertStringContainsString('private function defaultNamespaceResolver(): \Closure', $entitySource);
        $this->assertStringNotContainsString('private ?\Closure $namespaceResolver', $entitySource);
        $this->assertStringNotContainsString('$this->namespaceResolver === null', $entitySource);
        $this->assertStringContainsString('$this->modelEntityExceptionFactory', $factorySource);
        $this->assertStringContainsString('new entity_dependencies(', $factorySource);
        $this->assertStringContainsString('$namespaceResolver', $factorySource);
        $this->assertStringContainsString('final class model_entity_exception_factory', $exceptionFactorySource);
        $this->assertStringContainsString('return ($this->configuredServiceFactory)($exceptionClass, [', $exceptionFactorySource);
        $this->assertStringNotContainsString('get_ns_name(', $entitySource);
        $this->assertStringNotContainsString('new fatalException', $entitySource);
        $this->assertStringNotContainsString('use fan\project\exception\model\entity\fatal as fatalException;', $entitySource);
    }

    public function testBaseModelRowFatalExceptionConstructionIsInjected(): void
    {
        $rowSource = file_get_contents(dirname(__DIR__, 2) . '/core/base/model/row.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/model_row_factory.php');
        $exceptionFactorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/model_row_exception_factory.php');

        $this->assertIsString($rowSource);
        $this->assertIsString($factorySource);
        $this->assertIsString($exceptionFactorySource);
        $this->assertStringContainsString('private mixed $modelRowExceptionFactory = null;', $rowSource);
        $this->assertStringContainsString('protected function createModelRowFatalException(', $rowSource);
        $this->assertStringContainsString('$this->modelRowExceptionFactory', $factorySource);
        $this->assertStringContainsString('final class model_row_exception_factory', $exceptionFactorySource);
        $this->assertStringContainsString('return ($this->configuredServiceFactory)($exceptionClass, [', $exceptionFactorySource);
        $this->assertStringNotContainsString('new fatalException', $rowSource);
        $this->assertStringNotContainsString('use fan\project\exception\model\entity\fatal as fatalException;', $rowSource);
    }

    public function testModelRequestFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/model_request_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private object $fileStorage', $source);
        $this->assertStringContainsString('[$modelEntity, $reflector, $this->fileStorage]', $source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
    }

    public function testModelRequestFilesystemOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/base/model/request.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private ?object $fileStorage = null;', $source);
        $this->assertStringContainsString('$this->fileStorage()->read($fileName)', $source);
        $this->assertStringContainsString('$this->fileStorage()->exists($fileName)', $source);
        $this->assertStringContainsString('private function createRequestFatalException(', $source);
        $this->assertStringContainsString('$this->getEntity()->createRequestFatalException($message, $code, $previous)', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\model\entity\fatal as fatalException;', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:file_exists|file_get_contents)\s*\(/',
            $source
        );
    }

    public function testModelRowsetUsesEntityFatalExceptionFactoryBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/base/model/rowset.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private function createRowsetFatalException(', $source);
        $this->assertStringContainsString('$this->getEntity()->createRowsetFatalException($message, $code, $previous)', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\model\entity\fatal as fatalException;', $source);
    }

    public function testModelRequestFileStorageConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\model_request_file_storage\s*\(/',
            [
                'core/di/application_adapter_registry.php' => true,
            ],
            'Model request file storage concrete construction outside application composition root'
        );
    }

    public function testModelRowFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/model_row_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
    }

    public function testModelRowsetFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/model_rowset_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
    }

    public function testPlainControllerFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/plain_controller_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
    }

    public function testPlainServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/plain_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('public function __construct(callable $configuredServiceFactory, ?object $reflectionClassFactory = null)', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testSessionEngineFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/session_engine_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private object $nativeSession', $source);
        $this->assertStringContainsString('private ?object $pearHttpSession = null', $source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('?object $reflectionClassFactory = null', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('new \fan\core\adapter\pear_http_session()', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testPearSessionHttpCallsAreLimitedToAdapter(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/\\\\?HTTP_Session::(?:setContainer|useCookies|start|id|get|set|clear|destroy)\s*\(/',
            [],
            'Raw PEAR HTTP session static calls'
        );

        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/session/pear.php');
        $adapterSource = file_get_contents(dirname(__DIR__, 2) . '/core/adapter/pear_http_session.php');

        $this->assertIsString($source);
        $this->assertIsString($adapterSource);
        $this->assertStringContainsString('private ?object $httpSession = null;', $source);
        $this->assertStringContainsString('$this->httpSession()', $source);
        $this->assertStringContainsString('HTTP session adapter is not configured for PEAR session engine.', $source);
        $this->assertStringNotContainsString('HTTP_Session::', $source);
        $this->assertStringContainsString('$className::$method(...$arguments)', $adapterSource);
    }

    public function testNativeSessionCallsAreLimitedToAdapter(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:session_start|session_id|session_name|session_status|session_destroy)\s*\(/',
            [
                'core/adapter/native_session.php' => true,
            ],
            'Native session calls outside adapter'
        );

        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/session/inbuilt.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private ?object $nativeSession = null;', $source);
        $this->assertStringContainsString('$this->nativeSession()', $source);
    }

    public function testNativeSessionAdapterConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\native_session\s*\(/',
            [
                'core/factory/application_registry_defaults_provider_factory.php' => true,
            ],
            'Native session adapter construction outside application composition root'
        );
    }

    public function testPearSessionAdapterConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\pear_http_session\s*\(/',
            [
                'core/factory/application_registry_defaults_provider_factory.php' => true,
            ],
            'PEAR HTTP session adapter construction outside application composition root'
        );
    }

    public function testSessionServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/session_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('public function __construct(callable $configuredServiceFactory, ?object $reflectionClassFactory = null)', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testSessionServiceArrayOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/session.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/session_service_factory.php');
        $creatorSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_session_service_creator.php');

        $this->assertIsString($source);
        $this->assertIsString($factorySource);
        $this->assertIsString($creatorSource);
        $this->assertStringContainsString('$arrayValueReader = $this->arrayValueReader();', $source);
        $this->assertStringNotContainsString('array_val(', $source);
        $this->assertStringContainsString('static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => \array_val($array, $key, $default)', $factorySource);
        $this->assertStringContainsString('$sessionDependencies->arrayValueReader()', $creatorSource);
    }

    public function testSoapServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/soap_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
    }

    public function testSoapWsdlFileChecksAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/soap.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private ?object $wsdlFileStorage = null;', $source);
        $this->assertStringContainsString('$this->wsdlFileStorage()->exists($wsdlFile_Full)', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\bfile_exists\s*\(/',
            $source
        );
    }

    public function testSoapArrayOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/soap.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/soap_service_factory.php');
        $creatorSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_utility_service_creator.php');

        $this->assertIsString($source);
        $this->assertIsString($factorySource);
        $this->assertIsString($creatorSource);
        $this->assertStringContainsString('protected function arrayValueReader(): callable', $source);
        $this->assertStringNotContainsString('array_val(', $source);
        $this->assertStringContainsString('static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => \array_val($array, $key, $default)', $factorySource);
        $this->assertStringContainsString('$utilityDependencies->arrayValueReader()', $creatorSource);
    }

    public function testSoapRuntimeObjectsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/soap.php');
        $factorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/soap_service_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($factorySource);
        $this->assertStringContainsString('private \Closure $arrayValueReader;', $source);
        $this->assertStringContainsString('private \Closure $soapHeaderFactory;', $source);
        $this->assertStringContainsString('private \Closure $soapClientFactory;', $source);
        $this->assertStringContainsString('private \Closure $soapVarFactory;', $source);
        $this->assertStringContainsString('private \Closure $domDocumentFactory;', $source);
        $this->assertStringContainsString('private \Closure $streamContextFactory;', $source);
        $this->assertStringContainsString('$this->arrayValueReader = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->soapHeaderFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->soapClientFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->soapVarFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->domDocumentFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->streamContextFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('if (!isset($this->arrayValueReader)) {', $source);
        $this->assertStringContainsString('if (!isset($this->soapHeaderFactory)) {', $source);
        $this->assertStringContainsString('if (!isset($this->soapClientFactory)) {', $source);
        $this->assertStringContainsString('if (!isset($this->soapVarFactory)) {', $source);
        $this->assertStringContainsString('if (!isset($this->domDocumentFactory)) {', $source);
        $this->assertStringContainsString('if (!isset($this->streamContextFactory)) {', $source);
        $this->assertStringNotContainsString('private mixed $arrayValueReader = null;', $source);
        $this->assertStringNotContainsString('private mixed $soapHeaderFactory = null;', $source);
        $this->assertStringNotContainsString('private mixed $soapClientFactory = null;', $source);
        $this->assertStringNotContainsString('private mixed $soapVarFactory = null;', $source);
        $this->assertStringNotContainsString('private mixed $domDocumentFactory = null;', $source);
        $this->assertStringNotContainsString('private mixed $streamContextFactory = null;', $source);
        $this->assertStringNotContainsString('$this->soapHeaderFactory = $soapHeaderFactory === null ? null : \Closure::fromCallable($soapHeaderFactory);', $source);
        $this->assertStringNotContainsString('$this->soapClientFactory = $soapClientFactory === null ? null : \Closure::fromCallable($soapClientFactory);', $source);
        $this->assertStringNotContainsString('$this->soapVarFactory = $soapVarFactory === null ? null : \Closure::fromCallable($soapVarFactory);', $source);
        $this->assertStringNotContainsString('$this->domDocumentFactory = $domDocumentFactory === null ? null : \Closure::fromCallable($domDocumentFactory);', $source);
        $this->assertStringNotContainsString('$this->streamContextFactory = $streamContextFactory === null ? null : \Closure::fromCallable($streamContextFactory);', $source);
        $this->assertStringContainsString('$this->createSoapHeader($nameSpace, $name, $data)', $source);
        $this->assertStringContainsString('$this->createSoapClient($wsdlFile_Full, $param && is_array($param) ? $param : null)', $source);
        $this->assertStringContainsString('$this->createSoapVar(', $source);
        $this->assertStringContainsString('$this->createDomDocument()', $source);
        $this->assertStringContainsString('$this->createStreamContext([', $source);
        $this->assertStringNotContainsString('new \SoapHeader', $source);
        $this->assertStringNotContainsString('new \SoapClient', $source);
        $this->assertStringNotContainsString('new \SoapVar', $source);
        $this->assertStringNotContainsString('new \DOMDocument', $source);
        $this->assertStringNotContainsString('stream_context_create(', $source);
        $this->assertStringContainsString('new \SoapHeader(', $factorySource);
        $this->assertStringContainsString('new \SoapClient(', $factorySource);
        $this->assertStringContainsString('new \SoapVar(', $factorySource);
        $this->assertStringContainsString('new \DOMDocument()', $factorySource);
        $this->assertStringContainsString('\stream_context_create($options)', $factorySource);
    }

    public function testTabDelegateFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/tab_delegate_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
    }

    public function testTabServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/tab_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('?object $reflectionClassFactory = null', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
        $this->assertStringNotContainsString('new \fan\project\view\definer(', $source);
    }

    public function testTabViewDefinerConstructionIsLimitedToExplicitFactory(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\project\\\\view\\\\definer\s*\(/',
            [
                'core/factory/view_definer_factory.php' => true,
            ],
            'Tab view definer concrete construction outside explicit factory'
        );
    }

    public function testModelReverseExceptionConstructionIsNotDoneDirectly(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\(?:fan\\\\)?project\\\\exception\\\\model\\\\reverse\s*\(/',
            [],
            'Model reverse exception concrete construction outside injected exception factory'
        );
    }

    public function testBlockMetaMakerConstructionIsLimitedToExplicitFactory(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\project\\\\base\\\\meta\\\\maker\s*\(/',
            [
                'core/factory/meta_maker_factory.php' => true,
            ],
            'Block meta maker concrete construction outside explicit factory'
        );
    }

    public function testDelayedMetaConstructionIsLimitedToExplicitFactory(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\project\\\\base\\\\meta\\\\delayed\s*\(/',
            [
                'core/factory/delayed_meta_factory.php' => true,
            ],
            'Delayed meta concrete construction outside explicit factory'
        );
    }

    public function testTransferExceptionConstructionIsLimitedToExplicitFactory(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\project\\\\base\\\\transfer\\\\(?:out|transfer_int|sham)\s*\(/',
            [
                'core/factory/transfer_exception_factory.php' => true,
            ],
            'Transfer exception concrete construction outside explicit factory'
        );
    }

    public function testViewRouterConstructionIsLimitedToExplicitFactory(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\project\\\\view\\\\router\\\\(?:simple|html|json|loader)\s*\(/',
            [
                'core/factory/view_router_factory.php' => true,
            ],
            'View router concrete construction outside explicit factory'
        );
    }

    public function testViewParsersDoNotOwnViewRouterFactoryFallback(): void
    {
        foreach ([
            'core/view/parser.php',
            'core/view/parser/html.php',
            'core/view/parser/json.php',
            'core/view/parser/loader.php',
        ] as $relativePath) {
            $source = file_get_contents(dirname(__DIR__, 2) . '/' . $relativePath);

            $this->assertIsString($source);
            $this->assertStringNotContainsString('new \fan\core\di\view_router_factory', $source, $relativePath);
            $this->assertStringContainsString('static::viewRouterFactory($viewRouterFactory)', $source, $relativePath);
        }
    }

    public function testViewKeeperConstructionIsLimitedToExplicitFactories(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\project\\\\view\\\\keeper(?:\\\\loader\\\\(?:json|text))?\s*\(/',
            [
                'core/factory/view_keeper_factory.php' => true,
                'core/factory/view_loader_json_keeper_factory.php' => true,
                'core/factory/view_loader_text_keeper_factory.php' => true,
            ],
            'View keeper concrete construction outside explicit factories'
        );
    }

    public function testTabAliasFileChecksAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/service/tab.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('protected ?object $aliasFileStorage = null;', $source);
        $this->assertStringContainsString('$this->aliasFileStorage()->isReadable($aliasFile)', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\bis_readable\s*\(/',
            $source
        );
    }

    public function testTabAliasFileStorageConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\tab_alias_file_storage\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
            ],
            'Tab alias file storage construction outside application composition root'
        );
    }

    public function testSoapWsdlFileStorageConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\soap_wsdl_file_storage\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
            ],
            'SOAP WSDL file storage construction outside application composition root'
        );
    }

    public function testTabViewParserFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/tab_view_parser_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
    }

    public function testTemplateServiceFactoriesAreRemovedFromServiceFactoryGraph(): void
    {
        $root = dirname(__DIR__, 2);
        $providerSource = file_get_contents($root . '/core/factory/service_factory_registry.php');
        $bundleSource = file_get_contents($root . '/core/factory/application_service_factory_bundle.php');
        $optionsSource = file_get_contents($root . '/core/factory/application_service_factory_options.php');

        $this->assertIsString($providerSource);
        $this->assertIsString($bundleSource);
        $this->assertIsString($optionsSource);
        foreach (['templateTypeFactory', 'templateServiceFactory'] as $name) {
            $this->assertStringNotContainsString($name, $providerSource);
            $this->assertStringNotContainsString($name, $bundleSource);
            $this->assertStringNotContainsString($name, $optionsSource);
        }
    }

    public function testTemplateFileStorageConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\template_file_storage\s*\(/',
            [
                'core/factory/application_container_factory.php' => true,
            ],
            'Template file storage concrete construction outside application composition root'
        );
    }

    public function testCompiledTemplateLoaderStateConstructionIsLimitedToCompositionAndTests(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/adapter/compiled_template_loader.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('compiled_template_loader_state $state,', $source);
        $this->assertStringContainsString('?callable $classExists = null', $source);
        $this->assertStringContainsString('?callable $isReadable = null', $source);
        $this->assertStringContainsString('?callable $fileLoader = null', $source);
        $this->assertStringContainsString('private \Closure $classExists;', $source);
        $this->assertStringContainsString('private \Closure $isReadable;', $source);
        $this->assertStringContainsString('private \Closure $fileLoader;', $source);
        $this->assertStringContainsString('$this->classExists = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->isReadable = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->fileLoader = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('return ($this->classExists)($className, true);', $source);
        $this->assertStringContainsString('$fileLoader($path);', $source);
        $this->assertStringNotContainsString('new compiled_template_loader_state()', $source);
        $this->assertStringNotContainsString('return class_exists($className, true);', $source);
        $this->assertStringNotContainsString('&& is_readable($path)', $source);
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\?fan\\\\core\\\\adapter\\\\compiled_template_loader_state\s*\(|new\s+compiled_template_loader_state\s*\(/',
            [
                'core/di/application_compiled_template_adapter_defaults_provider.php' => true,
                'unit/core/adapter/CompiledTemplateLoaderStateTest.php' => true,
                'unit/core/adapter/CompiledTemplateLoaderTest.php' => true,
            ],
            'Compiled template loader state construction outside explicit composition/test boundaries'
        );
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\?fan\\\\core\\\\adapter\\\\compiled_template_loader\s*\(|new\s+compiled_template_loader\s*\(/',
            [
                'core/di/application_compiled_template_adapter_defaults_provider.php' => true,
                'unit/core/adapter/CompiledTemplateLoaderTest.php' => true,
            ],
            'Compiled template loader construction outside explicit composition/test boundaries'
        );
    }

    public function testTimerProgramFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/timer_program_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
    }

    public function testTimerServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/timer_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('public function __construct(callable $configuredServiceFactory, ?object $reflectionClassFactory = null)', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testUserEngineFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/user_engine_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
    }

    public function testUserServiceFactoryDoesNotOwnConfiguredServiceDefault(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/factory/user_service_factory.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
        $this->assertStringNotContainsString('new configured_service_factory()', $source);
        $this->assertStringNotContainsString('private ?object $reflectionClassFactory = null;', $source);
        $this->assertStringNotContainsString('public function __construct(callable $configuredServiceFactory, ?object $reflectionClassFactory = null)', $source);
        $this->assertStringNotContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory->create($className)', $source);
        $this->assertStringNotContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $source);
    }

    public function testRawEnvironmentAccessIsLimitedToExplicitBoundaries(): void
    {
        $allowedFiles = [
            'core/adapter/request_input_native_environment.php' => true,
        ];
        $matches = [];

        foreach ($this->productionPhpFiles() as $file) {
            $relativePath = $this->relativePath($file);
            if (isset($allowedFiles[$relativePath])) {
                continue;
            }

            $source = file_get_contents($file);
            $this->assertIsString($source);
            $codeOnly = $this->codeWithoutCommentsAndStrings($source);
            if (preg_match('/\$_(?:GET|POST|REQUEST|SERVER|SESSION|COOKIE|FILES)\b|\$GLOBALS\b/', $codeOnly) === 1) {
                $matches[] = $relativePath;
            }
        }

        $this->assertSame([], $matches, 'Raw environment access outside explicit boundaries found in: ' . implode(', ', $matches));
    }

    public function testVariableFileLoadingIsLimitedToExplicitBoundaries(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/(?:require|require_once|include|include_once)\s+\$(?:file|path)\b|return\s+(?:require|require_once|include|include_once)\s+\$(?:file|path)\b/',
            [
                'core/adapter/bootstrap_loader_file_storage.php' => true,
                'core/adapter/compiled_template_loader.php' => true,
                'core/adapter/php_array_file.php' => true,
                'core/adapter/php_template_file.php' => true,
                'core/adapter/project_tool_loader.php' => true,
                'core/adapter/zend_autoloader.php' => true,
            ],
            'Variable file loading outside explicit boundaries'
        );
    }

    public function testPhpArrayFileStaticLoadIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/\bphp_array_file::load\s*\(/',
            [],
            'Direct PHP-array adapter loading outside explicit loader boundary'
        );
    }

    public function testPhpTemplateFileRendererFilesystemOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/adapter/php_template_file.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private \Closure $isReadable;', $source);
        $this->assertStringContainsString('private \Closure $templateExecutor;', $source);
        $this->assertStringContainsString('public function __construct(?callable $isReadable = null, ?callable $templateExecutor = null)', $source);
        $this->assertStringContainsString('return (new self())->renderFile($path, $variables);', $source);
        $this->assertStringContainsString('return ($this->templateExecutor)($path, $variables);', $source);
        $this->assertStringNotContainsString('if (!is_readable($path))', $source);
    }

    public function testTemplateRenderingIsLimitedToExplicitRenderer(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/\bphp_template_file::render\s*\(/',
            [],
            'Direct template rendering outside explicit renderers'
        );
    }

    public function testProjectToolLoadingIsLimitedToExplicitAdapters(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/\bproject_tool_loader::load\s*\(/',
            [],
            'Direct project tool loading outside explicit defaults providers'
        );
    }

    public function testBootstrapStaticCallsAreLimitedToCompatibilityBoundaries(): void
    {
        $matches = [];

        foreach ($this->productionPhpFiles() as $file) {
            $relativePath = $this->relativePath($file);

            $source = file_get_contents($file);
            $this->assertIsString($source);
            if (preg_match('/\\\\bootstrap::/', $this->codeWithoutCommentsAndStrings($source)) === 1) {
                $matches[] = $relativePath;
            }
        }

        $this->assertSame([], $matches, 'Direct bootstrap static calls outside compatibility boundaries found in: ' . implode(', ', $matches));
    }

    public function testNativeErrorHandlerRegistrationIsLimitedToExplicitBoundaries(): void
    {
        $allowedFiles = [
            'core/adapter/warning_capture.php' => true,
            'core/runtime/error_handler_registrar.php' => true,
        ];
        $matches = [];

        foreach ($this->productionPhpFiles() as $file) {
            $relativePath = $this->relativePath($file);
            if (isset($allowedFiles[$relativePath])) {
                continue;
            }

            $source = file_get_contents($file);
            $this->assertIsString($source);
            if (preg_match('/\b(?:set_error_handler|restore_error_handler)\s*\(/', $this->codeWithoutCommentsAndStrings($source)) === 1) {
                $matches[] = $relativePath;
            }
        }

        $this->assertSame([], $matches, 'Native error handler registration outside explicit boundaries found in: ' . implode(', ', $matches));
    }

    public function testNativeIniAccessIsLimitedToExplicitBoundaries(): void
    {
        $allowedFiles = [
            'core/runtime/php_runtime_settings.php' => true,
        ];
        $matches = [];

        foreach ($this->productionPhpFiles() as $file) {
            $relativePath = $this->relativePath($file);
            if (isset($allowedFiles[$relativePath])) {
                continue;
            }

            $source = file_get_contents($file);
            $this->assertIsString($source);
            if (preg_match('/\b(?:ini_get|ini_set)\s*\(/', $this->codeWithoutCommentsAndStrings($source)) === 1) {
                $matches[] = $relativePath;
            }
        }

        $this->assertSame([], $matches, 'Native ini access outside explicit boundaries found in: ' . implode(', ', $matches));
    }

    public function testPhpRuntimeSettingsConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?php_runtime_settings\s*\(/',
            [
                'core/di/application_support_service_registrar.php' => true,
                'core/factory/context_error_handling_defaults_provider_factory.php' => true,
            ],
            'PHP runtime settings concrete construction outside composition root'
        );
    }

    public function testContextPhpRuntimeSettingsDefaultsConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?context_php_runtime_settings_defaults_provider_factory\s*\(/',
            [
            ],
            'Context php runtime settings defaults provider consumption outside error-handling context composition root'
        );
    }

    public function testErrorHandlerRegistrarConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?error_handler_registrar\s*\(/',
            [
                'core/factory/context_error_handling_defaults_provider_factory.php' => true,
            ],
            'Error handler registrar concrete construction outside composition root'
        );
    }

    public function testContextErrorHandlerRegistrarDefaultsConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?context_error_handler_registrar_defaults_provider_factory\s*\(/',
            [
            ],
            'Context error-handler registrar defaults provider consumption outside error-handling context composition root'
        );
    }

    public function testBootstrapErrorHandlerSetupConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?bootstrap_error_handler_setup\s*\(/',
            [
                'core/factory/context_error_handling_defaults_provider_factory.php' => true,
            ],
            'Bootstrap error handler setup concrete construction outside composition root'
        );
    }

    public function testContextBootstrapErrorHandlerSetupDefaultsConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?context_bootstrap_error_handler_setup_defaults_provider_factory\s*\(/',
            [
            ],
            'Context bootstrap error-handler setup defaults provider consumption outside error-handling context composition root'
        );
    }

    public function testErrorHandlerSetupConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?error_handler_setup\s*\(/',
            [
                'core/factory/context_error_handling_defaults_provider_factory.php' => true,
            ],
            'Error handler setup concrete construction outside composition root'
        );
    }

    public function testContextErrorHandlerSetupDefaultsConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?context_error_handler_setup_defaults_provider_factory\s*\(/',
            [
            ],
            'Context error-handler setup defaults provider consumption outside error-handling context composition root'
        );
    }

    public function testUploadSizeLimitProviderDoesNotOwnRuntimeDefaults(): void
    {
        $providerSource = file_get_contents(dirname(__DIR__, 2) . '/core/block/admin/upload_size_limit_provider.php');
        $traitSource = file_get_contents(dirname(__DIR__, 2) . '/core/block/admin/upload_size_limit_provider_aware_trait.php');

        $this->assertIsString($providerSource);
        $this->assertIsString($traitSource);
        $this->assertStringNotContainsString('defaultPhpRuntimeSettings', $providerSource);
        $this->assertStringNotContainsString('php_runtime_settings.php', $providerSource);
        $this->assertStringNotContainsString('new \fan\core\runtime\php_runtime_settings()', $providerSource);
        $this->assertStringNotContainsString('defaultUploadSizeLimitProvider', $traitSource);
        $this->assertStringNotContainsString('new upload_size_limit_provider()', $traitSource);
    }

    public function testNativeCookieWritesAreLimitedToExplicitAdapter(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/\bsetcookie\s*\(/',
            [
                'core/adapter/cookie_writer.php' => true,
            ],
            'Native cookie writes outside explicit adapter'
        );
    }

    public function testNativeErrorLogWritesAreLimitedToExplicitBoundaries(): void
    {
        $allowedFiles = [
            'core/adapter/error_log_writer.php' => true,
        ];
        $matches = [];

        foreach ($this->productionPhpFiles() as $file) {
            $relativePath = $this->relativePath($file);
            if (isset($allowedFiles[$relativePath])) {
                continue;
            }

            $source = file_get_contents($file);
            $this->assertIsString($source);
            if (preg_match('/\berror_log\s*\(/', $this->codeWithoutCommentsAndStrings($source)) === 1) {
                $matches[] = $relativePath;
            }
        }

        $this->assertSame([], $matches, 'Native error_log writes outside explicit boundaries found in: ' . implode(', ', $matches));
    }

    public function testBootstrapErrorLoggerFilesystemOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/runtime/error_logger.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private object $errorLogWriter,', $source);
        $this->assertStringContainsString('private object $fileStorage', $source);
        $this->assertStringContainsString('$this->fileStorage->isDirectory($logDir)', $source);
        $this->assertStringContainsString('$this->fileStorage->exists($logPath)', $source);
        $this->assertStringNotContainsString('new error_log_writer()', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:is_dir|is_writable|file_exists)\s*\(/',
            $source
        );
    }

    public function testExceptionBaseRequiresInjectedRuntimeBoundaries(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/exception/base.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('Exception database connections dependency is not configured.', $source);
        $this->assertStringContainsString('Exception runtime logger dependency is not configured.', $source);
        $this->assertStringContainsString('Exception request service dependency is not configured.', $source);
        $this->assertStringContainsString('Exception error service dependency is not configured.', $source);
        $this->assertStringContainsString('Exception header writer dependency is not configured.', $source);
        $this->assertStringNotContainsString('new error_log_writer()', $source);
        $this->assertStringNotContainsString('new \fan\core\adapter\header_writer()', $source);
        $this->assertStringNotContainsString('new class', $source);
        $this->assertStringNotContainsString('require_once dirname(__DIR__)', $source);
        $this->assertStringNotContainsString('class_exists(\'\fan\core\service\database\'', $source);
    }

    public function testFatalExceptionRequiresInjectedRequestInput(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/exception/fatal.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('Fatal exception request input dependency is not configured.', $source);
        $this->assertStringNotContainsString('return new class', $source);
        $this->assertStringNotContainsString('$_SERVER', $source);
    }

    public function testBaseDataRequiresInjectedErrorLoggerForErrorLogging(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/base/data.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private \Closure $subDataFactory;', $source);
        $this->assertStringContainsString('private \Closure $classNameResolver;', $source);
        $this->assertStringContainsString('?callable $subDataFactory = null', $source);
        $this->assertStringContainsString('?callable $classNameResolver = null', $source);
        $this->assertStringContainsString('private function subDataFactory(): callable', $source);
        $this->assertStringContainsString('private function className(object $object): string', $source);
        $this->assertStringContainsString('private function defaultSubDataFactory(): \Closure', $source);
        $this->assertStringContainsString('private function defaultClassNameResolver(): \Closure', $source);
        $this->assertStringNotContainsString('private ?\Closure $subDataFactory', $source);
        $this->assertStringNotContainsString('private ?\Closure $classNameResolver', $source);
        $this->assertStringNotContainsString('get_class_alt(', $source);
        $this->assertStringContainsString('Sub-data factory is not configured for data class', $source);
        $this->assertStringNotContainsString('new static(', $source);
        $this->assertStringContainsString('Data error logger dependency is not configured.', $source);
        $this->assertStringNotContainsString('return $this->errorLogger = new class', $source);
        $this->assertStringNotContainsString('::instance()', $source);
    }

    public function testErrorDemonstratorRequiresInjectedInput(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/error/demonstrator.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('Input dependency is not configured for error demonstrator.', $source);
        $this->assertStringNotContainsString('createDefaultInput', $source);
        $this->assertStringNotContainsString('return new class', $source);
        $this->assertStringNotContainsString('$_SERVER', $source);
    }

    public function testErrorLoggerFileStorageConstructionIsLimitedToBootstrapCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\error_logger_file_storage\s*\(/',
            [
                'core/factory/error_logger_defaults_provider_factory.php' => true,
            ],
            'Error logger file storage concrete construction outside bootstrap composition root'
        );
    }

    public function testNativeHeaderWritesAreLimitedToExplicitBoundaries(): void
    {
        $allowedFiles = [
            'core/adapter/header_writer.php' => true,
        ];
        $matches = [];

        foreach ($this->productionPhpFiles() as $file) {
            $relativePath = $this->relativePath($file);
            if (isset($allowedFiles[$relativePath])) {
                continue;
            }

            $source = file_get_contents($file);
            $this->assertIsString($source);
            if (preg_match('/(?<!function )(?<!->)\bheader\s*\(/', $this->codeWithoutCommentsAndStrings($source)) === 1) {
                $matches[] = $relativePath;
            }
        }

        $this->assertSame([], $matches, 'Native header writes outside explicit boundaries found in: ' . implode(', ', $matches));
    }

    public function testImageMetadataReadsAreLimitedToExplicitAdapter(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/\bgetimagesize\s*\(/',
            [
                'core/adapter/image_metadata_reader.php' => true,
            ],
            'Direct image metadata reads outside explicit adapter'
        );
    }

    public function testImageMetadataReaderConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\fan\\\\core\\\\adapter\\\\image_metadata_reader\s*\(/',
            [
                'core/di/application_image_adapter_defaults_provider.php' => true,
            ],
            'Image metadata reader construction outside application composition root'
        );
    }

    public function testImageRuntimeAdapterConstructionIsLimitedToCompositionRoot(): void
    {
        foreach ([
            'image_resource_factory',
            'image_canvas_operations',
            'image_output_writer',
        ] as $adapterClass) {
            $this->assertPatternOnlyAppearsInAllowedFiles(
                '/new\s+\\\\fan\\\\core\\\\adapter\\\\' . preg_quote($adapterClass, '/') . '\s*\(/',
                [
                    'core/di/application_image_adapter_defaults_provider.php' => true,
                ],
                $adapterClass . ' construction outside application composition root'
            );
        }
    }

    public function testWarningCaptureConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\?fan\\\\core\\\\adapter\\\\warning_capture\s*\(|new\s+warning_capture\s*\(/',
            [
                'core/factory/application_registry_defaults_provider_factory.php' => true,
            ],
            'Warning capture construction outside application composition root'
        );
    }

    public function testImageResourceCreationIsLimitedToExplicitAdapter(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/\bexif_imagetype\s*\(|\bimagecreatefrom(?:gif|jpeg|png|wbmp|xbm)\s*\(/',
            [
                'core/factory/adapter/image_resource_factory.php' => true,
            ],
            'Direct image resource creation outside explicit adapter'
        );
    }

    public function testImageCanvasOperationsAreLimitedToExplicitAdapter(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/\b(?:imagecreatetruecolor|imagecopymerge|imagecopyresampled|imagefilter|imagerotate|imagefilledrectangle|imagecolortransparent|imagecolorallocate|imagesx|imagesy|imagestring|imageftbbox|imagettftext|imagerectangle|imagepolygon|imagefilledpolygon|imageellipse|imagefilledellipse|imagearc|imagefilledarc|imageline|imagefontwidth|imagefontheight)\s*\(/',
            [
                'core/adapter/image_canvas_operations.php' => true,
            ],
            'Direct image canvas operations outside explicit adapter'
        );
    }

    public function testImageOutputWritesAreLimitedToExplicitAdapter(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/\bimage(?:gif|jpeg|png|wbmp|xbm)\s*\(/',
            [
                'core/adapter/image_output_writer.php' => true,
            ],
            'Direct image output writes outside explicit adapter'
        );
    }

    public function testSafeSerializerStaticCallsAreLimitedToExplicitOperationsBoundary(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/\bsafe_serializer::/',
            [
                'core/adapter/safe_serializer_operations.php' => true,
            ],
            'Direct safe serializer calls outside explicit operations boundary'
        );
    }

    public function testPhpMailerAdapterLayerIsRemoved(): void
    {
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/adapter/php_mailer.php');
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/di/application_mailer_adapter_defaults_provider.php');
        $this->assertPatternOnlyAppearsInAllowedFiles('/new\s+\\\\PHPMailer\\\\PHPMailer\\\\PHPMailer\s*\(/', [], 'PHPMailer concrete construction must stay removed');
    }

    public function testEmailEngineFactoryDoesNotOwnMailerAdapterDefault(): void
    {
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/di/email_engine_factory.php');
    }

    public function testCoreAdapterDefaultsAreInjectedIntoCoreProvider(): void
    {
        foreach ([
            'warning_capture',
            'php_array_file_loader',
            'safe_serializer_operations',
        ] as $adapterClass) {
            $this->assertPatternOnlyAppearsInAllowedFiles(
                '/new\s+\\\\fan\\\\core\\\\adapter\\\\' . preg_quote($adapterClass, '/') . '\s*\(/',
                [
                    'core/factory/application_registry_defaults_provider_factory.php' => true,
                ],
                $adapterClass . ' construction outside application registry composition root'
            );
        }

        $coreProviderSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_core_adapter_defaults_provider.php');

        $this->assertIsString($coreProviderSource);
        $this->assertStringContainsString('private object $warningCapture,', $coreProviderSource);
        $this->assertStringContainsString('private object $phpArrayFileLoader,', $coreProviderSource);
        $this->assertStringContainsString('callable $serializerOperationsFactory', $coreProviderSource);
        $this->assertStringContainsString('return $this->warningCapture;', $coreProviderSource);
        $this->assertStringContainsString('return $this->phpArrayFileLoader;', $coreProviderSource);
        $this->assertStringContainsString('return $this->serializerOperationsFactory;', $coreProviderSource);
        $this->assertStringNotContainsString('new \fan\core\adapter\warning_capture()', $coreProviderSource);
        $this->assertStringNotContainsString('new php_array_file_loader()', $coreProviderSource);
        $this->assertStringNotContainsString('new safe_serializer_operations(', $coreProviderSource);
    }

    public function testRemovedAdodbAdapterIsNotReferencedByRuntimeAndSessionProviders(): void
    {
        $runtimeProviderSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_runtime_adapter_defaults_provider.php');
        $sessionProviderSource = file_get_contents(dirname(__DIR__, 2) . '/core/di/application_session_adapter_defaults_provider.php');
        $registryDefaultsProviderFactorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/application_registry_defaults_provider_factory.php');

        $this->assertIsString($runtimeProviderSource);
        $this->assertIsString($sessionProviderSource);
        $this->assertIsString($registryDefaultsProviderFactorySource);
        $this->assertStringNotContainsString('adodb', strtolower($runtimeProviderSource));
        $this->assertStringNotContainsString('adodb', strtolower($sessionProviderSource));
        $this->assertStringNotContainsString('adodb', strtolower($registryDefaultsProviderFactorySource));
        $this->assertStringContainsString('callable $dataLoaderFactory,', $runtimeProviderSource);
        $this->assertStringContainsString('callable $errorLogWriterFactory,', $runtimeProviderSource);
        $this->assertStringContainsString('callable $headerWriterFactory,', $runtimeProviderSource);
        $this->assertStringContainsString('callable $cookieWriterFactory,', $runtimeProviderSource);
        $this->assertStringContainsString('callable $curlAdapterFactory', $runtimeProviderSource);
        $this->assertStringContainsString('object $nativeSession,', $sessionProviderSource);
        $this->assertStringContainsString('object $pearHttpSession', $sessionProviderSource);
        $this->assertStringNotContainsString('new \fan\core\adapter\native_session()', $sessionProviderSource);
        $this->assertStringNotContainsString('new \fan\core\adapter\pear_http_session()', $sessionProviderSource);
    }

    public function testContainerProviderDefaultsAreInjectedOutsideLeafFactories(): void
    {
        $contextContainerFactorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/context_container_factory.php');
        $contextDefaultsFactorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/context_defaults_factory.php');
        $contextCoreDefaultsFactorySource = file_get_contents(dirname(__DIR__, 2) . '/core/factory/context_core_defaults_provider_factory.php');

        $this->assertIsString($contextContainerFactorySource);
        $this->assertIsString($contextDefaultsFactorySource);
        $this->assertIsString($contextCoreDefaultsFactorySource);
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/di/container_registry.php');
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/di/container_registry_state.php');
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/di/container_registry_state_factory.php');
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/di/container_provider.php');
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/di/container_provider_factory.php');
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/application/container_registry_state_factory.php');
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/core/application/container_registry_state_setter.php');
        $this->assertStringNotContainsString('containerRegistryStateFactory', $contextContainerFactorySource);
        $this->assertStringNotContainsString('containerRegistryStateSetter', $contextContainerFactorySource);
        $this->assertStringNotContainsString('container_registry_state', $contextContainerFactorySource);
        $this->assertStringNotContainsString('\fan\core\di\container_registry::setState($state);', $contextContainerFactorySource);
        $this->assertStringContainsString('$contextCoreDefaults = (new context_core_defaults_provider_factory())();', $contextDefaultsFactorySource);
        $this->assertStringContainsString('$coreDefaults = $contextCoreDefaults();', $contextDefaultsFactorySource);
        $this->assertStringNotContainsString('$coreDefaultsProvider = (new context_core_defaults_provider_factory())();', $contextDefaultsFactorySource);
        $this->assertStringNotContainsString('$coreDefaults = (new context_core_defaults_factory())();', $contextDefaultsFactorySource);
        $this->assertStringContainsString('$contextSupportDefaults = (new context_support_defaults_provider_factory())();', $contextDefaultsFactorySource);
        $this->assertStringContainsString('$supportDefaults = $contextSupportDefaults();', $contextDefaultsFactorySource);
        $this->assertStringNotContainsString('$supportDefaultsProvider = (new context_support_defaults_provider_factory())();', $contextDefaultsFactorySource);
        $this->assertStringContainsString('$contextErrorHandlingDefaults = (new context_error_handling_defaults_provider_factory())();', $contextDefaultsFactorySource);
        $this->assertStringContainsString('$errorHandlingDefaults = $contextErrorHandlingDefaults();', $contextDefaultsFactorySource);
        $this->assertStringNotContainsString('$errorHandlingDefaultsProvider = (new context_error_handling_defaults_provider_factory())();', $contextDefaultsFactorySource);
        $this->assertStringContainsString('$contextStateDefaults = new bootstrap_state_defaults_factory();', $contextCoreDefaultsFactorySource);
        $this->assertStringContainsString("'stateFactory' => \$contextStateDefaults->stateFactory()", $contextCoreDefaultsFactorySource);
        $this->assertStringNotContainsString('$contextStateDefaults = (new context_state_defaults_provider_factory())();', $contextCoreDefaultsFactorySource);
        $this->assertStringNotContainsString('$bootstrapStateDefaults = new bootstrap_state_defaults_factory();', $contextCoreDefaultsFactorySource);
        $this->assertStringContainsString('$contextContainerDefaults = new context_container_defaults_factory(', $contextCoreDefaultsFactorySource);
        $this->assertStringContainsString("'containerFactory' => \$contextContainerDefaults()", $contextCoreDefaultsFactorySource);
        $this->assertStringNotContainsString('$contextContainerDefaults = (new context_container_defaults_provider_factory())();', $contextCoreDefaultsFactorySource);
        $this->assertStringNotContainsString('$containerFactory = (new context_container_defaults_factory(', $contextCoreDefaultsFactorySource);
        $this->assertStringContainsString('static fn(callable $applicationContainerFactory): context_container_factory => new context_container_factory(', $contextCoreDefaultsFactorySource);
        $this->assertStringContainsString('$contextApplicationContainerDefaults = (new application_container_defaults_provider_factory())();', $contextCoreDefaultsFactorySource);
        $this->assertStringContainsString('$contextApplicationContainerDefaults->containerFactory()', $contextCoreDefaultsFactorySource);
        $this->assertStringNotContainsString('application_container_defaults_factory::containerFactory()', $contextCoreDefaultsFactorySource);
        $this->assertStringContainsString('$contextRequestInputDefaults = new bootstrap_request_input_defaults_factory();', $contextCoreDefaultsFactorySource);
        $this->assertStringContainsString("'requestInputFactory' => \$contextRequestInputDefaults->requestInputFactory()", $contextCoreDefaultsFactorySource);
        $this->assertStringNotContainsString('context_core_request_input_defaults_provider_factory', $contextCoreDefaultsFactorySource);
        $this->assertStringNotContainsString('$contextRequestInputDefaults = (new context_request_input_defaults_provider_factory())();', $contextCoreDefaultsFactorySource);
        $this->assertStringContainsString('$contextRuntimeDefaults = new bootstrap_runtime_defaults_factory();', $contextCoreDefaultsFactorySource);
        $this->assertStringContainsString("'bootstrapRuntimeFactory' => \$contextRuntimeDefaults()", $contextCoreDefaultsFactorySource);
        $this->assertStringNotContainsString('$contextRuntimeDefaults = (new context_runtime_defaults_provider_factory())();', $contextCoreDefaultsFactorySource);
        $this->assertStringNotContainsString('$applicationContainerFactory = $this->applicationContainerFactory();', $contextDefaultsFactorySource);
        $this->assertStringNotContainsString('$containerRegistryStateFactory = new container_registry_state_factory($applicationContainerFactory);', $contextDefaultsFactorySource);
        $this->assertStringNotContainsString('new application_service_factory_options(', $contextDefaultsFactorySource);
        $this->assertStringNotContainsString('application_service_factory_bundle::fromProviders(', $contextDefaultsFactorySource);
        $this->assertStringNotContainsString('new \fan\core\di\application_container_factory($factoryBundle, $dependencyBundle)', $contextDefaultsFactorySource);
        $this->assertStringNotContainsString('$containerRegistryStateSetter = new container_registry_state_setter();', $contextDefaultsFactorySource);
        $this->assertStringNotContainsString('\fan\core\di\container_registry::setState($state);', $contextDefaultsFactorySource);
    }

    public function testContextContainerFactoryConstructionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?context_container_factory\s*\(/',
            [
                'core/factory/context_core_defaults_provider_factory.php' => true,
            ],
            'Context container factory concrete construction outside composition root'
        );
    }

    public function testContextCoreDefaultsConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?context_core_defaults_provider_factory\s*\(/',
            [
                'core/factory/context_defaults_factory.php' => true,
            ],
            'Context core defaults provider consumption outside root context composition root'
        );
    }

    public function testContextSupportDefaultsConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?context_support_defaults_provider_factory\s*\(/',
            [
                'core/factory/context_defaults_factory.php' => true,
            ],
            'Context support defaults provider consumption outside root context composition root'
        );
    }

    public function testContextErrorHandlingDefaultsConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?context_error_handling_defaults_provider_factory\s*\(/',
            [
                'core/factory/context_defaults_factory.php' => true,
            ],
            'Context error-handling defaults provider consumption outside root context composition root'
        );
    }

    public function testContextErrorLoggerDefaultsConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?context_error_logger_defaults_provider_factory\s*\(/',
            [
            ],
            'Context error logger defaults provider consumption outside error-handling context composition root'
        );
    }

    public function testContextStateDefaultsConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?context_state_defaults_provider_factory\s*\(/',
            [
            ],
            'Context state defaults provider consumption outside core context composition root'
        );
    }

    public function testContextApplicationContainerDefaultsConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?application_container_defaults_provider_factory\s*\(/',
            [
                'core/factory/context_core_defaults_provider_factory.php' => true,
            ],
            'Application container defaults provider consumption outside context composition root'
        );
    }

    public function testContextContainerDefaultsConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?context_container_defaults_provider_factory\s*\(/',
            [
            ],
            'Context container defaults provider consumption outside core context composition root'
        );
    }

    public function testContextRequestInputDefaultsConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?context_request_input_defaults_provider_factory\s*\(/',
            [
            ],
            'Context request-input defaults provider consumption outside core context composition root'
        );
    }

    public function testContextRuntimeDefaultsConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?context_runtime_defaults_provider_factory\s*\(/',
            [
            ],
            'Context runtime defaults provider consumption outside core context composition root'
        );
    }

    public function testContextAutoloaderDefaultsConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?context_autoloader_defaults_provider_factory\s*\(/',
            [
            ],
            'Context autoloader defaults provider consumption outside support context composition root'
        );
    }

    public function testContextLoaderDefaultsConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?context_loader_defaults_provider_factory\s*\(/',
            [
            ],
            'Context loader defaults provider consumption outside support context composition root'
        );
    }

    public function testContextConfigDefaultsConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?context_config_defaults_provider_factory\s*\(/',
            [
            ],
            'Context config defaults provider consumption outside support context composition root'
        );
    }

    public function testContextObjectDefaultsConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?context_object_defaults_provider_factory\s*\(/',
            [
            ],
            'Context object defaults provider consumption outside support context composition root'
        );
    }

    public function testBootstrapOperationsDefaultsConsumptionIsLimitedToCompositionRoots(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?bootstrap_operations_defaults_provider_factory\s*\(/',
            [
                'core/factory/application_container_operations_defaults_provider_factory.php' => true,
                'core/factory/bootstrap_runtime_operations_defaults_provider_factory.php' => true,
            ],
            'Bootstrap operations defaults provider consumption outside explicit bootstrap composition roots'
        );
    }

    public function testApplicationRegistryDefaultsConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\di\\\\)?application_registry_defaults_provider_factory\s*\(/',
            [
                'core/factory/application_container_registry_defaults_provider_factory.php' => true,
            ],
            'Application registry defaults provider consumption outside application-container composition root'
        );
    }

    public function testApplicationFactoryProviderDefaultsConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\di\\\\)?application_factory_provider_defaults_provider_factory\s*\(/',
            [
                'core/factory/application_container_factory_provider_defaults_provider_factory.php' => true,
            ],
            'Application factory-provider defaults consumption outside application-container composition root'
        );
    }

    public function testApplicationModelFactoryDefaultsConsumptionIsLimitedToFactoryProviderCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\di\\\\)?application_model_factory_defaults_provider_factory\s*\(/',
            [
                'core/factory/application_factory_provider_defaults_provider_factory.php' => true,
            ],
            'Application model factory defaults consumption outside factory-provider composition root'
        );
    }

    public function testApplicationServiceEngineFactoryDefaultsConsumptionIsLimitedToFactoryProviderCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\di\\\\)?application_service_engine_factory_defaults_provider_factory\s*\(/',
            [
                'core/factory/application_service_factory_defaults_provider_factory.php' => true,
            ],
            'Application service engine factory defaults consumption outside factory-provider composition root'
        );
    }

    public function testApplicationServiceSubFactoryDefaultsConsumptionIsLimitedToFactoryProviderCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\di\\\\)?application_service_sub_factory_defaults_provider_factory\s*\(/',
            [
                'core/factory/application_service_factory_defaults_provider_factory.php' => true,
            ],
            'Application service sub-factory defaults consumption outside factory-provider composition root'
        );
    }

    public function testApplicationCoreServiceFactoryDefaultsConsumptionIsLimitedToFactoryProviderCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\di\\\\)?application_core_service_factory_defaults_provider_factory\s*\(/',
            [
                'core/factory/application_service_factory_defaults_provider_factory.php' => true,
            ],
            'Application core service factory defaults consumption outside factory-provider composition root'
        );
    }

    public function testApplicationServiceFactoryDefaultsConsumptionIsLimitedToFactoryProviderCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\di\\\\)?application_service_factory_defaults_provider_factory\s*\(/',
            [
                'core/factory/application_factory_provider_defaults_provider_factory.php' => true,
            ],
            'Application service factory defaults consumption outside factory-provider composition root'
        );
    }

    public function testApplicationRuntimeFactoryDefaultsConsumptionIsLimitedToFactoryProviderCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\di\\\\)?application_runtime_factory_defaults_provider_factory\s*\(/',
            [
                'core/factory/application_factory_provider_defaults_provider_factory.php' => true,
            ],
            'Application runtime factory defaults consumption outside factory-provider composition root'
        );
    }

    public function testApplicationDeferredServiceFactoryProviderConsumptionIsLimitedToFactoryProviderCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\di\\\\)?application_deferred_service_factory_provider_factory\s*\(/',
            [
                'core/factory/application_factory_provider_defaults_provider_factory.php' => true,
            ],
            'Application deferred service factory registry consumption outside factory-provider composition root'
        );
    }

    public function testApplicationServiceRegistrarDefaultsConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\di\\\\)?application_service_registrar_defaults_provider_factory\s*\(/',
            [
                'core/factory/application_container_registrar_defaults_provider_factory.php' => true,
            ],
            'Application service registrar defaults consumption outside application-container composition root'
        );
    }

    public function testApplicationServiceCreatorDefaultsConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\di\\\\)?application_service_creator_defaults_provider_factory\s*\(/',
            [
                'core/factory/application_container_creator_defaults_provider_factory.php' => true,
            ],
            'Application service creator defaults consumption outside application-container composition root'
        );
    }

    public function testApplicationContainerFactoryCallableConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?application_container_factory_callable_factory\s*\(/',
            [
                'core/factory/application_container_defaults_provider_factory.php' => true,
            ],
            'Application container factory callable consumption outside application-container defaults composition root'
        );
    }

    public function testBootstrapRuntimeServiceDefaultsConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?bootstrap_runtime_service_defaults_provider_factory\s*\(/',
            [
                'core/factory/bootstrap_runtime_service_factory_defaults_provider_factory.php' => true,
            ],
            'Bootstrap runtime service defaults provider consumption outside runtime service factory composition root'
        );
    }

    public function testBootstrapRuntimeStateDefaultsConsumptionIsLimitedToCompositionRoot(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+(?:\\\\fan\\\\core\\\\bootstrap\\\\)?bootstrap_runtime_state_defaults_provider_factory\s*\(/',
            [
                'core/factory/bootstrap_runtime_state_factory_defaults_provider_factory.php' => true,
            ],
            'Bootstrap runtime state defaults provider consumption outside runtime state factory composition root'
        );
    }

    public function testFileDataRowFilesystemOperationsAreInjected(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/core/base/model/file_data/row.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private ?object $fileDataStorage = null;', $source);
        $this->assertStringContainsString('$this->fileDataStorage()', $source);
        $this->assertStringContainsString('parent::setDependenciesFromEntityService($entity);', $source);
        $this->assertStringContainsString('$this->createModelRowFatalException(', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\model\entity\fatal as fatalException;', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:file_exists|is_link|readlink|is_dir|is_writable|is_file|mkdir|filesize|filemtime|move_uploaded_file|file_put_contents|clearstatcache|rename|copy|unlink)\s*\(/',
            $source
        );
    }

    public function testViewLoaderStateDependencyIsInjected(): void
    {
        $routerSource = file_get_contents(dirname(__DIR__, 2) . '/core/view/router/loader.php');
        $parserSource = file_get_contents(dirname(__DIR__, 2) . '/core/view/parser/loader.php');
        $blockSource = file_get_contents(dirname(__DIR__, 2) . '/core/block/base.php');

        $this->assertIsString($routerSource);
        $this->assertIsString($parserSource);
        $this->assertIsString($blockSource);
        $this->assertStringContainsString('Loader state is not configured for loader view router.', $routerSource);
        $this->assertStringContainsString('Loader state is not configured for loader view parser.', $parserSource);
        $this->assertStringContainsString('Loader state is not configured for block.', $blockSource);
        $this->assertStringNotContainsString('new loader_state()', $routerSource);
    }

    public function testViewLoaderStateConstructionIsLimitedToRuntimeCompositionAndTests(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\?fan\\\\core\\\\view\\\\router\\\\loader_state\s*\(|new\s+loader_state\s*\(/',
            [
                'core/factory/bootstrap_runtime_state_defaults_provider_factory.php' => true,
                'core/factory/application_container_factory.php' => true,
                'core/factory/view_loader_state_factory.php' => true,
                'unit/core/service/BootstrapRuntimeTest.php' => true,
                'unit/core/view/parser/LoaderTest.php' => true,
                'unit/core/view/router/LoaderStateTest.php' => true,
                'unit/core/view/router/LoaderTest.php' => true,
            ],
            'View loader state construction outside explicit runtime composition/test boundaries'
        );
    }

    public function testMetaMakerStateDependencyIsInjected(): void
    {
        $makerSource = file_get_contents(dirname(__DIR__, 2) . '/core/base/meta/maker.php');
        $blockSource = file_get_contents(dirname(__DIR__, 2) . '/core/block/base.php');

        $this->assertIsString($makerSource);
        $this->assertIsString($blockSource);
        $this->assertStringContainsString('Meta maker state is not configured for meta maker.', $makerSource);
        $this->assertStringContainsString('Meta maker state is not configured for block.', $blockSource);
        $this->assertStringContainsString('$this->state     = $state ?? throw new \RuntimeException', $makerSource);
        $this->assertStringNotContainsString('new maker_state()', $makerSource);
    }

    public function testMetaMakerStateConstructionIsLimitedToRuntimeCompositionAndTests(): void
    {
        $this->assertPatternOnlyAppearsInAllowedFiles(
            '/new\s+\\\\?fan\\\\core\\\\base\\\\meta\\\\maker_state\s*\(|new\s+maker_state\s*\(/',
            [
                'core/factory/bootstrap_runtime_state_defaults_provider_factory.php' => true,
                'core/factory/application_service_factory_defaults_provider_factory.php' => true,
                'core/factory/application_container_factory.php' => true,
                'unit/mock/core/base/meta/MetaDoubles.php' => true,
                'unit/core/base/meta/MakerStateTest.php' => true,
                'unit/core/base/meta/MakerTest.php' => true,
                'unit/core/service/BootstrapRuntimeTest.php' => true,
            ],
            'Meta maker state construction outside explicit runtime composition/test boundaries'
        );
    }

    /**
     * @return list<string>
     */
    private function productionPhpFiles(): array
    {
        $root = dirname(__DIR__, 2);
        $files = [];

        foreach (['core', 'project', 'htdocs'] as $directory) {
            $path = $root . '/' . $directory;
            if (!is_dir($path)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $fileInfo) {
                if (!$fileInfo instanceof SplFileInfo || $fileInfo->getExtension() !== 'php') {
                    continue;
                }
                $files[] = $fileInfo->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    private function relativePath(string $path): string
    {
        return substr($path, strlen(dirname(__DIR__, 2)) + 1);
    }

    /**
     * @param array<string, true> $allowedFiles
     */
    private function assertPatternOnlyAppearsInAllowedFiles(string $pattern, array $allowedFiles, string $message): void
    {
        $matches = [];

        foreach ($this->productionPhpFiles() as $file) {
            $relativePath = $this->relativePath($file);
            if (
                isset($allowedFiles[$relativePath])
                || $this->isApplicationAdapterCompositionRoot($relativePath, $pattern)
                || $this->isApplicationStateCompositionRoot($relativePath, $pattern)
                || $this->isApplicationModelFactoryCompositionRoot($relativePath, $pattern)
            ) {
                continue;
            }

            $source = file_get_contents($file);
            $this->assertIsString($source);
            if (preg_match($pattern, $source) === 1) {
                $matches[] = $relativePath;
            }
        }

        $this->assertSame([], $matches, $message . ' found in: ' . implode(', ', $matches));
    }

    private function isApplicationAdapterCompositionRoot(string $relativePath, string $pattern): bool
    {
        return in_array($relativePath, [
            'core/di/application_adapter_registry.php',
            'core/di/application_adapter_registry_defaults_provider.php',
            'core/di/application_core_adapter_defaults_provider.php',
            'core/di/application_storage_adapter_defaults_provider.php',
        ], true)
            && str_contains($pattern, '\\\\adapter\\\\');
    }

    private function isApplicationStateCompositionRoot(string $relativePath, string $pattern): bool
    {
        return $relativePath === 'core/di/application_state_registry.php'
            && str_contains($pattern, '_state');
    }

    private function isApplicationModelFactoryCompositionRoot(string $relativePath, string $pattern): bool
    {
        return $relativePath === 'core/di/application_container_dependency_provider.php'
            && (
                str_contains($pattern, '\\\\di\\\\entity_')
                || str_contains($pattern, '\\\\di\\\\model_')
            );
    }

    /**
     * @return list<string>
     */
    private function dynamicReflectionBoundaryInventoryFiles(): array
    {
        return [
            'core/adapter/bootstrap_loader_file_storage.php',
            'core/adapter/compiled_template_loader.php',
            'core/adapter/pear_http_session.php',
            'core/adapter/project_tool_loader.php',
            'core/adapter/twig_template_service.php',
            'core/adapter/zend_autoloader.php',
            'core/base/model/entity_dependencies.php',
            'core/base/transfer/int.php',
            'core/block/base_dependency_defaults.php',
            'core/block/base_dependency_application_data_factory_group.php',
            'core/block/base_dependency_application_factory_group.php',
            'core/block/base_dependency_application_runtime_factory_group.php',
            'core/block/base_dependency_application_service_runtime_factory_group.php',
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
            'core/block/base_dependency_image_metadata_media_error_helper_group.php',
            'core/block/base_dependency_image_modify_media_core_factory_group.php',
            'core/block/base_dependency_json_data_core_factory_group.php',
            'core/block/base_dependency_locale_factory_context_group.php',
            'core/block/base_dependency_matcher_factory_context_group.php',
            'core/block/base_dependency_helper_group.php',
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
            'core/block/base_dependency_storage_group.php',
            'core/block/base_dependency_bootstrap_runtime_context_group.php',
            'core/block/base_dependency_tab_service_runtime_context_group.php',
            'core/block/base_dependency_tab_runtime_context_group.php',
            'core/block/base_dependency_upload_limit_storage_group.php',
            'core/block/base_dependency_user_application_data_factory_group.php',
            'core/block/base_dependency_view_factory_loader_group.php',
            'core/block/base_dependency_view_loader_group.php',
            'core/block/base_dependency_view_parser_exception_factory_loader_group.php',
            'core/block/base_dependency_view_router_factory_loader_group.php',
            'core/block/base_dependency_view_state_loader_group.php',
            'core/block/base_dependency_view_meta_group.php',
            'core/di/application_client_array_adducer_payload_dependencies.php',
            'core/di/application_client_array_payload_dependencies.php',
            'core/di/application_client_array_value_reader_payload_dependencies.php',
            'core/di/application_client_bootstrap_runtime_dependencies.php',
            'core/di/application_client_cache_runtime_dependencies.php',
            'core/di/application_client_config_cache_runtime_dependencies.php',
            'core/di/application_client_config_runtime_dependencies.php',
            'core/di/application_client_curl_adapter_transport_dependencies.php',
            'core/di/application_client_curl_factory_transport_dependencies.php',
            'core/di/application_client_curl_transport_dependencies.php',
            'core/di/application_client_error_transport_dependencies.php',
            'core/di/application_client_payload_dependencies.php',
            'core/di/application_client_request_payload_dependencies.php',
            'core/di/application_client_runtime_dependencies.php',
            'core/di/application_client_cookie_writer_payload_dependencies.php',
            'core/di/application_client_serialization_payload_dependencies.php',
            'core/di/application_client_serialization_transport_dependencies.php',
            'core/di/application_client_serializer_operations_payload_dependencies.php',
            'core/di/application_client_service_dependencies.php',
            'core/di/application_client_service_creator.php',
            'core/di/application_client_transport_dependencies.php',
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
            'core/di/application_content_tab_factory_context_dependencies.php',
            'core/di/application_content_php_array_file_loader_storage_dependencies.php',
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
            'core/di/application_content_context_dependencies.php',
            'core/di/application_content_runtime_dependencies.php',
            'core/di/application_content_service_dependencies.php',
            'core/di/application_content_service_creator.php',
            'core/di/application_content_storage_dependencies.php',
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
            'core/di/application_infrastructure_service_dependencies.php',
            'core/di/application_infrastructure_service_creator.php',
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
            'core/di/application_pager_bootstrap_runtime_dependencies.php',
            'core/di/application_pager_cache_factory_config_cache_runtime_dependencies.php',
            'core/di/application_pager_config_cache_runtime_dependencies.php',
            'core/di/application_pager_config_config_cache_runtime_dependencies.php',
            'core/di/application_pager_context_dependencies.php',
            'core/di/application_pager_entity_context_dependencies.php',
            'core/di/application_pager_exception_dependencies.php',
            'core/di/application_pager_request_context_dependencies.php',
            'core/di/application_pager_runtime_dependencies.php',
            'core/di/application_pager_service_dependencies.php',
            'core/di/application_pager_service_creator.php',
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
            'core/di/application_user_cache_factory_bootstrap_cache_runtime_dependencies.php',
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
            'core/di/application_user_session_exception_session_context_dependencies.php',
            'core/di/application_user_session_factory_identity_factory_dependencies.php',
            'core/di/application_user_support_factory_dependencies.php',
            'core/di/application_user_error_factory_support_factory_dependencies.php',
            'core/di/application_user_entity_factory_support_factory_dependencies.php',
            'core/di/application_utility_array_value_reader_helper_error_core_dependencies.php',
            'core/di/application_utility_cache_factory_config_cache_core_dependencies.php',
            'core/di/application_utility_config_cache_core_dependencies.php',
            'core/di/application_utility_config_config_cache_core_dependencies.php',
            'core/di/application_utility_core_dependencies.php',
            'core/di/application_utility_class_storage_dependencies.php',
            'core/di/application_utility_file_storage_dependencies.php',
            'core/di/application_utility_php_array_file_loader_file_storage_dependencies.php',
            'core/di/application_utility_soap_wsdl_file_storage_file_storage_dependencies.php',
            'core/di/application_utility_helper_error_core_dependencies.php',
            'core/di/application_utility_error_factory_helper_error_core_dependencies.php',
            'core/di/application_utility_image_canvas_output_dependencies.php',
            'core/di/application_utility_image_canvas_operations_canvas_output_dependencies.php',
            'core/di/application_utility_image_output_writer_canvas_output_dependencies.php',
            'core/di/application_utility_image_dependencies.php',
            'core/di/application_utility_image_metadata_resource_dependencies.php',
            'core/di/application_utility_image_metadata_reader_metadata_resource_dependencies.php',
            'core/di/application_utility_image_resource_factory_metadata_resource_dependencies.php',
            'core/di/application_utility_image_storage_dependencies.php',
            'core/di/application_utility_image_source_file_storage_image_storage_dependencies.php',
            'core/di/application_utility_obfuscator_file_storage_image_storage_dependencies.php',
            'core/di/application_utility_bootstrap_runtime_runtime_core_dependencies.php',
            'core/di/application_utility_runtime_core_dependencies.php',
            'core/di/application_utility_php_runtime_settings_runtime_core_dependencies.php',
            'core/di/application_utility_service_dependencies.php',
            'core/di/application_utility_storage_dependencies.php',
            'core/di/application_utility_service_creator.php',
            'core/di/configured_class_instantiator.php',
            'core/factory/adapter/reflection_class_factory.php',
            'core/factory/application_runtime_class_instantiator_provider.php',
            'core/factory/application_runtime_configured_service_provider.php',
            'core/factory/application_runtime_factory_defaults_provider_factory.php',
            'core/factory/bootstrap_object_class_instantiator_provider.php',
            'core/factory/bootstrap_object_configured_service_provider.php',
            'core/factory/bootstrap_object_defaults_provider_factory.php',
            'core/factory/cache_engine_factory.php',
            'core/factory/configured_service_factory.php',
            'core/service/block_context.php',
            'core/service/locale.php',
            'core/service/plain.php',
            'core/service/tab.php',
            'core/service/timer.php',
            'core/service/translation.php',
            'tools/composer_autoload.php',
        ];
    }

    private function codeWithoutCommentsAndStrings(string $source): string
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

    private function hasLoadingStatement(string $source): bool
    {
        foreach (PhpToken::tokenize($source) as $token) {
            if (in_array($token->id, [T_REQUIRE, T_REQUIRE_ONCE, T_INCLUDE, T_INCLUDE_ONCE], true)) {
                return true;
            }
        }

        return false;
    }
}
