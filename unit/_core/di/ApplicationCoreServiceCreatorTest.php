<?php

declare(strict_types=1);

use fan\core\di\application_core_service_creator;
use fan\core\di\container;
use PHPUnit\Framework\TestCase;
use fan\project\service\application;
use fan\project\service\debug;
use fan\project\service\header;
use fan\project\service\locale;
use fan\project\service\reflector;
use fan\project\service\request;
use fan\project\service\role;


final class ApplicationCoreServiceCreatorTest extends TestCase
{
    public function testRequestServiceCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = new container();
        $container
            ->factory('request_input', static fn(): object => (object)['name' => 'request_input'])
            ->factory('bootstrap_runtime', static fn(): object => (object)['name' => 'runtime'])
            ->factory('config', static fn(): object => (object)['name' => 'config'])
            ->factory('json', static fn(container $container, bool $useBase64 = false): object => (object)['useBase64' => $useBase64], false)
            ->factory('cookie', static fn(): object => (object)['name' => 'cookie'])
            ->factory('matcher', static fn(): object => (object)['name' => 'matcher'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('array_adducer', static fn(): callable => static fn(mixed $value): array => is_array($value) ? $value : [$value])
            ->factory('recursive_merger', static fn(): callable => static fn(array $left, array $right): array => array_replace_recursive($left, $right))
            ->factory('array_value_reader', static fn(): callable => static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default)
            ->factory('class_name_resolver', static fn(): callable => static fn(object $object): string => get_class($object));
        $received = [];

        $service = (new application_core_service_creator())->createRequestService(
            $container,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'request'];
            }
        );

        $this->assertSame('request', $service->service);
        $this->assertSame('\\' . request::class, $received[0] ?? null);
        $this->assertSame($container->get('request_input'), $received[1] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[2] ?? null);
        $this->assertSame('cache-key', ($received[8])('cache-key')->type);
        $this->assertSame($container->get('array_adducer'), $received[9] ?? null);
        $this->assertSame($container->get('recursive_merger'), $received[10] ?? null);
        $this->assertSame($container->get('array_value_reader'), $received[11] ?? null);
        $this->assertSame($container->get('class_name_resolver'), $received[12] ?? null);
    }

    public function testRoleServiceCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = new container();
        $container
            ->factory('current_user', static fn(): object => (object)['name' => 'current_user'])
            ->factory('current_user_checked', static fn(): object => (object)['name' => 'current_user_checked'])
            ->factory('session', static fn(container $container, string $namespace, string $group): object => (object)['namespace' => $namespace, 'group' => $group], false)
            ->factory('error', static fn(): object => (object)['name' => 'error'])
            ->factory('current_user_space', static fn(): string => 'frontend')
            ->factory('date', static fn(container $container, string $date, mixed $format = null): object => (object)['date' => $date, 'format' => $format], false)
            ->factory('bootstrap_runtime', static fn(): object => (object)['name' => 'runtime'])
            ->factory('config', static fn(): object => (object)['name' => 'config'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false);
        $received = [];

        $service = (new application_core_service_creator())->createRoleService(
            $container,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'role'];
            }
        );

        $this->assertSame('role', $service->service);
        $this->assertSame('\\' . role::class, $received[0] ?? null);
        $this->assertSame('current_user_checked', ($received[1])(true)->name);
        $this->assertSame('main', ($received[2])('main', 'custom')->namespace);
        $this->assertSame('frontend', ($received[4])());
        $this->assertSame('cache-key', ($received[8])('cache-key')->type);
    }

    public function testApplicationServiceCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = new container();
        $container
            ->factory('bootstrap_runtime', static fn(): object => (object)['name' => 'runtime'])
            ->factory('config', static fn(): object => (object)['name' => 'config'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('array_adducer', static fn(): callable => static fn(mixed $value): array => is_array($value) ? $value : [$value]);
        $received = [];

        $service = (new application_core_service_creator())->createApplicationService(
            $container,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'application'];
            }
        );

        $this->assertSame('application', $service->service);
        $this->assertSame('\\' . application::class, $received[0] ?? null);
        $this->assertTrue($received[1] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[2] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[3] ?? null);
        $this->assertSame($container->get('config'), $received[4] ?? null);
        $this->assertSame('cache-key', ($received[5])('cache-key')->type);
        $this->assertSame($container->get('array_adducer'), $received[6] ?? null);
    }

    public function testDebugServiceCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = new container();
        $container
            ->factory('tab', static fn(): object => (object)['name' => 'tab'])
            ->factory('request_input', static fn(): object => (object)['name' => 'request_input'])
            ->factory('bootstrap_runtime', static fn(): object => (object)['name' => 'runtime'])
            ->factory('config', static fn(): object => (object)['name' => 'config'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('meta_file_storage', static fn(): object => (object)['name' => 'meta_file_storage'])
            ->factory('array_adducer', static fn(): callable => static fn(mixed $value): array => is_array($value) ? $value : [$value])
            ->factory('reflection_class_factory', static fn(): object => (object)['name' => 'reflection_class_factory']);
        $received = [];

        $service = (new application_core_service_creator())->createDebugService(
            $container,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'debug'];
            }
        );

        $this->assertSame('debug', $service->service);
        $this->assertSame('\\' . debug::class, $received[0] ?? null);
        $this->assertTrue($received[1] ?? null);
        $this->assertSame($container->get('tab'), $received[2] ?? null);
        $this->assertSame($container->get('request_input'), $received[3] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[4] ?? null);
        $this->assertSame($container->get('config'), $received[5] ?? null);
        $this->assertSame('cache-key', ($received[6])('cache-key')->type);
        $this->assertSame($container->get('meta_file_storage'), $received[7] ?? null);
        $this->assertSame($container->get('array_adducer'), $received[8] ?? null);
        $this->assertSame($container->get('reflection_class_factory'), $received[9] ?? null);
    }

    public function testReflectorServiceCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = new container();
        $container
            ->factory('bootstrap_runtime', static fn(): object => (object)['name' => 'runtime'])
            ->factory('config', static fn(): object => (object)['name' => 'config'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('reflection_class_factory', static fn(): object => (object)['name' => 'reflection_class_factory']);
        $received = [];

        $service = (new application_core_service_creator())->createReflectorService(
            $container,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'reflector'];
            }
        );

        $this->assertSame('reflector', $service->service);
        $this->assertSame('\\' . reflector::class, $received[0] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[1] ?? null);
        $this->assertSame($container->get('config'), $received[2] ?? null);
        $this->assertSame('cache-key', ($received[3])('cache-key')->type);
        $this->assertSame($container->get('reflection_class_factory'), $received[4] ?? null);
        $this->assertArrayNotHasKey(5, $received);
    }

    public function testLocaleServiceCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = new container();
        $container
            ->factory('entity', static fn(): object => (object)['name' => 'entity'])
            ->factory('tab', static fn(): object => (object)['name' => 'tab'])
            ->factory('session', static fn(container $container, string $namespace, string $group): object => (object)['namespace' => $namespace, 'group' => $group], false)
            ->factory('request', static fn(): object => (object)['name' => 'request'])
            ->factory('cookie', static fn(container $container, mixed $path = null, mixed $domain = null): object => (object)['path' => $path, 'domain' => $domain], false)
            ->factory('matcher', static fn(): object => (object)['name' => 'matcher'])
            ->factory('bootstrap_runtime', static fn(): object => (object)['name' => 'runtime'])
            ->factory('config', static fn(): object => (object)['name' => 'config'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('array_adducer', static fn(): callable => static fn(mixed $value): array => is_array($value) ? $value : [$value])
            ->factory('class_name_resolver', static fn(): callable => static fn(object $object): string => get_class($object));
        $received = [];

        $service = (new application_core_service_creator())->createLocaleService(
            $container,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'locale'];
            }
        );

        $this->assertSame('locale', $service->service);
        $this->assertSame('\\' . locale::class, $received[0] ?? null);
        $this->assertSame($container->get('entity'), ($received[2])());
        $this->assertSame($container->get('tab'), ($received[3])());
        $this->assertSame('locale', ($received[4])('locale', 'service')->namespace);
        $this->assertSame($container->get('request'), ($received[5])());
        $this->assertSame('/', ($received[6])('/', null)->path);
        $this->assertSame($container->get('matcher'), ($received[7])());
        $this->assertSame($container->get('bootstrap_runtime'), $received[8] ?? null);
        $this->assertSame($container->get('config'), $received[9] ?? null);
        $this->assertSame('runtime', ($received[10])('runtime')->type);
        $this->assertSame($container->get('array_adducer'), $received[11] ?? null);
        $this->assertSame($container->get('class_name_resolver'), $received[12] ?? null);
    }

    public function testHeaderServiceCreatorPassesRecursiveMergerToInjectedFactory(): void
    {
        $container = new container();
        $container
            ->factory('request_input', static fn(): object => (object)['name' => 'request_input'])
            ->factory('header_writer', static fn(): object => (object)['name' => 'header_writer'])
            ->factory('bootstrap_runtime', static fn(): object => (object)['name' => 'runtime'])
            ->factory('config', static fn(): object => (object)['name' => 'config'])
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('recursive_merger', static fn(): callable => static fn(array $left, array $right): array => array_replace_recursive($left, $right));
        $received = [];

        $service = (new application_core_service_creator())->createHeaderService(
            $container,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'header'];
            }
        );

        $this->assertSame('header', $service->service);
        $this->assertSame('\\' . header::class, $received[0] ?? null);
        $this->assertTrue($received[1] ?? null);
        $this->assertSame($container->get('request_input'), $received[2] ?? null);
        $this->assertSame($container->get('header_writer'), $received[3] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[4] ?? null);
        $this->assertSame($container->get('config'), $received[5] ?? null);
        $this->assertSame('cache-key', ($received[6])('cache-key')->type);
        $this->assertSame($container->get('recursive_merger'), $received[7] ?? null);
    }
}
