<?php

declare(strict_types=1);

use fan\core\bootstrap\application;
use fan\core\bootstrap\context;
use fan\core\bootstrap\context_registry_state;
use fan\core\bootstrap\state;
use PHPUnit\Framework\TestCase;
use fan\core\di\context_defaults_factory;
use fan\core\bootstrap\loader;
use fan\core\di\container;


final class BootstrapApplicationTest extends TestCase
{
    public function testApplicationReadsInjectedContextState(): void
    {
        $state = new state();
        $state->setConfig(['config_cache' => ['application' => 'injected']]);
        $context = new context($state, defaultFactoriesFactory: self::defaultFactoriesFactory());
        $application = new application(new context_registry_state($context));

        $this->assertSame($context, $application->context());
        $this->assertSame(['application' => 'injected'], $application->context()->state()->config()['config_cache']);
    }

    public function testApplicationCreatesContextStateLazilyThroughInjectedFactory(): void
    {
        $state = new state();
        $state->setConfig(['config_cache' => ['factory' => 'injected']]);
        $context = new context($state, defaultFactoriesFactory: self::defaultFactoriesFactory());
        $calls = 0;
        $application = new application(
            defaultContextStateFactory: static function () use (&$calls, $context): context_registry_state {
                $calls++;

                return new context_registry_state($context);
            }
        );

        $this->assertSame($context, $application->context());
        $this->assertSame($context, $application->context());
        $this->assertSame(1, $calls);
    }

    public function testApplicationCanReplaceContextAfterConstruction(): void
    {
        $first = new context(new state(), defaultFactoriesFactory: self::defaultFactoriesFactory());
        $secondState = new state();
        $secondState->setConfig(['config_cache' => ['context' => 'replaced']]);
        $second = new context($secondState, defaultFactoriesFactory: self::defaultFactoriesFactory());
        $application = new application(new context_registry_state($first));

        $application->setContext($second);

        $this->assertSame($second, $application->context());
    }

    public function testApplicationCanReplaceContextFactory(): void
    {
        $state = new state();
        $state->setConfig(['config_cache' => ['factory' => 'replaced']]);
        $context = new context($state, defaultFactoriesFactory: self::defaultFactoriesFactory());
        $calls = 0;
        $application = new application(new context_registry_state(new context(new state(), defaultFactoriesFactory: self::defaultFactoriesFactory())));

        $application->setContextFactory(function () use (&$calls, $context): context {
            $calls++;

            return $context;
        });

        $this->assertSame($context, $application->context());
        $this->assertSame($context, $application->context());
        $this->assertSame(1, $calls);
    }

    public function testApplicationInjectsFatalExceptionFactoryIntoLoaderCallback(): void
    {
        $state = new state();
        $container = (new container())
            ->set('header_writer', (object)['name' => 'header-writer'])
            ->set('array_value_reader', static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default);
        $requestInput = (object)['name' => 'request-input'];
        $capturedBootstrapArguments = null;
        $capturedFatalArguments = null;
        $context = new context(
            $state,
            containerFactory: static fn(): container => $container,
            requestInputFactory: static fn(): object => $requestInput,
            zendAutoloaderLoaderFactory: static fn(): object => new class {
                public function load(): void
                {
                }
            },
            bootstrapLoaderFileStorageFactory: static fn(): object => (object)['name' => 'file-storage'],
            bootstrapObjectFactory: static function (string $class, array $arguments) use (&$capturedBootstrapArguments): object {
                $capturedBootstrapArguments = [$class, $arguments];

                return (object)['class' => $class, 'arguments' => $arguments];
            },
            defaultFactoriesFactory: self::defaultFactoriesFactory()
        );
        $application = new application(
            new context_registry_state($context),
            fatalExceptionFactory: static function (string $message, mixed ...$arguments) use (&$capturedFatalArguments): \Throwable {
                $capturedFatalArguments = [$message, $arguments];

                return new RuntimeException('injected fatal');
            }
        );

        $defineObj = new ReflectionMethod(application::class, 'defineObj');
        $loader = $defineObj->invoke(
            $application,
            'loader',
            loader::class,
            dirname(__DIR__, 3) . '/core/application/loader.php'
        );
        $fatalCallback = $loader->arguments[4];
        $exception = $fatalCallback('loader failed');

        $this->assertSame(loader::class, $capturedBootstrapArguments[0]);
        $this->assertIsCallable($fatalCallback);
        $this->assertSame('reader-value', ($loader->arguments[5])(['key' => 'reader-value'], 'key'));
        $this->assertSame('injected fatal', $exception->getMessage());
        $this->assertSame('loader failed', $capturedFatalArguments[0]);
        $this->assertSame($requestInput, $capturedFatalArguments[1]['requestInput']);
        $this->assertSame($container->get('header_writer'), $capturedFatalArguments[1]['exceptionHeaderWriter']);
        $this->assertIsObject($capturedFatalArguments[1]['exceptionRuntimeLogger']);
    }

    public function testApplicationUsesInjectedDefaultFatalExceptionFactoryProvider(): void
    {
        $state = new state();
        $container = (new container())
            ->set('header_writer', (object)['name' => 'header-writer'])
            ->set('array_value_reader', static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default);
        $providerCalls = 0;
        $capturedFatalArguments = null;
        $context = new context(
            $state,
            containerFactory: static fn(): container => $container,
            requestInputFactory: static fn(): object => (object)['name' => 'request-input'],
            zendAutoloaderLoaderFactory: static fn(): object => new class {
                public function load(): void
                {
                }
            },
            bootstrapLoaderFileStorageFactory: static fn(): object => (object)['name' => 'file-storage'],
            bootstrapObjectFactory: static fn(string $class, array $arguments): object => (object)['class' => $class, 'arguments' => $arguments],
            defaultFactoriesFactory: self::defaultFactoriesFactory()
        );
        $application = new application(
            new context_registry_state($context),
            fatalExceptionFactoryProvider: static function () use (&$providerCalls, &$capturedFatalArguments): callable {
                ++$providerCalls;

                return static function (string $message, mixed ...$arguments) use (&$capturedFatalArguments): \Throwable {
                    $capturedFatalArguments = [$message, $arguments];

                    return new RuntimeException('provider fatal');
                };
            }
        );

        $defineObj = new ReflectionMethod(application::class, 'defineObj');
        $loader = $defineObj->invoke(
            $application,
            'loader',
            loader::class,
            dirname(__DIR__, 3) . '/core/application/loader.php'
        );
        $fatalCallback = $loader->arguments[4];
        $exception = $fatalCallback('loader failed by provider');

        $this->assertSame(1, $providerCalls);
        $this->assertSame('reader-value', ($loader->arguments[5])(['key' => 'reader-value'], 'key'));
        $this->assertSame('provider fatal', $exception->getMessage());
        $this->assertSame('loader failed by provider', $capturedFatalArguments[0]);
        $this->assertSame($container->get('header_writer'), $capturedFatalArguments[1]['exceptionHeaderWriter']);
    }

    public function testInitDelegatesPhpVersionFailureToRuntimeSettings(): void
    {
        $state = new state();
        $runtimeSettings = new BootstrapApplicationRuntimeSettingsDouble('7.4.33', 'cli');
        $context = new context(
            $state,
            phpRuntimeSettingsFactory: static fn(): object => $runtimeSettings,
            defaultFactoriesFactory: self::defaultFactoriesFactory()
        );
        $application = new application(new context_registry_state($context));

        $this->assertFalse($application->init(null, static function (): void {
        }));
        $this->assertSame([
            'PHP-FAN can\'t work with version less than "8.3.0". Actually your version is "7.4.33".',
        ], $runtimeSettings->terminatedMessages);
        $this->assertFalse($state->isInit());
    }

    public function testApplicationSourceOwnsContextStateInsteadOfStaticBootstrapFacade(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/application/application.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class application', $source);
        $this->assertStringContainsString('private ?context_registry_state $contextState;', $source);
        $this->assertStringContainsString('private \Closure $defaultContextStateFactory;', $source);
        $this->assertStringContainsString('private \Closure $fatalExceptionFactory;', $source);
        $this->assertStringContainsString('private \Closure $fatalExceptionFactoryProvider;', $source);
        $this->assertStringContainsString('?callable $fatalExceptionFactory = null', $source);
        $this->assertStringContainsString('?callable $fatalExceptionFactoryProvider = null', $source);
        $this->assertStringContainsString('$this->fatalExceptionFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('?? fn(mixed ...$arguments): mixed => ($this->fatalExceptionFactoryProvider)()(...$arguments)', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertStringContainsString('$contextDefaultsProvider = new context_defaults_factory();', $source);
        $this->assertStringContainsString('new context_registry_state(null, new context_factory($contextDefaultsProvider))', $source);
        $this->assertStringContainsString('$fatalExceptionFactory = $this->fatalExceptionFactory;', $source);
        $this->assertStringContainsString('return $fatalExceptionFactory(', $source);
        $this->assertStringContainsString('$phpRuntimeSettings = $this->phpRuntimeSettings();', $source);
        $this->assertStringContainsString('$phpVersion = $phpRuntimeSettings->version();', $source);
        $this->assertStringContainsString('$phpRuntimeSettings->terminate(', $source);
        $this->assertStringContainsString('strtolower($phpRuntimeSettings->sapiName()) === \'cli\'', $source);
        $this->assertStringNotContainsString('runCli', $source);
        $this->assertStringNotContainsString('This script can be run in CLI mode only', $source);
        $this->assertStringNotContainsString('php_sapi_name()', $source);
        $this->assertStringNotContainsString('die(', $source);
        $this->assertStringContainsString("\$arguments[] = \$this->container()->get('array_value_reader');", $source);
        $this->assertStringContainsString('?? static function (): callable {', $source);
        $this->assertStringContainsString('return new fatal_exception_factory();', $source);
        $this->assertStringNotContainsString('private ?\Closure $fatalExceptionFactoryProvider', $source);
        $this->assertStringNotContainsString('$fatalExceptionFactoryProvider === null ? null : \Closure::fromCallable($fatalExceptionFactoryProvider)', $source);
        $this->assertStringNotContainsString('private function defaultFatalExceptionFactory(): callable', $source);
        $this->assertStringNotContainsString('private function fatalExceptionFactoryProvider(): callable', $source);
        $this->assertStringNotContainsString('$fatalExceptionFactory = new \fan\core\di\fatal_exception_factory();', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/context_defaults_factory.php';", $source);
        $this->assertStringNotContainsString('new context_registry_state(null, new context_factorynew context_defaults_factory())', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/context_registry_state_default_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/context_registry_state_factory.php';", $source);
        $this->assertStringNotContainsString('new context_registry_state_default_factory()', $source);
        $this->assertStringNotContainsString('new context_registry_state_factory(', $source);
        $this->assertFileDoesNotExist(dirname(__DIR__, 3) . '/core/application/context_registry_state_default_factory.php');
        $this->assertFileDoesNotExist(dirname(__DIR__, 3) . '/core/application/context_registry_state_factory.php');
        $this->assertFileDoesNotExist(dirname(__DIR__, 3) . '/core/bootstrap.php');
    }

    private static function defaultFactoriesFactory(): callable
    {
        return new context_defaults_factory();
    }
}

final class BootstrapApplicationRuntimeSettingsDouble
{
    /**
     * @var list<string>
     */
    public array $terminatedMessages = [];

    public function __construct(private string $version, private string $sapiName)
    {
    }

    public function version(): string
    {
        return $this->version;
    }

    public function sapiName(): string
    {
        return $this->sapiName;
    }

    public function terminate(string $message): string
    {
        $this->terminatedMessages[] = $message;

        return 'terminated';
    }
}
