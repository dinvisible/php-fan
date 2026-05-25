<?php

declare(strict_types=1);

use fan\core\di\application_pager_service_creator;
use fan\core\di\container;
use PHPUnit\Framework\TestCase;
use fan\core\block\base;
use fan\project\service\pager;


final class ApplicationPagerServiceCreatorTest extends TestCase
{    public function testPagerCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = $this->containerWithPagerDependencies();
        $state = new ApplicationPagerStateDouble();
        $block = new ApplicationPagerBlockDouble();
        $received = [];

        $pager = (new application_pager_service_creator())->createPagerService(
            $container,
            $state,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'pager'];
            },
            $block
        );

        $this->assertSame('pager', $pager->service);
        $this->assertSame('\\' . pager::class, $received[0] ?? null);
        $this->assertSame($block, $received[1] ?? null);
        $this->assertSame($container->get('entity'), ($received[2])());
        $this->assertSame($container->get('tab'), ($received[3])());
        $this->assertSame($container->get('request'), ($received[4])());
        $this->assertSame($container->get('bootstrap_runtime'), $received[5] ?? null);
        $this->assertSame($container->get('config'), $received[6] ?? null);
        $this->assertSame('cache-key', ($received[7])('cache-key')->type);
        $this->assertSame($pager, $state->getInstance('pager-block'));
    }

    public function testPagerCreatorUsesInjectedError500FactoryForInvalidResolvedBlock(): void
    {
        $container = $this->containerWithPagerDependencies();
        $state = new ApplicationPagerStateDouble();
        $container
            ->factory('tab', static fn(): object => new ApplicationPagerInvalidTabDouble())
            ->factory(
                'error500_exception_factory',
                static fn(): callable => static fn(string $message): Throwable => new RuntimeException($message)
            );

        try {
            (new application_pager_service_creator())->createPagerService(
                $container,
                $state,
                static fn(): object => (object)[],
                'missing-block'
            );
            $this->fail('Expected injected error500 exception.');
        } catch (Throwable $exception) {
            $this->assertSame('Incorect call service pager. Please point block of data or its name.', $exception->getMessage());
        }
    }

    public function testPagerCreatorResolvesNamedBlockBeforeCallingFactory(): void
    {
        $container = $this->containerWithPagerDependencies();
        $state = new ApplicationPagerStateDouble();
        $resolvedBlock = new ApplicationPagerBlockDouble();
        $received = [];
        $container->factory('tab', static fn(): object => new ApplicationPagerTabDouble($resolvedBlock));

        $pager = (new application_pager_service_creator())->createPagerService(
            $container,
            $state,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'pager'];
            },
            'pager-block'
        );

        $this->assertSame('pager', $pager->service);
        $this->assertSame($resolvedBlock, $received[1] ?? null);
        $this->assertSame($pager, $state->getInstance('pager-block'));
    }

    private function containerWithPagerDependencies(): container
    {
        $container = new container();
        $container
            ->factory('entity', static fn(): object => (object)['name' => 'entity'])
            ->factory('tab', static fn(): object => new ApplicationPagerTabDouble(new ApplicationPagerBlockDouble()))
            ->factory('request', static fn(): object => (object)['name' => 'request'])
            ->factory('bootstrap_runtime', static fn(): object => (object)['name' => 'runtime'])
            ->factory('config', static fn(): object => (object)['name' => 'config'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('role', static fn(): object => (object)['name' => 'role'])
            ->factory('session', static fn(container $container, string $namespace, string $group): object => (object)['namespace' => $namespace, 'group' => $group], false)
            ->factory('locale', static fn(): object => (object)['name' => 'locale'])
            ->factory('transfer', static fn(): object => (object)['name' => 'transfer'])
            ->factory('translation', static fn(): object => (object)['name' => 'translation'])
            ->factory('date', static fn(container $container, string $date, mixed $format = null): object => (object)['date' => $date, 'format' => $format], false)
            ->factory('array_adducer', static fn(): callable => static fn(mixed $value): array => is_array($value) ? $value : [$value])
            ->factory('recursive_merger', static fn(): callable => static fn(mixed ...$values): array => array_replace_recursive(...$values))
            ->factory('array_value_reader', static fn(): callable => static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default)
            ->factory('class_name_resolver', static fn(): callable => static fn(object $object): string => get_class($object))
            ->factory('error500_exception_factory', static fn(): callable => static fn(string $message): Throwable => new RuntimeException($message));

        return $container;
    }
}

final class ApplicationPagerStateDouble
{
    private array $instances = [];

    public function getInstance(string $name): mixed
    {
        return $this->instances[$name] ?? null;
    }

    public function setInstance(string $name, mixed $instance): void
    {
        $this->instances[$name] = $instance;
    }
}

final class ApplicationPagerTabDouble
{
    public function __construct(private mixed $block)
    {
    }

    public function getTabBlock(string $name): mixed
    {
        return $this->block;
    }
}

final class ApplicationPagerInvalidTabDouble
{
    public function getTabBlock(string $name): object
    {
        return (object)['name' => $name];
    }
}

final class ApplicationPagerBlockDouble extends base
{
    public function __construct()
    {
    }

    public function getBlockName(): string
    {
        return 'pager-block';
    }
}
