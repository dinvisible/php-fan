<?php

declare(strict_types=1);

use fan\core\di\application_support_service_registrar;
use fan\core\di\container;
use PHPUnit\Framework\TestCase;
use fan\core\di\plain_exception_factory;
use fan\core\service\transfer;


final class ApplicationSupportServiceRegistrarTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!function_exists('adduceToArray')) {
            eval('function adduceToArray(mixed $value): array { if (is_array($value)) { return $value; } if (is_object($value) && method_exists($value, "toArray")) { return $value->toArray(); } return empty($value) ? [] : [$value]; }');
        }
        if (!function_exists('array_merge_recursive_alt')) {
            eval('function array_merge_recursive_alt(mixed $arrFirst): mixed { if (!is_array($arrFirst)) { $arrFirst = $arrFirst === null ? [] : [$arrFirst]; } foreach (array_slice(func_get_args(), 1) as $arrNext) { if ($arrNext === null) { continue; } $arrNext = is_array($arrNext) ? $arrNext : [$arrNext]; foreach ($arrNext as $key => $value) { $arrFirst[$key] = isset($arrFirst[$key]) && (is_array($arrFirst[$key]) || is_array($value)) ? array_merge_recursive_alt($arrFirst[$key], $value) : $value; } } return $arrFirst; }');
        }
        if (!function_exists('array_val')) {
            eval('function array_val(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed { return $key === null ? $default : ($array[$key] ?? $default); }');
        }
        if (!function_exists('is_array_alt')) {
            eval('function is_array_alt(mixed $value): bool { return is_array($value) || $value instanceof \ArrayAccess; }');
        }
        if (!function_exists('get_class_alt')) {
            eval('function get_class_alt(object|string $value): ?string { return is_object($value) ? get_class($value) : $value; }');
        }
        if (!function_exists('get_class_name')) {
            eval('function get_class_name(object|string $value): ?string { $class = is_object($value) ? get_class($value) : $value; $parts = explode("\\\\\\\\", $class); return end($parts); }');
        }
        if (!function_exists('get_ns_name')) {
            eval('function get_ns_name(object|string $value, int $depth = 1): ?string { if ($depth < 0 || $depth > 40) { return null; } $name = is_object($value) ? get_class($value) : $value; for ($i = 0; $i < $depth; $i++) { $position = strrpos($name, chr(92)); $name = $position > 0 ? substr($name, 0, $position) : ""; } return $name; }');
        }
    }

    public function testRegistrarOwnsSupportServiceGraphRegistrations(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_support_service_registrar.php');
        $containerSource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/application_container_factory.php');
        $bundleSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_container_dependency_bundle.php');

        $this->assertIsString($source);
        $this->assertIsString($containerSource);
        $this->assertIsString($bundleSource);
        $this->assertStringContainsString('final class application_support_service_registrar', $source);
        $this->assertStringNotContainsString("->factory('database_pool'", $source);
        $this->assertStringNotContainsString("->factory('database_connections'", $source);
        $this->assertStringContainsString("'array_adducer'", $source);
        $this->assertStringContainsString("'recursive_merger'", $source);
        $this->assertStringContainsString("'array_value_reader'", $source);
        $this->assertStringContainsString("'array_like_checker'", $source);
        $this->assertStringContainsString("'class_name_resolver'", $source);
        $this->assertStringContainsString("'short_class_name_resolver'", $source);
        $this->assertStringContainsString("'namespace_resolver'", $source);
        $this->assertStringContainsString("'reflection_class_factory'", $source);
        $this->assertStringContainsString("'request_input',", $source);
        $this->assertStringContainsString("'core_fatal_exception_factory'", $source);
        $this->assertStringContainsString("'bootstrap_runtime',", $source);
        $this->assertStringContainsString("'delayed_meta_factory'", $source);
        $this->assertStringContainsString("'meta_maker_factory'", $source);
        $this->assertStringContainsString("new meta_maker_factory(\n                    \$container->get('delayed_meta_factory'),\n                    \$container->get('recursive_merger'),\n                    \$container->get('array_adducer'),\n                    \$container->get('class_name_resolver')", $source);
        $this->assertStringContainsString("'view_keeper_factory'", $source);
        $this->assertStringContainsString("'view_loader_json_keeper_factory'", $source);
        $this->assertStringContainsString("'view_loader_text_keeper_factory'", $source);
        $this->assertStringContainsString("'view_loader_state_factory'", $source);
        $this->assertStringContainsString("'view_router_factory'", $source);
        $this->assertStringContainsString("new view_router_factory(\$container->get('view_keeper_factory'), \$container->get('array_adducer'))", $source);
        $this->assertStringContainsString("'transfer_exception_factory'", $source);
        $this->assertStringNotContainsString("'template_exception_factory'", $source);
        $this->assertStringContainsString("'error500_exception_factory'", $source);
        $this->assertStringContainsString("'plain_exception_factory'", $source);
        $this->assertStringContainsString('new plain_exception_factory()', $source);
        $this->assertStringContainsString("\$container->get('class_name_resolver')", $source);
        $this->assertStringContainsString("'block_context',", $source);
        $this->assertStringContainsString('new block_context(', $source);
        $this->assertStringContainsString("\$container->get('reflection_class_factory')", $source);
        $this->assertStringContainsString("'plain_file_context',", $source);
        $this->assertStringContainsString("\$container->get('plain_exception_factory')", $source);
        $this->assertStringNotContainsString('new \fan\project\exception\plain\fatal', $source);
        $this->assertStringContainsString("'transfer',", $source);
        $this->assertStringContainsString('public application_support_service_registrar $supportServiceRegistrar,', $bundleSource);
        $this->assertStringContainsString('$supportServiceRegistrar = $dependencyBundle->supportServiceRegistrar;', $containerSource);
        $this->assertStringContainsString('$supportServiceRegistrar->register(', $containerSource);
        $this->assertStringNotContainsString("->factory('database_pool'", $containerSource);
        $this->assertStringNotContainsString("->factory('plain_file_context'", $containerSource);
        $this->assertStringNotContainsString('new \fan\core\runtime\php_runtime_settings()', $containerSource);
        $this->assertStringNotContainsString('adodb', $containerSource);
    }

    public function testRegistrarRegistersSupportServicesWithInjectedFactories(): void
    {
        $container = $this->containerWithSupportDependencies();
        $requestInput = (object)['name' => 'request-input'];
        $runtime = (object)['name' => 'runtime'];
        $requestInputCalls = 0;
        $runtimeCalls = [];
        $blockExceptionFactory = new ApplicationSupportBlockExceptionFactoryDouble();

        (new application_support_service_registrar())->register(
            $container,
            new ApplicationSupportSerializerOperationsDouble(),
            static function () use ($requestInput, &$requestInputCalls): object {
                ++$requestInputCalls;

                return $requestInput;
            },
            static fn(): array => ['operation' => 'bootstrap'],
            static function (mixed ...$arguments) use ($runtime, &$runtimeCalls): object {
                $runtimeCalls[] = $arguments;

                return $runtime;
            },
            static fn(): object => (object)['name' => 'service-engine-factory'],
            $blockExceptionFactory
        );

        $this->assertFalse($container->has('database_pool'));
        $this->assertTrue($container->has('php_runtime_settings'));
        $this->assertTrue($container->has('serializer_operations'));
        $this->assertTrue($container->has('array_adducer'));
        $this->assertTrue($container->has('recursive_merger'));
        $this->assertTrue($container->has('array_value_reader'));
        $this->assertTrue($container->has('array_like_checker'));
        $this->assertTrue($container->has('class_name_resolver'));
        $this->assertTrue($container->has('short_class_name_resolver'));
        $this->assertTrue($container->has('namespace_resolver'));
        $this->assertTrue($container->has('reflection_class_factory'));
        $this->assertFalse($container->has('database_connections'));
        $this->assertTrue($container->has('core_fatal_exception_factory'));
        $this->assertTrue($container->has('request_input'));
        $this->assertTrue($container->has('error_demonstrator_loader'));
        $this->assertTrue($container->has('error_demonstrator_factory'));
        $this->assertFalse($container->has('adodb_session_environment'));
        $this->assertTrue($container->has('block_exception_factory'));
        $this->assertTrue($container->has('meta_row_factory'));
        $this->assertTrue($container->has('delayed_meta_factory'));
        $this->assertTrue($container->has('meta_maker_factory'));
        $this->assertTrue($container->has('view_keeper_factory'));
        $this->assertTrue($container->has('view_loader_json_keeper_factory'));
        $this->assertTrue($container->has('view_loader_text_keeper_factory'));
        $this->assertTrue($container->has('view_loader_state_factory'));
        $this->assertTrue($container->has('view_router_factory'));
        $this->assertTrue($container->has('transfer_exception_factory'));
        $this->assertFalse($container->has('template_exception_factory'));
        $this->assertTrue($container->has('error500_exception_factory'));
        $this->assertTrue($container->has('plain_exception_factory'));
        $this->assertTrue($container->has('upload_size_limit_provider'));
        $this->assertTrue($container->has('bootstrap_runtime'));
        $this->assertTrue($container->has('block_context'));
        $this->assertTrue($container->has('plain_file_context'));
        $this->assertTrue($container->has('transfer'));

        $this->assertSame($requestInput, $container->get('request_input'));
        $this->assertSame($requestInput, $container->get('request_input'));
        $this->assertSame(1, $requestInputCalls);
        $this->assertIsCallable($container->get('core_fatal_exception_factory'));
        $this->assertSame(['value'], ($container->get('array_adducer'))('value'));
        $this->assertSame(['a' => 1, 'b' => 2], ($container->get('recursive_merger'))(['a' => 1], ['b' => 2]));
        $this->assertSame('fallback', ($container->get('array_value_reader'))([], 'missing', 'fallback'));
        $this->assertTrue(($container->get('array_like_checker'))(new ArrayObject()));
        $this->assertSame(self::class, ($container->get('class_name_resolver'))($this));
        $this->assertSame('ApplicationSupportServiceRegistrarTest', ($container->get('short_class_name_resolver'))($this));
        $this->assertSame('FanTest', ($container->get('namespace_resolver'))('FanTest\Nested\Sample', 2));
        $this->assertInstanceOf(ReflectionClass::class, $container->get('reflection_class_factory')->create($this));
        $this->assertSame($runtime, $container->get('bootstrap_runtime'));
        $this->assertCount(1, $runtimeCalls);
        $this->assertSame($blockExceptionFactory, $container->get('block_exception_factory'));
        $this->assertIsCallable($container->get('delayed_meta_factory'));
        $this->assertIsCallable($container->get('meta_maker_factory'));
        $this->assertIsCallable($container->get('view_keeper_factory'));
        $this->assertIsCallable($container->get('view_loader_json_keeper_factory'));
        $this->assertIsCallable($container->get('view_loader_text_keeper_factory'));
        $this->assertIsCallable($container->get('view_loader_state_factory'));
        $this->assertIsCallable($container->get('view_router_factory'));
        $this->assertIsCallable($container->get('transfer_exception_factory'));
        $this->assertIsCallable($container->get('error500_exception_factory'));
        $this->assertIsCallable($container->get('plain_exception_factory'));
        $this->assertInstanceOf(plain_exception_factory::class, $container->get('plain_exception_factory'));
        $this->assertInstanceOf(transfer::class, $container->get('transfer'));
        $this->assertTrue($blockExceptionFactory->dependenciesSet);
    }

    private function containerWithSupportDependencies(): container
    {
        $container = new container();
        $container
            ->set('service_listener_state', (object)['name' => 'listener-state'])
            ->set('service_single_state', (object)['name' => 'single-state'])
            ->set('view_loader_state', (object)['name' => 'view-loader-state'])
            ->set('meta_maker_state', (object)['name' => 'meta-maker-state'])
            ->set('spec_file_image_row_state', (object)['name' => 'spec-file-state'])
            ->set('header_writer', (object)['name' => 'header-writer'])
            ->set('error_log_writer', (object)['name' => 'error-log-writer'])
            ->set('error_demonstrator_file_storage', (object)['name' => 'error-demonstrator-storage'])
            ->set('error_demonstrator_loader', (object)['name' => 'error-demonstrator-loader'])
            ->set('request', (object)['name' => 'request'])
            ->set('error', (object)['name' => 'error'])
            ->set('application', (object)['name' => 'application'])
            ->set('translation', (object)['name' => 'translation'])
            ->set('entity', (object)['name' => 'entity'])
            ->set('image_metadata_reader', (object)['name' => 'image-metadata-reader'])
            ->set('plain_file_storage', (object)['name' => 'plain-file-storage'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('image_modify', static fn(container $container, string $sourcePath): object => (object)['sourcePath' => $sourcePath], false);

        return $container;
    }
}

final class ApplicationSupportSerializerOperationsDouble
{
    public function stableKeyEncoder(): callable
    {
        return static fn(mixed $value): string => serialize($value);
    }
}

final class ApplicationSupportBlockExceptionFactoryDouble
{
    public bool $dependenciesSet = false;

    public function __invoke(): object
    {
        return (object)['name' => 'block-exception'];
    }

    public function setExceptionDependencies(mixed ...$dependencies): void
    {
        $this->dependenciesSet = $dependencies !== [];
    }
}
