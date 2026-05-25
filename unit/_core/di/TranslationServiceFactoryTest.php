<?php

declare(strict_types=1);

use fan\core\di\translation_service_factory;
use fan\core\service\translation;
use PHPUnit\Framework\TestCase;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;

final class TranslationServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreTranslationServiceWithoutDynamicOverrideFactory(): void
    {
        $this->ensureBaseFunctionAliases();

        $locale = new TranslationServiceFactoryLocaleDouble(['en' => 'English', 'fr' => 'French']);
        $runtime = new TranslationServiceFactoryRuntimeDouble();
        $tabFactory = static fn(): object => new stdClass();
        $messageTagFactories = ['tag' => static fn(): object => new stdClass()];
        $errorLogger = new stdClass();
        $blockContext = new stdClass();
        $matcher = new stdClass();
        $requestInput = new stdClass();
        $configurator = new TranslationServiceFactoryConfiguratorDouble(new TranslationServiceFactoryConfigDouble([]));
        $cacheFactoryCalls = [];
        $cacheFactory = static function (string $type) use (&$cacheFactoryCalls): object {
            $cacheFactoryCalls[] = $type;

            return (object)['type' => $type];
        };
        $loadedPhpArrayFiles = [];
        $phpArrayLoader = static function (string $path, mixed $default = null) use (&$loadedPhpArrayFiles): mixed {
            $loadedPhpArrayFiles[] = [$path, $default];

            return $default;
        };
        $fileStorage = new stdClass();
        $overrideCalls = [];

        $translation = (new translation_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            }
        ))(
            translation::class,
            true,
            $locale,
            $runtime,
            $tabFactory,
            $messageTagFactories,
            $errorLogger,
            $blockContext,
            $matcher,
            $requestInput,
            $runtime,
            $configurator,
            $cacheFactory,
            $phpArrayLoader,
            $fileStorage
        );

        $this->assertInstanceOf(translation::class, $translation);
        $this->assertSame([], $overrideCalls);
        $this->assertSame([translation::class], $runtime->initializer->serviceParams);
        $this->assertSame([$translation], $configurator->getServiceConfigCalls);
        $this->assertContains($configurator->resetCalls[0][0] ?? null, ['translation', translation::class]);
        $this->assertSame('ENABLED', $configurator->resetCalls[0][1] ?? null);
        $this->assertSame([], $cacheFactoryCalls);
        $this->assertSame([], $loadedPhpArrayFiles);
    }

    public function testFactoryDelegatesConfiguredOverrideServiceCreation(): void
    {
        $locale = new stdClass();
        $runtime = new stdClass();
        $tabFactory = static fn(): object => new stdClass();
        $messageTagFactories = ['tag' => static fn(): object => new stdClass()];
        $errorLogger = new stdClass();
        $blockContext = new stdClass();
        $matcher = new stdClass();
        $requestInput = new stdClass();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $phpArrayLoader = static fn(string $path, mixed $default = null): mixed => $default;
        $fileStorage = new stdClass();
        $configuredCalls = [];

        $translation = (new translation_service_factory(
            static function (string $className, array $arguments) use (&$configuredCalls): object {
                $configuredCalls[] = [$className, $arguments];

                return new $className(...$arguments);
            }
        ))(
            TranslationServiceFactoryProbe::class,
            true,
            $locale,
            $runtime,
            $tabFactory,
            $messageTagFactories,
            $errorLogger,
            $blockContext,
            $matcher,
            $requestInput,
            $runtime,
            $configurator,
            $cacheFactory,
            $phpArrayLoader,
            $fileStorage
        );

        $this->assertInstanceOf(TranslationServiceFactoryProbe::class, $translation);
        $this->assertTrue($translation->allowIni);
        $this->assertSame($locale, $translation->locale);
        $this->assertSame($runtime, $translation->runtime);
        $this->assertSame($tabFactory, $translation->tabFactory);
        $this->assertSame($messageTagFactories, $translation->messageTagFactories);
        $this->assertSame($errorLogger, $translation->errorLogger);
        $this->assertSame($blockContext, $translation->blockContext);
        $this->assertSame($matcher, $translation->matcher);
        $this->assertSame($requestInput, $translation->requestInput);
        $this->assertSame($runtime, $translation->serviceBootstrapRuntime);
        $this->assertSame($configurator, $translation->serviceConfigurator);
        $this->assertSame($cacheFactory, $translation->serviceCacheFactory);
        $this->assertSame($phpArrayLoader, $translation->phpArrayLoader);
        $this->assertSame($fileStorage, $translation->fileStorage);
        $this->assertSame(TranslationServiceFactoryProbe::class, $configuredCalls[0][0] ?? null);
        $this->assertSame([
            true,
            $locale,
            $runtime,
            $tabFactory,
            $messageTagFactories,
            $errorLogger,
            $blockContext,
            $matcher,
            $requestInput,
            $runtime,
            $configurator,
            $cacheFactory,
            $phpArrayLoader,
            $fileStorage,
        ], $configuredCalls[0][1] ?? null);
    }

                private function ensureBaseFunctionAliases(): void
    {
        if (!function_exists('get_class_name')) {
            eval('function get_class_name(string|object $object): ?string { if (is_object($object)) { $object = get_class($object); } $parts = explode(chr(92), $object); return end($parts); }');
        }
        if (!function_exists('fan\core\base\get_class_name')) {
            eval('namespace fan\core\base { function get_class_name(string|object $object): ?string { return \get_class_name($object); } }');
        }
    }
}

final class TranslationServiceFactoryProbe
{
    public function __construct(
        public bool $allowIni,
        public object $locale,
        public object $runtime,
        public $tabFactory,
        public array $messageTagFactories,
        public object $errorLogger,
        public object $blockContext,
        public object $matcher,
        public object $requestInput,
        public object $serviceBootstrapRuntime,
        public object $serviceConfigurator,
        public $serviceCacheFactory,
        public $phpArrayLoader,
        public object $fileStorage
    ) {
    }
}


final class TranslationServiceFactoryLocaleDouble
{
    public function __construct(private array $languages)
    {
    }

    public function getAvailableLanguages(): array
    {
        return $this->languages;
    }

    public function getLanguage(): string
    {
        return (string)array_key_first($this->languages);
    }

    public function getDefaultLanguage(): string
    {
        return (string)array_key_first($this->languages);
    }

    public function isEnabled(): bool
    {
        return true;
    }
}

final class TranslationServiceFactoryRuntimeDouble
{
    public TranslationServiceFactoryInitializerDouble $initializer;
    public array $parsedPaths = [];

    public function __construct()
    {
        $this->initializer = new TranslationServiceFactoryInitializerDouble();
    }

    public function getInitializer(): TranslationServiceFactoryInitializerDouble
    {
        return $this->initializer;
    }

    public function parsePath(string $path): string
    {
        $this->parsedPaths[] = $path;

        return $path;
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
        return static fn(object|string $object): string => get_class_name($object) ?? (is_object($object) ? get_class($object) : $object);
    }
}

final class TranslationServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class TranslationServiceFactoryConfiguratorDouble
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

    public function reset(string $className, mixed $key): void
    {
        $this->resetCalls[] = [$className, $key];
    }
}

final class TranslationServiceFactoryConfigDouble extends ArrayObject
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this : ($this[$key] ?? $default);
    }
}
