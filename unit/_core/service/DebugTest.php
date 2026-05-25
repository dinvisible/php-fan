<?php

declare(strict_types=1);

use fan\core\service\debug;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\base\service;
use fan\core\block\base;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;


class ServiceDebugTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/debug.php';

    public function testSetExtFilesDoesNothingWhenDebugIsDisabled(): void
    {
        $debug = $this->debug(['ENABLED' => false]);
        $root = new ServiceDebugRootDouble();

        $debug->setExtFiles($root, true);

        $this->assertSame([], $root->css);
        $this->assertSame([], $root->js);
        $this->assertSame([], $root->embedJs);
    }

    public function testSetExtFilesDoesNotRegisterRemovedDebugTraceAssetsWhenEnabled(): void
    {
        $debug = $this->debug(['ENABLED' => true]);
        $root = new ServiceDebugRootDouble();

        $debug->setExtFiles($root, true);

        $this->assertSame([], $root->css);
        $this->assertSame([], $root->js);
        $this->assertSame([], $root->embedJs);
    }

    public function testBlockCodeIsStoredForSecondDebugRows(): void
    {
        $debug = $this->debug(['ENABLED' => true]);

        $debug->setBlockCode('main', '<b>html</b>');

        $this->assertSame(['main' => '<b>html</b>'], $debug->blockCode());
    }

    public function testSecondDebugCodeWrapsBlockInfoAndConfiguredAssets(): void
    {
        $html = $this->debug()->getSecondDebugCode('<li>row</li>', 'Debug title');

        $this->assertStringContainsString('<title>Debug title</title>', $html);
        $this->assertStringNotContainsString('debug_control.css', $html);
        $this->assertStringNotContainsString('/debug/control.css', $html);
        $this->assertStringNotContainsString('/js/debug.js', $html);
        $this->assertStringNotContainsString('js-wrapper.js', $html);
        $this->assertStringNotContainsString('/debug/wrapper.js', $html);
        $this->assertStringNotContainsString('debug_trace', $html);
        $this->assertStringNotContainsString('/debug/trace.js', $html);
        $this->assertStringNotContainsString('/debug/common.css', $html);
        $this->assertStringNotContainsString('/debug/mode2.css', $html);
        $this->assertStringContainsString('<ul class="debug2_list"><li>row</li></ul>', $html);
    }

    public function testReduceMetaArrayKeepsOnlyCommonAndOwnKeys(): void
    {
        $arrayAdducerCalls = [];
        $debug = $this->debug(arrayAdducer: static function (mixed $value) use (&$arrayAdducerCalls): array {
            $arrayAdducerCalls[] = $value;

            return is_array($value) ? $value : [$value];
        });
        $meta = [
            'common' => ['a' => 1],
            'own' => ['b' => 2],
            'parent' => ['c' => 3],
        ];

        $this->assertSame([
            'common' => ['a' => 1],
            'own' => ['b' => 2],
        ], $debug->exposeReduceMetaArray($meta));
        $this->assertSame([$meta], $arrayAdducerCalls);
    }

    public function testReduceMetaArrayRequiresInjectedArrayAdducer(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Array adducer is not configured for debug service.');

        $debug = new ServiceDebugProbe();
        $debug->exposeReduceMetaArray([
            'common' => ['a' => 1],
            'own' => ['b' => 2],
            'parent' => ['c' => 3],
        ]);
    }

    public function testShowMetaArrayRendersScalarsArraysAndObjects(): void
    {
        $html = $this->debug()->exposeShowMetaArray([
            'name' => 'fan',
            'enabled' => true,
            'nested' => ['count' => 2],
            'object' => new stdClass(),
        ], null, []);

        $this->assertStringContainsString('<span class="debug_array_key">name</span>', $html);
        $this->assertStringContainsString('<span class="debug_array_val">&quot;fan&quot;</span>', $html);
        $this->assertStringContainsString('<span class="debug_array_val">true</span>', $html);
        $this->assertStringContainsString('Instance of <b>stdClass</b> class', $html);
    }

    public function testWrapHtmlCodeUsesInjectedTabDefaultInitNumber(): void
    {
        $debug = $this->debug(['ENABLED' => true], new ServiceDebugTabDouble(77));

        $html = $debug->wrapHtmlCode('<p>body</p>', new ServiceDebugBlockDouble('main'));

        $this->assertStringContainsString('<b>77:</b> main', $html);
        $this->assertStringContainsString('<p>body</p>', $html);
    }

    public function testBlockDetailUsesInjectedReflectionClassFactory(): void
    {
        $factory = new ServiceDebugReflectionClassFactoryDouble();
        $debug = $this->debug(
            ['ENABLED' => true],
            reflectionClassFactory: $factory
        );
        $block = new ServiceDebugBlockDouble('main');

        $html = $debug->exposeBlockDetail($block);

        $this->assertSame([$block], $factory->calls);
        $this->assertStringContainsString('List of parents', $html);
        $this->assertStringContainsString('ServiceDebugBlockDouble', $html);
    }

    public function testBlockDetailRequiresReflectionClassFactoryCreateMethod(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Reflection class factory must expose create().');

        (new ServiceDebugProbe())->exposeBlockDetail(new ServiceDebugBlockDouble('main'));
    }

    public function testConstructorUsesInjectedBaseServiceDependencies(): void
    {
        $this->ensureBaseHelper();
        $runtime = new ServiceDebugRuntimeDouble();
        $config = new ServiceDebugConfigDouble([
            'ENABLED' => true,
            'DEBUG_IP' => '/^127\.0\.0\.1$/',
        ]);
        $configurator = new ServiceDebugConfiguratorDouble($config);
        $cacheFactoryCalls = [];
        $arrayAdducer = static fn(mixed $value): array => is_array($value) ? $value : [$value];
        $reflectionClassFactory = new ServiceDebugReflectionClassFactoryDouble();

        $debug = new ServiceDebugConstructorProbe(
            true,
            new ServiceDebugTabDouble(),
            new ServiceDebugInputDouble(['SERVER_ADDR' => '127.0.0.1']),
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            },
            new ServiceDebugMetaFileStorageDouble(),
            $arrayAdducer,
            $reflectionClassFactory
        );

        $this->assertSame([ServiceDebugConstructorProbe::class], $runtime->initializer->serviceParams);
        $this->assertSame([$debug], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [ServiceDebugConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertTrue($debug->isEnabled());
        $this->assertSame([], $cacheFactoryCalls);
    }

    private function ensureBaseHelper(): void
    {
        if (function_exists('fan\core\base\get_class_name')) {
            return;
        }

        eval('
            namespace fan\core\base;

            function get_class_name(string|object $object): ?string
            {
                if (is_object($object)) {
                    $object = get_class($object);
                }
                $parts = explode("\\\\", $object);

                return end($parts);
            }
        ');
    }

    private function debug(
        array $config = [],
        ?object $tab = null,
        ?object $input = null,
        ?callable $arrayAdducer = null,
        ?object $reflectionClassFactory = null
    ): ServiceDebugProbe
    {
        $debug = new ServiceDebugProbe();
        $property = new ReflectionProperty(service::class, 'config');
        $property->setValue($debug, new ServiceDebugConfigDouble($config));
        $property = new ReflectionProperty(debug::class, 'tab');
        $property->setValue($debug, $tab ?? new ServiceDebugTabDouble());
        $property = new ReflectionProperty(debug::class, 'input');
        $property->setValue($debug, $input ?? new ServiceDebugInputDouble());
        $property = new ReflectionProperty(debug::class, 'metaFileStorage');
        $property->setValue($debug, new ServiceDebugMetaFileStorageDouble());
        $property = new ReflectionProperty(debug::class, 'arrayAdducer');
        $property->setValue(
            $debug,
            \Closure::fromCallable($arrayAdducer ?? static fn(mixed $value): array => is_array($value) ? $value : [$value])
        );
        $property = new ReflectionProperty(debug::class, 'reflectionClassFactory');
        $property->setValue(
            $debug,
            $reflectionClassFactory ?? new ServiceDebugReflectionClassFactoryDouble()
        );

        return $debug;
    }
}

final class ServiceDebugProbe extends debug
{
    public function __construct()
    {
    }

    public function blockCode(): ?array
    {
        $property = new ReflectionProperty(debug::class, 'blockCode');
        return $property->getValue($this);
    }

    public function exposeReduceMetaArray(array $meta): array
    {
        return $this->_reduceMetaArray($meta);
    }

    public function exposeShowMetaArray(array $meta, mixed $resultMeta, array $keys): string
    {
        return $this->_showMetaArray($meta, $resultMeta, $keys);
    }

    public function exposeBlockDetail(base $block): string
    {
        return $this->_getBlockDetail($block);
    }
}

final class ServiceDebugConstructorProbe extends debug
{
    public function __construct(
        bool $allowIni,
        ?object $tab,
        ?object $input,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory,
        ?object $metaFileStorage,
        ?callable $arrayAdducer,
        ?object $reflectionClassFactory
    )
    {
        parent::__construct(
            $allowIni,
            $tab,
            $input,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $metaFileStorage,
            $arrayAdducer,
            $reflectionClassFactory
        );
    }
}

final class ServiceDebugReflectionClassFactoryDouble
{
    public array $calls = [];

    public function create(object|string $object): ReflectionClass
    {
        $this->calls[] = $object;

        return new ReflectionClass($object);
    }
}

final class ServiceDebugMetaFileStorageDouble
{
    public array $existsPaths = [];

    public function __construct(private bool $exists = false)
    {
    }

    public function exists(string $path): bool
    {
        $this->existsPaths[] = $path;

        return $this->exists;
    }
}

final class ServiceDebugRootDouble
{
    public array $css = [];
    public array $js = [];
    public array $embedJs = [];

    public function setExternalCss(string $file): void
    {
        $this->css[] = $file;
    }

    public function setExternalJs(string $file): void
    {
        $this->js[] = $file;
    }

    public function setEmbedJs(string $code, ?string $position = null, ?int $priority = null): void
    {
        $this->embedJs[] = [$code, $position, $priority];
    }
}

final class ServiceDebugTabDouble
{
    public function __construct(private int $defaultInitNum = 100)
    {
    }

    public function getDefaultInitNum(): int
    {
        return $this->defaultInitNum;
    }
}

final class ServiceDebugInputDouble
{
    public function __construct(private array $server = [])
    {
    }

    public function serverValue(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }
}

final class ServiceDebugBlockDouble extends base
{
    public function __construct(private string $name)
    {
    }

    public function getBlockName(): string
    {
        return $this->name;
    }

    public function getMeta(string|array|null $key = null, mixed $default = null, bool $convToArray = false): mixed
    {
        return $default;
    }

    public function getDebugInfo(): array
    {
        return [
            'metaFile' => null,
            'templateFile' => null,
            'meta' => [],
            'containerMeta' => [],
            'fileMeta' => [],
            'parentMeta' => [],
            'folderMeta' => [],
        ];
    }
}

final class ServiceDebugConfigDouble extends ArrayObject
{
    public function __construct(private array $data)
    {
        parent::__construct($data);
    }

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this;
        }

        return array_key_exists($key, $this->getArrayCopy()) ? $this[$key] : $default;
    }
}

final class ServiceDebugRuntimeDouble
{
    public ServiceDebugInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new ServiceDebugInitializerDouble();
    }

    public function getInitializer(): ServiceDebugInitializerDouble
    {
        return $this->initializer;
    }

    private ?service_listener_state $baseServiceListenerState = null;

    private ?service_single_state $baseServiceSingleState = null;

    public function serviceListenerState(): service_listener_state
    {
        return $this->baseServiceListenerState ??= new service_listener_state();
    }

    public function serviceSingleState(): service_single_state
    {
        return $this->baseServiceSingleState ??= new service_single_state();
    }

    public function classNameResolver(): callable
    {
        return static fn(object $object): string => get_class($object);
    }
}

final class ServiceDebugInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServiceDebugConfiguratorDouble
{
    public array $getServiceConfigCalls = [];
    public array $resetCalls = [];

    public function __construct(private object $config)
    {
    }

    public function getServiceConfig(object $service): object
    {
        $this->getServiceConfigCalls[] = $service;

        return $this->config;
    }

    public function reset(string $className, string $key): void
    {
        $this->resetCalls[] = [$className, $key];
    }
}
