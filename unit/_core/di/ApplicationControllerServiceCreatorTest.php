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
