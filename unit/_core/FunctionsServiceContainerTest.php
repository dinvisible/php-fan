<?php

declare(strict_types=1);

use fan\core\di\container;
use fan\core\di\container_interface;
use fan\core\di\service_factory_interface;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class FunctionsServiceContainerTest extends TestCase
{
    protected function setUp(): void
    {
        $this->loadCoreFunctions();
    }

    protected function tearDown(): void
    {
        reset_service_container();
    }

    public function testDefaultContainerRegistersFirstServiceLayer(): void
    {
        $container = service_container();

        foreach ([
            'config',
            'database',
            'database_by_param',
            'request',
            'cache',
            'session',
            'email',
            'role',
            'entity',
            'tab',
            'template',
            'json',
            'error',
            'header',
            'locale',
            'application',
        ] as $serviceName) {
            $this->assertTrue($container->has($serviceName), $serviceName . ' should be registered.');
        }
    }

    public function testServiceUsesExplicitContainerDependency(): void
    {
        $database = new stdClass();
        $container = new container();
        $container->set('database', $database);

        service_container($container);

        $this->assertSame($database, service('database'));
    }

    public function testServiceAcceptsContainerContractImplementation(): void
    {
        $database = new stdClass();

        service_container(new class($database) implements container_interface {
            public function __construct(private object $database)
            {
            }

            public function has(string $id): bool
            {
                return $id === 'database';
            }

            public function get(string $id, mixed ...$arguments): mixed
            {
                return $this->database;
            }
        });

        $this->assertSame($database, service('database'));
    }

    public function testContainerAwareConsumerUsesInjectedContainerContract(): void
    {
        $request = new stdClass();
        $consumer = new class {
            use \fan\core\di\container_aware_trait;

            public function fetchRequest(): object
            {
                return $this->containerService('request');
            }
        };
        $consumer->setServiceContainer(new class($request) implements container_interface {
            public function __construct(private object $request)
            {
            }

            public function has(string $id): bool
            {
                return $id === 'request';
            }

            public function get(string $id, mixed ...$arguments): mixed
            {
                return $this->request;
            }
        });

        $this->assertSame($request, $consumer->fetchRequest());
    }

    public function testServicePassesArgumentsToContainerFactory(): void
    {
        $container = new container();
        $container->factory(
            'cache',
            static fn(container_interface $container, string $type): object => (object)['type' => $type],
            false
        );

        service_container($container);

        $cache = service('cache', 'session_data');

        $this->assertSame('session_data', $cache->type);
    }

    public function testServiceFallsBackToConfiguredFactory(): void
    {
        service_factory(new class implements service_factory_interface {
            public function has(string $serviceName): bool
            {
                return $serviceName === 'custom';
            }

            public function create(string $serviceName, array $arguments = []): mixed
            {
                return (object)[
                    'name' => $serviceName,
                    'arguments' => $arguments,
                ];
            }
        });

        $service = service('custom', ['alpha', 'beta']);

        $this->assertSame('custom', $service->name);
        $this->assertSame(['alpha', 'beta'], $service->arguments);
    }

    public function testUnknownServiceStillReturnsNull(): void
    {
        $this->assertNull(service('service_that_does_not_exist'));
    }

    private function loadCoreFunctions(): void
    {
        if (!function_exists('service')) {
            require_once dirname(__DIR__, 2) . '/_core/functions.php';
        }
    }
}
