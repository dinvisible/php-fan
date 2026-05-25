<?php

declare(strict_types=1);

use fan\core\runtime\request_input_source_defaults_factory;
use fan\core\di\request_input_source_defaults_provider_factory;
use PHPUnit\Framework\TestCase;
use fan\core\service\request_input_source;


final class BootstrapRequestInputSourceDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesRequestInputSourceDefaultsProvider(): void
    {
        $environment = new stdClass();
        $provider = (new request_input_source_defaults_provider_factory())(static fn(): object => $environment);
        $sourceFactory = $provider->sourceFactory();

        $this->assertInstanceOf(request_input_source_defaults_factory::class, $provider);
        $this->assertIsCallable($sourceFactory);
        $this->assertInstanceOf(request_input_source::class, $sourceFactory());
    }

    public function testFactoryUsesInjectedDefaultSourceCompositionProviders(): void
    {
        $environment = (object)['name' => 'environment'];
        $globals = (object)['name' => 'globals'];
        $source = (object)['name' => 'source'];
        $calls = [
            'globalsFactory' => 0,
            'sourceFactory' => 0,
        ];
        $receivedEnvironmentFactory = null;
        $receivedGlobalsFactory = null;
        $provider = (new request_input_source_defaults_provider_factory(
            sourceFactoryProvider: static function (callable $globalsFactory) use (&$calls, &$receivedGlobalsFactory, $source): callable {
                ++$calls['sourceFactory'];
                $receivedGlobalsFactory = $globalsFactory;

                return static fn(): object => $source;
            },
            globalsFactoryProvider: static function (callable $environmentFactory) use (&$calls, &$receivedEnvironmentFactory, $globals): callable {
                ++$calls['globalsFactory'];
                $receivedEnvironmentFactory = $environmentFactory;

                return static fn(): object => $globals;
            }
        ))(static fn(): object => $environment);

        $sourceFactory = $provider->sourceFactory();

        $this->assertSame($source, $sourceFactory());
        $this->assertSame(1, $calls['globalsFactory']);
        $this->assertSame(1, $calls['sourceFactory']);
        $this->assertSame($environment, $receivedEnvironmentFactory());
        $this->assertSame($globals, $receivedGlobalsFactory());
    }

    public function testSourceOwnsRequestInputSourceDefaultAssemblyBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/request_input_source_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class request_input_source_defaults_provider_factory', $source);
        $this->assertStringContainsString('private \Closure $sourceFactoryProvider;', $source);
        $this->assertStringContainsString('private \Closure $globalsFactoryProvider;', $source);
        $this->assertStringContainsString('public function __construct(', $source);
        $this->assertStringContainsString('?callable $sourceFactoryProvider = null,', $source);
        $this->assertStringContainsString('?callable $globalsFactoryProvider = null', $source);
        $this->assertStringContainsString('$this->sourceFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->globalsFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('public function __invoke(callable $environmentFactory): request_input_source_defaults_factory', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/request_input_source_defaults_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/request_input_source_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/request_input_globals_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../service/request_input_source.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../adapter/request_input_globals.php';", $source);
        $this->assertStringContainsString('return new request_input_source_defaults_factory(', $source);
        $this->assertStringContainsString('$sourceFactoryProvider = $this->sourceFactoryProvider;', $source);
        $this->assertStringContainsString('$globalsFactoryProvider = $this->globalsFactoryProvider;', $source);
        $this->assertStringContainsString('static fn(): callable => $sourceFactoryProvider(', $source);
        $this->assertStringContainsString('$globalsFactoryProvider($environmentFactory)', $source);
        $this->assertStringContainsString('?? static fn(callable $globalsFactory): callable => new request_input_source_factory($globalsFactory)', $source);
        $this->assertStringContainsString('?? static fn(callable $environmentFactory): callable => new request_input_globals_factory($environmentFactory)', $source);
        $this->assertStringNotContainsString('private ?\Closure $sourceFactoryProvider', $source);
        $this->assertStringNotContainsString('private ?\Closure $globalsFactoryProvider', $source);
        $this->assertStringNotContainsString('$sourceFactoryProvider === null ? null : \Closure::fromCallable($sourceFactoryProvider)', $source);
        $this->assertStringNotContainsString('$globalsFactoryProvider === null ? null : \Closure::fromCallable($globalsFactoryProvider)', $source);
        $this->assertStringNotContainsString('private function sourceFactoryProvider(): callable', $source);
        $this->assertStringNotContainsString('private function globalsFactoryProvider(): callable', $source);
        $this->assertStringNotContainsString('new request_input_source_factory(' . "\n" . '                new request_input_globals_factory($environmentFactory)', $source);
        $this->assertStringNotContainsString('request_input_native_environment', $source);
    }
}
