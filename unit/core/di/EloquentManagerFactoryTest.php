<?php

declare(strict_types=1);

use fan\core\di\eloquent_manager_factory;
use Illuminate\Database\Capsule\Manager as Capsule;
use PHPUnit\Framework\TestCase;

final class EloquentManagerFactoryTest extends TestCase
{
    public function testFactoryCreatesCapsuleFromProjectStyleConfig(): void
    {
        $capsule = (new eloquent_manager_factory())([
            'DEFAULT_CONNECTION' => 'common',
            'SET_AS_GLOBAL' => '0',
            'BOOT_ELOQUENT' => '0',
            'CONNECTIONS' => [
                'common' => [
                    'DRIVER' => 'mysql',
                    'HOST' => 'localhost',
                    'DATABASE' => 'php_fan_test',
                    'USER' => 'test_fan',
                    'PASSWORD' => '123',
                    'CHARSET' => 'utf8mb4',
                    'COLLATION' => 'utf8mb4_unicode_ci',
                    'PREFIX' => 'fan_',
                ],
            ],
        ]);

        $this->assertInstanceOf(Capsule::class, $capsule);
        $this->assertSame('common', $capsule->getDatabaseManager()->getDefaultConnection());
        $this->assertSame('php_fan_test', $capsule->getConnection('common')->getConfig('database'));
        $this->assertSame('test_fan', $capsule->getConnection('common')->getConfig('username'));
        $this->assertSame('fan_', $capsule->getConnection('common')->getConfig('prefix'));
    }

    public function testFactoryCreatesCapsuleFromLaravelStyleConfigObject(): void
    {
        $config = new EloquentManagerFactoryConfigDouble([
            'default' => 'sqlite_test',
            'global' => false,
            'boot' => false,
            'connections' => [
                'sqlite_test' => [
                    'driver' => 'sqlite',
                    'database' => ':memory:',
                    'prefix' => '',
                ],
            ],
        ]);

        $capsule = (new eloquent_manager_factory())($config);

        $this->assertSame('sqlite_test', $capsule->getDatabaseManager()->getDefaultConnection());
        $this->assertSame(':memory:', $capsule->getConnection('sqlite_test')->getConfig('database'));
    }

    public function testFactoryRequiresAtLeastOneConnection(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Eloquent connections are not configured.');

        (new eloquent_manager_factory())([]);
    }
}

final class EloquentManagerFactoryConfigDouble
{
    public function __construct(private array $data)
    {
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
