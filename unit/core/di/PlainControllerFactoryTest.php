<?php

declare(strict_types=1);

use fan\core\di\plain_controller_factory;
use PHPUnit\Framework\TestCase;

final class PlainControllerFactoryTest extends TestCase
{
    public function testFactoryCreatesControllerWithKeyAndDependencies(): void
    {
        $plain = new stdClass();
        $dependency = new stdClass();
        $delegatedClass = null;
        $delegatedArguments = null;
        $factory = new plain_controller_factory(
            static function (string $className, array $arguments) use (&$delegatedClass, &$delegatedArguments): object {
                $delegatedClass = $className;
                $delegatedArguments = $arguments;

                return new $className(...$arguments);
            }
        );
        $controller = $factory(
            PlainControllerFactoryControllerDouble::class,
            $plain,
            'asset',
            [$dependency]
        );

        $this->assertSame(PlainControllerFactoryControllerDouble::class, $delegatedClass);
        $this->assertSame([$plain, 'asset', $dependency], $delegatedArguments);
        $this->assertInstanceOf(PlainControllerFactoryControllerDouble::class, $controller);
        $this->assertSame($plain, $controller->plainService);
        $this->assertSame('asset', $controller->controllerKey);
        $this->assertSame([$dependency], $controller->dependencies);
    }}

final class PlainControllerFactoryControllerDouble
{
    public array $dependencies;

    public function __construct(
        public object $plainService,
        public int|string $controllerKey,
        object ...$dependencies
    ) {
        $this->dependencies = $dependencies;
    }
}
