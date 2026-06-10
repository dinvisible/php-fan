<?php

declare(strict_types=1);

use fan\core\runtime\request_input_defaults_factory;
use fan\core\di\request_input_defaults_provider_factory;
use PHPUnit\Framework\TestCase;
use fan\core\service\request_input;


final class RequestInputDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesRequestInputDefaultsProvider(): void
    {
        $environment = new stdClass();
        $provider = (new request_input_defaults_provider_factory())(static fn(): object => $environment);
        $requestInputFactory = $provider();

        $this->assertInstanceOf(request_input_defaults_factory::class, $provider);
        $this->assertIsCallable($requestInputFactory);
        $this->assertInstanceOf(request_input::class, $requestInputFactory());
    }

    public function testFactoryUsesInjectedDefaultCompositionProviders(): void
    {
        $environment = (object)['name' => 'environment-provider'];
        $source = (object)['name' => 'source-provider'];
        $requestInput = (object)['name' => 'request-input-provider'];
        $calls = [
            'environment' => 0,
            'sourceDefaults' => 0,
            'requestInputFactory' => 0,
        ];
        $receivedEnvironmentFactory = null;
        $receivedSourceFactory = null;
        $provider = (new request_input_defaults_provider_factory(
            environmentFactoryProvider: static function () use (&$calls, $environment): object {
                ++$calls['environment'];

                return $environment;
            },
            sourceDefaultsProviderFactory: static function (callable $environmentFactory) use (&$calls, &$receivedEnvironmentFactory, $source): object {
                ++$calls['sourceDefaults'];
                $receivedEnvironmentFactory = $environmentFactory;

                return new class($source) {
                    public function __construct(private object $source)
                    {
                    }

                    public function sourceFactory(): callable
                    {
                        return fn(): object => $this->source;
                    }
                };
            },
            requestInputFactoryFactoryProvider: static function (callable $sourceFactory) use (&$calls, &$receivedSourceFactory, $requestInput): callable {
                ++$calls['requestInputFactory'];
                $receivedSourceFactory = $sourceFactory;

                return static fn(): object => $requestInput;
            }
        ))();

        $requestInputFactory = $provider();

        $this->assertSame($requestInput, $requestInputFactory());
        $this->assertSame(1, $calls['sourceDefaults']);
        $this->assertSame(1, $calls['requestInputFactory']);
        $this->assertSame($environment, $receivedEnvironmentFactory());
        $this->assertSame(1, $calls['environment']);
        $this->assertSame($source, $receivedSourceFactory());
    }

    public function testSourceOwnsRequestInputDefaultCompositionBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/request_input_defaults_provider_factory.php');
        $defaultsSource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/runtime/request_input_defaults_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($defaultsSource);
        $this->assertStringContainsString('final class request_input_defaults_provider_factory', $source);
        $this->assertStringContainsString('private \Closure $environmentFactoryProvider;', $source);
        $this->assertStringContainsString('private \Closure $sourceDefaultsProviderFactory;', $source);
        $this->assertStringContainsString('private \Closure $requestInputFactoryFactoryProvider;', $source);
        $this->assertStringContainsString('public function __construct(', $source);
        $this->assertStringContainsString('?callable $environmentFactoryProvider = null,', $source);
        $this->assertStringContainsString('?callable $sourceDefaultsProviderFactory = null,', $source);
        $this->assertStringContainsString('?callable $requestInputFactoryFactoryProvider = null', $source);
        $this->assertStringContainsString('$this->environmentFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->sourceDefaultsProviderFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->requestInputFactoryFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('public function __invoke(?callable $environmentFactory = null): request_input_defaults_factory', $source);
        $this->assertStringContainsString('return new request_input_defaults_factory(', $source);
        $this->assertStringContainsString('$environmentFactory ??= $this->environmentFactoryProvider;', $source);
        $this->assertStringContainsString('$sourceDefaultsProviderFactory = $this->sourceDefaultsProviderFactory;', $source);
        $this->assertStringContainsString('$sourceDefaults = $sourceDefaultsProviderFactory($environmentFactory);', $source);
        $this->assertStringContainsString('$requestInputFactoryFactory = $this->requestInputFactoryFactoryProvider;', $source);
        $this->assertStringContainsString('?? static fn(): object => new request_input_native_environment()', $source);
        $this->assertStringContainsString('?? static fn(callable $environmentFactory): request_input_source_defaults_factory => (new request_input_source_defaults_provider_factory())($environmentFactory)', $source);
        $this->assertStringContainsString('?? static fn(callable $sourceFactory): request_input_factory => new request_input_factory($sourceFactory)', $source);
        $this->assertStringNotContainsString('private ?\Closure $environmentFactoryProvider', $source);
        $this->assertStringNotContainsString('private ?\Closure $sourceDefaultsProviderFactory', $source);
        $this->assertStringNotContainsString('private ?\Closure $requestInputFactoryFactoryProvider', $source);
        $this->assertStringNotContainsString('$environmentFactoryProvider === null ? null : \Closure::fromCallable($environmentFactoryProvider)', $source);
        $this->assertStringNotContainsString('$sourceDefaultsProviderFactory === null ? null : \Closure::fromCallable($sourceDefaultsProviderFactory)', $source);
        $this->assertStringNotContainsString('$requestInputFactoryFactoryProvider === null ? null : \Closure::fromCallable($requestInputFactoryFactoryProvider)', $source);
        $this->assertStringNotContainsString('private function environmentFactoryProvider(): callable', $source);
        $this->assertStringNotContainsString('private function sourceDefaultsProviderFactory(): callable', $source);
        $this->assertStringNotContainsString('private function requestInputFactoryFactoryProvider(): callable', $source);
        $this->assertStringNotContainsString("request_input_defaults_factory::class => 'request_input_defaults_factory.php'", $source);
        $this->assertStringNotContainsString("request_input_factory::class => 'request_input_factory.php'", $source);
        $this->assertStringNotContainsString("request_input_source_defaults_provider_factory::class => 'request_input_source_defaults_provider_factory.php'", $source);
        $this->assertStringNotContainsString("request_input::class => '../service/request_input.php'", $source);
        $this->assertStringNotContainsString("request_input_native_environment::class => '../adapter/request_input_native_environment.php'", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/' . \$fileName;", $source);
        $this->assertStringNotContainsString('$environmentFactory ??= static fn(): object => new \fan\core\adapter\request_input_native_environment();', $source);
        $this->assertStringNotContainsString('$sourceDefaults = (new request_input_source_defaults_provider_factory())($environmentFactory);', $source);
        $this->assertStringNotContainsString('new request_input_factory(', $defaultsSource);
        $this->assertStringNotContainsString('request_input_source_defaults_provider_factory', $defaultsSource);
        $this->assertStringNotContainsString('request_input_native_environment', $defaultsSource);
        $this->assertStringNotContainsString('require_once', $defaultsSource);
    }
}
