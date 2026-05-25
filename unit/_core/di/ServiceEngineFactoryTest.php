<?php

declare(strict_types=1);

use fan\core\di\service_engine_factory;
use PHPUnit\Framework\TestCase;

final class ServiceEngineFactoryTest extends TestCase
{
    public function testFactoryDelegatesRequestedEngineClassToConfiguredFactory(): void
    {
        $delegatedClass = null;
        $delegatedArguments = null;
        $factory = new service_engine_factory(
            static function (string $className, array $arguments) use (&$delegatedClass, &$delegatedArguments): object {
                $delegatedClass = $className;
                $delegatedArguments = $arguments;

                return new $className(...$arguments);
            }
        );
        $engine = $factory(ServiceEngineFactoryTestEngine::class);

        $this->assertSame(ServiceEngineFactoryTestEngine::class, $delegatedClass);
        $this->assertSame([], $delegatedArguments);
        $this->assertInstanceOf(ServiceEngineFactoryTestEngine::class, $engine);
    }}

final class ServiceEngineFactoryTestEngine
{
}
