<?php

declare(strict_types=1);

use fan\core\di\container;
use fan\core\di\container_interface;
use PHPUnit\Framework\TestCase;

final class ContainerTest extends TestCase
{
    public function testContainerExposesStableLookupContract(): void
    {
        $container = new container();

        $this->assertInstanceOf(container_interface::class, $container);
    }

    public function testFactoryReceivesContainerAndRuntimeArguments(): void
    {
        $container = new container();
        $container->factory(
            'cache',
            static fn(container_interface $receivedContainer, string $type): object => (object)[
                'container' => $receivedContainer,
                'type' => $type,
            ],
            false
        );

        $service = $container->get('cache', 'page');

        $this->assertSame($container, $service->container);
        $this->assertSame('page', $service->type);
    }

    public function testSharedFactoryCachesArgumentLessService(): void
    {
        $container = new container();
        $container->factory('request', static fn(container_interface $container): object => new stdClass());

        $this->assertSame($container->get('request'), $container->get('request'));
    }

    public function testExplicitInstanceOverridesFactory(): void
    {
        $container = new container();
        $explicitService = new stdClass();

        $container->factory('database', static fn(container_interface $container): object => new stdClass());
        $container->set('database', $explicitService);

        $this->assertSame($explicitService, $container->get('database'));
    }

    public function testAliasResolvesRegisteredService(): void
    {
        $container = new container();
        $email = new stdClass();

        $container->set('email', $email);
        $container->alias('mailer', 'email');

        $this->assertTrue($container->has('mailer'));
        $this->assertSame($email, $container->get('mailer'));
    }

    public function testUnknownServiceThrowsClearException(): void
    {
        $container = new container();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Service "missing" is not registered');

        $container->get('missing');
    }
}
