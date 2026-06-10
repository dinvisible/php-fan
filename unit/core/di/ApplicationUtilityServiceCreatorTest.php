<?php

declare(strict_types=1);

use fan\core\di\application_utility_service_creator;
use fan\core\di\container;
use PHPUnit\Framework\TestCase;
use fan\core\service\date_state;
use fan\project\service\date;
use fan\project\service\image_modify;
use fan\project\service\obfuscator;
use fan\project\service\soap;


final class ApplicationUtilityServiceCreatorTest extends TestCase
{    public function testObfuscatorCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = $this->containerWithUtilityDependencies();
        $state = new ApplicationUtilityNamedStateDouble();
        $received = [];

        $obfuscator = $this->creator()->createObfuscatorService(
            $container,
            $state,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'obfuscator'];
            },
            'Js'
        );

        $this->assertSame('obfuscator', $obfuscator->service);
        $this->assertSame('\\' . obfuscator::class, $received[0] ?? null);
        $this->assertSame('js', $received[1] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[2] ?? null);
        $this->assertSame($container->get('config'), $received[3] ?? null);
        $this->assertSame('cache-key', ($received[4])('cache-key')->type);
        $this->assertSame($container->get('php_array_file_loader'), $received[5] ?? null);
        $this->assertSame($container->get('obfuscator_file_storage'), $received[6] ?? null);
        $this->assertSame($obfuscator, $state->getInstance('js'));
    }

    public function testImageModifyCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = $this->containerWithUtilityDependencies();
        $state = new ApplicationUtilityNamedStateDouble();
        $received = [];

        $image = $this->creator()->createImageModifyService(
            $container,
            $state,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'image'];
            },
            'image_modify',
            '/tmp/source.png',
            ['mode' => 'resize']
        );

        $this->assertSame('image', $image->service);
        $this->assertSame('\\' . image_modify::class, $received[0] ?? null);
        $this->assertSame('/tmp/source.png', $received[1] ?? null);
        $this->assertSame(['mode' => 'resize'], $received[2] ?? null);
        $this->assertSame($state, $received[3] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[4] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[5] ?? null);
        $this->assertSame($container->get('config'), $received[6] ?? null);
        $this->assertSame('cache-key', ($received[7])('cache-key')->type);
        $this->assertSame($container->get('image_metadata_reader'), $received[8] ?? null);
        $this->assertSame($container->get('image_resource_factory'), $received[9] ?? null);
        $this->assertSame($container->get('image_canvas_operations'), $received[10] ?? null);
        $this->assertSame($container->get('image_output_writer'), $received[11] ?? null);
        $this->assertSame($container->get('image_source_file_storage'), $received[12] ?? null);
        $this->assertSame('fallback', ($received[13])([], 'missing', 'fallback'));
        $this->assertSame($image, $state->getInstance('\\' . image_modify::class));
    }

    public function testSoapCreatorPassesExplicitDependenciesAndInitializesSoapObject(): void
    {
        $container = $this->containerWithUtilityDependencies();
        $received = [];

        $soap = $this->creator()->createSoapService(
            $container,
            static function (mixed ...$arguments) use (&$received): ApplicationUtilitySoapDouble {
                $received = $arguments;

                return new ApplicationUtilitySoapDouble();
            },
            '/tmp/service.wsdl',
            ['trace' => true],
            false
        );

        $this->assertInstanceOf(ApplicationUtilitySoapDouble::class, $soap);
        $this->assertSame('\\' . soap::class, $received[0] ?? null);
        $this->assertFalse($received[1] ?? null);
        $this->assertSame($container->get('error'), ($received[2])());
        $this->assertSame($container->get('bootstrap_runtime'), $received[3] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[4] ?? null);
        $this->assertSame($container->get('config'), $received[5] ?? null);
        $this->assertSame('cache-key', ($received[6])('cache-key')->type);
        $this->assertSame($container->get('php_runtime_settings'), $received[7] ?? null);
        $this->assertSame($container->get('soap_wsdl_file_storage'), $received[8] ?? null);
        $this->assertSame('fallback', ($received[9])([], 'missing', 'fallback'));
        $this->assertSame($container->get('class_name_resolver'), $received[10] ?? null);
        $this->assertSame(['/tmp/service.wsdl', ['trace' => true]], $soap->initializedWith);
    }

    public function testDateCreatorParsesConfiguredFormatAndPassesDependenciesToInjectedFactory(): void
    {
        $container = $this->containerWithUtilityDependencies();
        $state = new date_state();
        $received = [];

        $date = $this->creator()->createDateService(
            $container,
            $state,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'date'];
            },
            '2026-06-02 10:11:12',
            'mysql',
            'UTC',
            false
        );

        $this->assertSame('date', $date->service);
        $this->assertSame('\\' . date::class, $received[0] ?? null);
        $this->assertInstanceOf(DateTime::class, $received[1] ?? null);
        $this->assertSame('2026-06-02 10:11:12', ($received[1])->format('Y-m-d H:i:s'));
        $this->assertSame('mysql', $received[2] ?? null);
        $this->assertTrue($received[3] ?? null);
        $this->assertSame('UTC', $received[4] ?? null);
        $this->assertFalse($received[5] ?? null);
        $this->assertSame($state, $received[6] ?? null);
        $this->assertIsCallable($received[7] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[8] ?? null);
        $this->assertSame($container->get('config'), $received[9] ?? null);
        $this->assertSame('cache-key', ($received[10])('cache-key')->type);
        $this->assertSame($container->get('class_name_resolver'), $received[11] ?? null);
        $this->assertSame('fallback', ($received[12])([], 'missing', 'fallback'));
        $this->assertSame($container->get('config')->dateConfig, $state->getGlobalConfig());
    }

    public function testDateCreatorUsesInjectedExceptionFactoryForUnknownFormat(): void
    {
        $container = $this->containerWithUtilityDependencies();
        $state = new date_state();
        $createdMessages = [];
        $expected = new RuntimeException('factory exception');
        $creator = new application_utility_service_creator(
            static function (string $message) use (&$createdMessages, $expected): Throwable {
                $createdMessages[] = $message;

                return $expected;
            }
        );

        try {
            $creator->createDateService(
                $container,
                $state,
                static fn(): object => (object)[],
                '2026-06-02',
                'missing',
                'UTC',
                false
            );
            $this->fail('Expected date exception from injected factory.');
        } catch (Throwable $exception) {
            $this->assertSame($expected, $exception);
        }

        $this->assertSame(['Requested format "missing" isn\'t found.'], $createdMessages);
    }

    private function creator(): application_utility_service_creator
    {
        return new application_utility_service_creator(
            static fn(string $message): Throwable => new RuntimeException($message)
        );
    }

    private function containerWithUtilityDependencies(): container
    {
        $container = new container();
        $container
            ->factory('bootstrap_runtime', static fn(): object => (object)['name' => 'runtime'])
            ->factory('config', static fn(): ApplicationUtilityConfigDouble => new ApplicationUtilityConfigDouble())
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('php_array_file_loader', static fn(): callable => static fn(string $path): array => ['path' => $path])
            ->factory('obfuscator_file_storage', static fn(): object => (object)['name' => 'obfuscator-storage'])
            ->factory('image_metadata_reader', static fn(): object => (object)['name' => 'metadata'])
            ->factory('image_resource_factory', static fn(): object => (object)['name' => 'resource'])
            ->factory('image_canvas_operations', static fn(): object => (object)['name' => 'canvas'])
            ->factory('image_output_writer', static fn(): object => (object)['name' => 'output'])
            ->factory('image_source_file_storage', static fn(): object => (object)['name' => 'image-storage'])
            ->factory('array_value_reader', static fn(): callable => static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default)
            ->factory('class_name_resolver', static fn(): callable => static fn(object $object): string => get_class($object))
            ->factory('error', static fn(): object => (object)['name' => 'error'])
            ->factory('php_runtime_settings', static fn(): object => (object)['name' => 'php-runtime'])
            ->factory('soap_wsdl_file_storage', static fn(): object => (object)['name' => 'soap-storage']);

        return $container;
    }
}

final class ApplicationUtilityNamedStateDouble
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

final class ApplicationUtilitySoapDouble
{
    public array $initializedWith = [];

    public function initializeSoapObject(string $wsdlFile, ?array $param = null): void
    {
        $this->initializedWith = [$wsdlFile, $param];
    }
}

final class ApplicationUtilityConfigDouble
{
    public ApplicationUtilityDateConfigDouble $dateConfig;

    public function __construct()
    {
        $this->dateConfig = new ApplicationUtilityDateConfigDouble();
    }

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $key === 'date' ? $this->dateConfig : $default;
    }
}

final class ApplicationUtilityDateConfigDouble
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        if ($key === 'TIMEZONE') {
            return 'UTC';
        }
        if ($key === 'DEFAULT_FORMAT') {
            return ['mysql'];
        }
        if ($key === ['FORMAT', 'mysql']) {
            return new ApplicationUtilityDateFormatConfigDouble();
        }

        return $default;
    }
}

final class ApplicationUtilityDateFormatConfigDouble
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return match ($key) {
            'full_pattern' => 'Y-m-d H:i:s',
            'short_pattern' => 'Y-m-d',
            default => $default,
        };
    }
}
