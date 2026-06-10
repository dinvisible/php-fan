<?php

declare(strict_types=1);

use fan\core\bootstrap\context_container_factory;
use fan\core\di\container;
use fan\core\di\container_interface;
use PHPUnit\Framework\TestCase;

final class ContextContainerFactoryTest extends TestCase
{
    public function testFactoryCreatesApplicationContainer(): void
    {
        $applicationContainer = new container();
        $applicationContainer->factory('config', static fn(): object => new stdClass());
        $applicationContainer->factory('php_array_file_loader', static fn(): callable => static fn(): array => []);
        $calls = 0;
        $factory = new context_container_factory(static function () use (&$calls, $applicationContainer): container {
            $calls++;

            return $applicationContainer;
        });
        $container = $factory();

        $this->assertSame($applicationContainer, $container);
        $this->assertTrue($container->has('config'));
        $this->assertTrue($container->has('php_array_file_loader'));
        $this->assertSame(1, $calls);
    }

    public function testFactoryRejectsInvalidApplicationContainerFactoryResult(): void
    {
        $factory = new context_container_factory(static fn(): object => new stdClass());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Application container factory must return a container.');

        $factory();
    }

    public function testFactoryDoesNotOwnApplicationContainerConstruction(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/context_container_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class context_container_factory', $source);
        $this->assertStringContainsString('public function __construct(callable $applicationContainerFactory)', $source);
        $this->assertStringNotContainsString('containerRegistryStateFactory', $source);
        $this->assertStringNotContainsString('containerRegistryStateSetter', $source);
        $this->assertStringNotContainsString('container_registry_state', $source);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/factory/application_container_factory.php';", $source);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/di/container_registry.php';", $source);
        $this->assertStringNotContainsString('new application_container_factory()', $source);
        $this->assertStringContainsString('public function __invoke(?context $context = null): container_interface', $source);
        $this->assertStringContainsString('$container = ($this->applicationContainerFactory)($context);', $source);
        $this->assertStringNotContainsString('\fan\core\di\container_registry::setState($state);', $source);
    }
}
