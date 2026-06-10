<?php

declare(strict_types=1);

use fan\core\di\user_engine_factory;
use PHPUnit\Framework\TestCase;
use fan\core\service\user\base;

final class UserEngineFactoryTest extends TestCase
{
    public function testFactoryDelegatesUserEngineWithSnapshotCodecs(): void
    {
        $serializerOperations = new UserEngineFactorySerializerOperationsDouble();
        $delegatedClass = null;
        $delegatedArguments = null;
        $arrayValueReader = static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
        $arrayAdducer = static fn(mixed $value): array => is_array($value) ? $value : (array)$value;
        $classNameResolver = static fn(object $object): string => get_class($object);

        $engine = (new user_engine_factory(
            $serializerOperations,
            static function (string $className, array $arguments) use (&$delegatedClass, &$delegatedArguments): object {
                $delegatedClass = $className;
                $delegatedArguments = $arguments;

                return new $className(...$arguments);
            },
            $arrayValueReader,
            $arrayAdducer,
            $classNameResolver
        ))(
            UserEngineFactoryEngineDouble::class,
            ['id' => 7]
        );

        $this->assertSame(UserEngineFactoryEngineDouble::class, $delegatedClass);
        $this->assertIsArray($delegatedArguments);
        $this->assertSame(['id' => 7], $delegatedArguments[0]);
        $this->assertIsCallable($delegatedArguments[1]);
        $this->assertIsCallable($delegatedArguments[2]);
        $this->assertIsCallable($delegatedArguments[3]);
        $this->assertIsCallable($delegatedArguments[4]);
        $this->assertIsCallable($delegatedArguments[5]);
        $this->assertInstanceOf(UserEngineFactoryEngineDouble::class, $engine);
        $this->assertSame(['id' => 7], $engine->constructorArgs[0]);
        $this->assertIsCallable($engine->constructorArgs[1]);
        $this->assertIsCallable($engine->constructorArgs[2]);
        $this->assertIsCallable($engine->constructorArgs[3]);
        $this->assertIsCallable($engine->constructorArgs[4]);
        $this->assertIsCallable($engine->constructorArgs[5]);
        $this->assertSame('encoded:payload', ($engine->constructorArgs[1])('payload'));
        $this->assertSame(['decoded' => 'payload'], ($engine->constructorArgs[2])('payload'));
        $this->assertSame($arrayValueReader, $engine->constructorArgs[3]);
        $this->assertSame($arrayAdducer, $engine->constructorArgs[4]);
        $this->assertSame($classNameResolver, $engine->constructorArgs[5]);
        $this->assertSame('fallback', ($engine->constructorArgs[3])([], 'missing', 'fallback'));
    }}

final class UserEngineFactoryEngineDouble extends base
{
    public array $constructorArgs = [];

    public function __construct(
        mixed $identifyer,
        ?callable $snapshotEncoder = null,
        ?callable $snapshotDecoder = null,
        ?callable $arrayValueReader = null,
        ?callable $arrayAdducer = null,
        ?callable $classNameResolver = null
    ) {
        $this->constructorArgs = func_get_args();
        parent::__construct($identifyer, $snapshotEncoder, $snapshotDecoder, $arrayValueReader, $arrayAdducer, $classNameResolver);
    }

    public function makePasswordHash(string $password): string
    {
        return 'hash:' . $password;
    }

    protected function _loadData(): bool
    {
        return false;
    }

    protected function _saveData(): bool
    {
        return false;
    }

    protected function _validateForSave(): bool
    {
        return false;
    }
}

final class UserEngineFactorySerializerOperationsDouble
{
    public function phpSnapshotEncoder(): callable
    {
        return static fn(mixed $value): string => 'encoded:' . (string)$value;
    }

    public function phpSnapshotDecoder(): callable
    {
        return static fn(string $payload): array => ['decoded' => $payload];
    }
}
