<?php

declare(strict_types=1);

use fan\core\di\config_row_factory;
use PHPUnit\Framework\TestCase;
use fan\core\service\config\row;

final class ConfigRowFactoryTest extends TestCase
{
    public function testFactoryCreatesConfigRowWithInjectedSnapshotCallbacks(): void
    {
        $factory = new config_row_factory(
            new ConfigRowFactorySerializerOperationsDouble(),
            static fn(): \Throwable => new RuntimeException('service exception'),
            static fn(object|string $object): string => is_object($object) ? get_class($object) : $object
        );

        $row = $factory(['database' => ['host' => 'localhost']]);

        $this->assertInstanceOf(row::class, $row);
        $this->assertSame('localhost', $row->get(['database', 'host']));
    }}

final class ConfigRowFactorySerializerOperationsDouble
{
    public function phpSnapshotEncoder(): callable
    {
        return static fn(mixed $value): string => serialize($value);
    }

    public function phpSnapshotDecoder(): callable
    {
        return static fn(string $value): mixed => unserialize($value);
    }
}
