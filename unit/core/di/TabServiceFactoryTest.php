<?php

declare(strict_types=1);

use fan\core\di\tab_service_factory;
use fan\core\service\tab;
use fan\core\service\tab_state;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use fan\core\service\service_single_state;


if (!function_exists('get_class_name')) {
    function get_class_name(string|object $object): ?string
    {
        if (is_object($object)) {
            $object = get_class($object);
        }

        $parts = explode('\\', $object);

        return end($parts);
    }
}

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class TabServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreTabServiceWithTypedConstructor(): void
    {
        $overrideCalls = [];
        $factory = new tab_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            },
            static fn(array $config, object $request, object $tab): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass()
        );
        $matcher = new stdClass();
        $request = new stdClass();
        $locale = new stdClass();
        $sessionFactory = static fn(string $namespace, string $group): object => (object)[
            'namespace' => $namespace,
            'group' => $group,
        ];
        $input = new stdClass();
        $runtime = new TabServiceFactoryRuntimeDouble();
        $roleFactory = static fn(): object => new stdClass();
        $transferFactory = static fn(): object => new stdClass();
        $configFactory = static fn(): object => new stdClass();
        $headerFactory = static fn(): object => new stdClass();
        $applicationFactory = static fn(): object => new stdClass();
        $debugFactory = static fn(): object => new stdClass();
        $jsonFactory = static fn(): object => new stdClass();
        $dataLoaderFactory = static fn(): object => new stdClass();
        $templateFactory = static fn(): object => new stdClass();
        $errorFactory = static fn(): object => new stdClass();
        $logFactory = static fn(): object => new stdClass();
        $cookieFactory = static fn(): object => new stdClass();
        $reflectorFactory = static fn(): object => new stdClass();
        $entityFactory = static fn(): object => new stdClass();
        $formFactory = static fn(): object => new stdClass();
        $pagerFactory = static fn(): object => new stdClass();
        $obfuscatorFactory = static fn(): object => new stdClass();
        $imageModifyFactory = static fn(): object => new stdClass();
        $databaseFactory = static fn(): object => new stdClass();
        $userFactory = static fn(): object => new stdClass();
        $dateFactory = static fn(): object => new stdClass();
        $state = new tab_state();
        $configurator = new TabServiceFactoryConfiguratorDouble(new TabServiceFactoryConfigDouble());
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $phpArrayFileLoader = static fn(string $path, mixed $default = null): mixed => $default;
        $delegateFactory = static fn(): object => new stdClass();
        $viewParserFactory = static fn(): object => new stdClass();
        $blockFactory = static fn(): object => new stdClass();
        $blockExceptionFactory = static fn(): object => new stdClass();
        $metaRowFactory = static fn(): object => new stdClass();
        $aliasFileStorage = new stdClass();
        $imageMetadataReader = new stdClass();
        $errorLogWriter = new stdClass();
        $blockFileStorage = new stdClass();
        $metaFileStorage = new stdClass();
        $projectToolFileStorage = new stdClass();
        $rootHtmlFileStorage = new stdClass();
        $tab = $factory(
            tab::class,
            true,
            $matcher,
            $request,
            $locale,
            $sessionFactory,
            $input,
            $runtime,
            $roleFactory,
            $transferFactory,
            $configFactory,
            $headerFactory,
            $applicationFactory,
            $debugFactory,
            $jsonFactory,
            $dataLoaderFactory,
            $templateFactory,
            $errorFactory,
            $logFactory,
            $cookieFactory,
            $reflectorFactory,
            $entityFactory,
            $formFactory,
            $pagerFactory,
            $obfuscatorFactory,
            $imageModifyFactory,
            $databaseFactory,
            $userFactory,
            $dateFactory,
            $state,
            $runtime,
            $configurator,
            $cacheFactory,
            $phpArrayFileLoader,
            $delegateFactory,
            $viewParserFactory,
            $blockFactory,
            $blockExceptionFactory,
            $metaRowFactory,
            $aliasFileStorage,
            imageMetadataReader: $imageMetadataReader,
            errorLogWriter: $errorLogWriter,
            blockFileStorage: $blockFileStorage,
            metaFileStorage: $metaFileStorage,
            projectToolFileStorage: $projectToolFileStorage,
            rootHtmlFileStorage: $rootHtmlFileStorage
        );

        $this->assertInstanceOf(tab::class, $tab);
        $this->assertSame([], $overrideCalls);
        $this->assertSame([tab::class], $runtime->initializer->serviceParams);
        $this->assertSame([$tab], $configurator->getServiceConfigCalls);
        $this->assertSame([[tab::class, 'ENABLED']], $configurator->resetCalls);
        $this->assertSame($matcher, $this->propertyValue($tab, 'matcher'));
        $this->assertSame($request, $this->propertyValue($tab, 'request'));
        $this->assertSame($locale, $this->propertyValue($tab, 'locale'));
        $this->assertInstanceOf(Closure::class, $this->propertyValue($tab, 'sessionFactory'));
        $this->assertSame($input, $this->propertyValue($tab, 'input'));
        $this->assertSame($runtime, $this->propertyValue($tab, 'runtime'));
        $this->assertInstanceOf(Closure::class, $this->propertyValue($tab, 'roleFactory'));
        $this->assertInstanceOf(Closure::class, $this->propertyValue($tab, 'transferFactory'));
        $this->assertInstanceOf(Closure::class, $this->propertyValue($tab, 'configFactory'));
        $this->assertSame($state, $this->propertyValue($tab, 'tabState'));
        $this->assertInstanceOf(Closure::class, $this->propertyValue($tab, 'phpArrayFileLoader'));
        $this->assertInstanceOf(Closure::class, $this->propertyValue($tab, 'delegateFactory'));
        $this->assertInstanceOf(Closure::class, $this->propertyValue($tab, 'viewParserFactory'));
        $this->assertInstanceOf(Closure::class, $this->propertyValue($tab, 'blockFactory'));
        $this->assertInstanceOf(Closure::class, $this->propertyValue($tab, 'blockExceptionFactory'));
        $this->assertInstanceOf(Closure::class, $this->propertyValue($tab, 'metaRowFactory'));
        $this->assertSame($aliasFileStorage, $this->propertyValue($tab, 'aliasFileStorage'));
        $this->assertInstanceOf(Closure::class, $this->propertyValue($tab, 'viewDefinerFactory'));
        $this->assertInstanceOf(Closure::class, $this->propertyValue($tab, 'metaMakerFactory'));
        $this->assertInstanceOf(Closure::class, $this->propertyValue($tab, 'viewRouterFactory'));
        $this->assertInstanceOf(Closure::class, $this->propertyValue($tab, 'viewLoaderStateFactory'));
        $this->assertInstanceOf(Closure::class, $this->propertyValue($tab, 'metaMakerStateFactory'));
        $this->assertInstanceOf(Closure::class, $this->propertyValue($tab, 'arrayAdducer'));
        $this->assertInstanceOf(Closure::class, $this->propertyValue($tab, 'recursiveMerger'));
        $this->assertInstanceOf(Closure::class, $this->propertyValue($tab, 'arrayValueReader'));
        $this->assertInstanceOf(Closure::class, $this->propertyValue($tab, 'arrayLikeChecker'));
        $this->assertInstanceOf(Closure::class, $this->propertyValue($tab, 'shortClassNameResolver'));
        $this->assertSame($imageMetadataReader, $this->propertyValue($tab, 'imageMetadataReader'));
        $this->assertSame($errorLogWriter, $this->propertyValue($tab, 'errorLogWriter'));
        $this->assertSame($blockFileStorage, $this->propertyValue($tab, 'blockFileStorage'));
        $this->assertSame($metaFileStorage, $this->propertyValue($tab, 'metaFileStorage'));
        $this->assertSame($projectToolFileStorage, $this->propertyValue($tab, 'projectToolFileStorage'));
        $this->assertSame($rootHtmlFileStorage, $this->propertyValue($tab, 'rootHtmlFileStorage'));
    }

    public function testFactoryDelegatesConfiguredTabServiceOverrides(): void
    {
        $calls = [];
        $factory = new tab_service_factory(
            static function (string $className, array $arguments) use (&$calls): object {
                $calls[] = [$className, $arguments];

                return new TabServiceFactoryProbe(...$arguments);
            },
            static fn(array $config, object $request, object $tab): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass()
        );
        $dependencies = [
            true,
            new stdClass(),
            new stdClass(),
            new stdClass(),
            static fn(): object => new stdClass(),
            new stdClass(),
            new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            new stdClass(),
            new stdClass(),
            new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): array => [],
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            new stdClass(),
        ];

        $tab = $factory(TabServiceFactoryProbe::class, ...$dependencies);

        $this->assertInstanceOf(TabServiceFactoryProbe::class, $tab);
        $this->assertSame(TabServiceFactoryProbe::class, $calls[0][0]);
        $this->assertSame($dependencies, array_slice($calls[0][1], 0, count($dependencies)));
        $this->assertInstanceOf(Closure::class, $calls[0][1][count($dependencies)]);
        $this->assertInstanceOf(Closure::class, $calls[0][1][count($dependencies) + 1]);
        $this->assertInstanceOf(Closure::class, $calls[0][1][count($dependencies) + 2]);
        $this->assertInstanceOf(Closure::class, $calls[0][1][count($dependencies) + 3]);
        $this->assertInstanceOf(Closure::class, $calls[0][1][count($dependencies) + 4]);
        $this->assertInstanceOf(Closure::class, $calls[0][1][count($dependencies) + 5]);
        $this->assertInstanceOf(Closure::class, $calls[0][1][count($dependencies) + 6]);
        $this->assertInstanceOf(Closure::class, $calls[0][1][count($dependencies) + 7]);
        $this->assertInstanceOf(Closure::class, $calls[0][1][count($dependencies) + 8]);
        $this->assertInstanceOf(Closure::class, $calls[0][1][count($dependencies) + 9]);
        $this->assertInstanceOf(Closure::class, $calls[0][1][count($dependencies) + 10]);
        $this->assertSame($calls[0][1], $tab->dependencies);
    }

                private function propertyValue(object $object, string $propertyName): mixed
    {
        $property = new ReflectionProperty(tab::class, $propertyName);

        return $property->getValue($object);
    }

    private static function factory(callable $configuredServiceFactory): tab_service_factory
    {
        return new tab_service_factory(
            $configuredServiceFactory,
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass(),
            static fn(): object => new stdClass()
        );
    }

    private static function invokeFactoryWithMinimalArguments(tab_service_factory $factory): void
    {
        $dependencies = array_merge(
            [
                true,
                new stdClass(),
                new stdClass(),
                new stdClass(),
            ],
            array_fill(0, 24, static fn(): object => new stdClass()),
            [
                new stdClass(),
                new stdClass(),
                new stdClass(),
            ],
            array_fill(0, 7, static fn(): object => new stdClass()),
            [
                new stdClass(),
            ]
        );

        $factory(tab::class, ...$dependencies);
    }
}

final class TabServiceFactoryProbe
{
    public array $dependencies;

    public function __construct(mixed ...$dependencies)
    {
        $this->dependencies = $dependencies;
    }
}


final class TabServiceFactoryRuntimeDouble
{
    public TabServiceFactoryInitializerDouble $initializer;
    private service_single_state $singleState;

    public function __construct()
    {
        $this->initializer = new TabServiceFactoryInitializerDouble();
        $this->singleState = new service_single_state();
    }

    public function getInitializer(): TabServiceFactoryInitializerDouble
    {
        return $this->initializer;
    }

    public function serviceSingleState(): service_single_state
    {
        return $this->singleState;
    }
}

final class TabServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class TabServiceFactoryConfiguratorDouble
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

final class TabServiceFactoryConfigDouble
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $default;
    }
}
