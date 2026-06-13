<?php

declare(strict_types=1);

use fan\core\di\application_controller_service_creator;
use fan\core\di\container;
use PHPUnit\Framework\TestCase;
use fan\core\plain\db_file;
use fan\core\plain\obfuscator;
use fan\project\service\plain;


final class ApplicationControllerServiceCreatorTest extends TestCase
{
    public function testPlainCreatorPassesExplicitDependenciesAndControllerDependencyRules(): void
    {
        $container = $this->containerWithControllerDependencies();
        $plainControllerFactory = static fn(): object => (object)['name' => 'plain_controller_factory'];
        $received = [];

        $service = (new application_controller_service_creator())->createPlainService(
            $container,
            $plainControllerFactory,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'plain'];
            }
        );

        $this->assertSame('plain', $service->service);
        $this->assertSame('\\' . plain::class, $received[0] ?? null);
        $this->assertTrue($received[1] ?? null);
        $this->assertSame($container->get('matcher'), $received[2] ?? null);
        $this->assertSame('plain', ($received[3])()->configType);
        $this->assertSame($container->get('header'), $received[4] ?? null);
        $this->assertSame($plainControllerFactory, $received[6] ?? null);
        $this->assertSame('cache-key', ($received[9])('cache-key')->type);

        $controllerDependenciesFactory = $received[5];
        $obfuscatorDependencies = $controllerDependenciesFactory(obfuscator::class, 'hash', (object)[]);
        $this->assertSame('code', ($obfuscatorDependencies[0])('code')->type);
        $this->assertSame($container->get('request'), $obfuscatorDependencies[1] ?? null);
        $this->assertSame([$container->get('plain_file_context')], $controllerDependenciesFactory(db_file::class, 'file', (object)[]));
        $this->assertSame([], $controllerDependenciesFactory(stdClass::class, 'empty', (object)[]));
    }

    public function testProjectServiceClassAvailabilityCheckIsInjected(): void
    {
        $checkedClasses = [];
        $creator = new application_controller_service_creator(
            static function (string $className) use (&$checkedClasses): bool {
                $checkedClasses[] = $className;

                return false;
            }
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Service "plain" does not expose a project class.');

        try {
            $creator->createPlainService(
                $this->containerWithControllerDependencies(),
                static fn(): object => (object)['name' => 'plain_controller_factory'],
                static fn(): object => (object)['service' => 'plain']
            );
        } finally {
            $this->assertSame(['\\' . plain::class], $checkedClasses);
        }
    }

    public function testControllerCreatorUsesDependencyBundle(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_controller_service_creator.php');
        $bundleSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_controller_service_dependencies.php');
        $plainSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_controller_plain_dependencies.php');
        $plainRouteSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_controller_plain_route_dependencies.php');
        $matcherPlainRouteSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_controller_matcher_plain_route_dependencies.php');
        $headerPlainRouteSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_controller_header_plain_route_dependencies.php');
        $plainConfigSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_controller_plain_config_dependencies.php');
        $handlerSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_controller_handler_dependencies.php');
        $obfuscatorHandlerSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_controller_obfuscator_handler_dependencies.php');
        $obfuscatorFactoryHandlerSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_controller_obfuscator_factory_handler_dependencies.php');
        $requestHandlerSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_controller_request_handler_dependencies.php');
        $plainFileHandlerSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_controller_plain_file_handler_dependencies.php');
        $runtimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_controller_runtime_dependencies.php');
        $bootstrapRuntimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_controller_bootstrap_runtime_dependencies.php');
        $configCacheRuntimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_controller_config_cache_runtime_dependencies.php');
        $configRuntimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_controller_config_runtime_dependencies.php');
        $cacheRuntimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_controller_cache_runtime_dependencies.php');

        $this->assertIsString($source);
        $this->assertIsString($bundleSource);
        $this->assertIsString($plainSource);
        $this->assertIsString($plainRouteSource);
        $this->assertIsString($matcherPlainRouteSource);
        $this->assertIsString($headerPlainRouteSource);
        $this->assertIsString($plainConfigSource);
        $this->assertIsString($handlerSource);
        $this->assertIsString($obfuscatorHandlerSource);
        $this->assertIsString($obfuscatorFactoryHandlerSource);
        $this->assertIsString($requestHandlerSource);
        $this->assertIsString($plainFileHandlerSource);
        $this->assertIsString($runtimeSource);
        $this->assertIsString($bootstrapRuntimeSource);
        $this->assertIsString($configCacheRuntimeSource);
        $this->assertIsString($configRuntimeSource);
        $this->assertIsString($cacheRuntimeSource);
        $this->assertStringContainsString('private function controllerDependencies(container_interface $container): application_controller_service_dependencies', $source);
        $this->assertStringContainsString('return new application_controller_service_dependencies($container);', $source);
        $this->assertStringContainsString('$controllerDependencies = $this->controllerDependencies($container);', $source);
        $this->assertStringContainsString('$controllerDependencies->matcher()', $source);
        $this->assertStringContainsString('$controllerDependencies->plainConfigFactory()', $source);
        $this->assertStringContainsString('$controllerDependencies->plainFileContext()', $source);
        $this->assertStringContainsString('final class application_controller_service_dependencies', $bundleSource);
        $this->assertStringContainsString('new application_controller_plain_dependencies($container)', $bundleSource);
        $this->assertStringContainsString('new application_controller_handler_dependencies($container)', $bundleSource);
        $this->assertStringContainsString('new application_controller_runtime_dependencies($container)', $bundleSource);
        $this->assertStringContainsString('new application_controller_plain_route_dependencies($container)', $plainSource);
        $this->assertStringContainsString('new application_controller_plain_config_dependencies($container)', $plainSource);
        $this->assertStringContainsString('return $this->route->matcher();', $plainSource);
        $this->assertStringContainsString('return $this->config->plainConfigFactory();', $plainSource);
        $this->assertStringContainsString('new application_controller_matcher_plain_route_dependencies($container)', $plainRouteSource);
        $this->assertStringContainsString('new application_controller_header_plain_route_dependencies($container)', $plainRouteSource);
        $this->assertStringContainsString('return $this->matcher->matcher();', $plainRouteSource);
        $this->assertStringContainsString('return $this->header->header();', $plainRouteSource);
        $this->assertStringContainsString('new application_controller_obfuscator_handler_dependencies($container)', $handlerSource);
        $this->assertStringContainsString('new application_controller_plain_file_handler_dependencies($container)', $handlerSource);
        $this->assertStringContainsString('return $this->obfuscator->request();', $handlerSource);
        $this->assertStringContainsString('return $this->plainFile->plainFileContext();', $handlerSource);
        $this->assertStringContainsString('new application_controller_obfuscator_factory_handler_dependencies($container)', $obfuscatorHandlerSource);
        $this->assertStringContainsString('new application_controller_request_handler_dependencies($container)', $obfuscatorHandlerSource);
        $this->assertStringContainsString('return $this->obfuscatorFactory->obfuscatorFactory();', $obfuscatorHandlerSource);
        $this->assertStringContainsString('return $this->request->request();', $obfuscatorHandlerSource);
        $this->assertStringContainsString('new application_controller_bootstrap_runtime_dependencies($container)', $runtimeSource);
        $this->assertStringContainsString('new application_controller_config_cache_runtime_dependencies($container)', $runtimeSource);
        $this->assertStringContainsString('return $this->bootstrap->bootstrapRuntime();', $runtimeSource);
        $this->assertStringContainsString('return $this->configCache->cacheFactory();', $runtimeSource);
        $this->assertStringContainsString('new application_controller_config_runtime_dependencies($container)', $configCacheRuntimeSource);
        $this->assertStringContainsString('new application_controller_cache_runtime_dependencies($container)', $configCacheRuntimeSource);
        $this->assertStringContainsString('return $this->config->config();', $configCacheRuntimeSource);
        $this->assertStringContainsString('return $this->cache->cacheFactory();', $configCacheRuntimeSource);
        $this->assertStringContainsString('return $this->container->get(service_id::MATCHER);', $matcherPlainRouteSource);
        $this->assertStringContainsString('return $this->container->get(service_id::HEADER);', $headerPlainRouteSource);
        $this->assertStringContainsString('return fn(): mixed => $this->container->get(service_id::CONFIG, \'plain\');', $plainConfigSource);
        $this->assertStringContainsString('return fn(string $type): mixed => $this->container->get(service_id::OBFUSCATOR, $type);', $obfuscatorFactoryHandlerSource);
        $this->assertStringContainsString('return $this->container->get(service_id::REQUEST);', $requestHandlerSource);
        $this->assertStringContainsString('return $this->container->get(service_id::PLAIN_FILE_CONTEXT);', $plainFileHandlerSource);
        $this->assertStringContainsString('return $this->container->get(service_id::BOOTSTRAP_RUNTIME);', $bootstrapRuntimeSource);
        $this->assertStringContainsString('return $this->container->get(service_id::CONFIG);', $configRuntimeSource);
        $this->assertStringContainsString('return fn(string $type): mixed => $this->container->get(service_id::CACHE, $type);', $cacheRuntimeSource);
    }

    private function containerWithControllerDependencies(): container
    {
        $container = new container();
        $container
            ->factory('matcher', static fn(): object => (object)['name' => 'matcher'])
            ->factory('config', static fn(container $container, string $configType = 'service'): object => (object)['configType' => $configType], false)
            ->factory('bootstrap_runtime', static fn(): object => (object)['name' => 'runtime'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('header', static fn(): object => (object)['name' => 'header'])
            ->factory('obfuscator', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('request', static fn(): object => (object)['name' => 'request'])
            ->factory('plain_file_context', static fn(): object => (object)['name' => 'plain_file_context']);

        return $container;
    }
}
