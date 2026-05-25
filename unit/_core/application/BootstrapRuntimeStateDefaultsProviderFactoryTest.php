<?php

declare(strict_types=1);

use fan\core\di\bootstrap_runtime_state_defaults_factory;
use fan\core\di\bootstrap_runtime_state_defaults_provider_factory;
use PHPUnit\Framework\TestCase;
use fan\core\base\meta\maker_state;
use fan\core\base\model\spec_file\image\row_state;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;
use fan\core\view\router\loader_state;


final class BootstrapRuntimeStateDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesRuntimeStateDefaultsProvider(): void
    {
        $provider = (new bootstrap_runtime_state_defaults_provider_factory())();
        $runtimeStateFactory = $provider->runtimeStateFactory();
        $state = $runtimeStateFactory();

        $this->assertInstanceOf(bootstrap_runtime_state_defaults_factory::class, $provider);
        $this->assertIsCallable($runtimeStateFactory);
        $this->assertInstanceOf(service_listener_state::class, $state['serviceListenerState']);
        $this->assertInstanceOf(service_single_state::class, $state['serviceSingleState']);
        $this->assertInstanceOf(loader_state::class, $state['viewLoaderState']);
        $this->assertInstanceOf(maker_state::class, $state['metaMakerState']);
        $this->assertInstanceOf(row_state::class, $state['specFileImageRowState']);
    }

    public function testFactoryUsesInjectedRuntimeStateFactories(): void
    {
        $expected = [
            'serviceListenerState' => (object)['name' => 'listener'],
            'serviceSingleState' => (object)['name' => 'single'],
            'viewLoaderState' => (object)['name' => 'view-loader'],
            'metaMakerState' => (object)['name' => 'meta-maker'],
            'specFileImageRowState' => (object)['name' => 'spec-file-image-row'],
        ];
        $provider = (new bootstrap_runtime_state_defaults_provider_factory(
            serviceListenerStateFactory: static fn(): object => $expected['serviceListenerState'],
            serviceSingleStateFactory: static fn(): object => $expected['serviceSingleState'],
            viewLoaderStateFactory: static fn(): object => $expected['viewLoaderState'],
            metaMakerStateFactory: static fn(): object => $expected['metaMakerState'],
            specFileImageRowStateFactory: static fn(): object => $expected['specFileImageRowState']
        ))();

        $runtimeStateFactory = $provider->runtimeStateFactory();

        $this->assertInstanceOf(bootstrap_runtime_state_defaults_factory::class, $provider);
        $this->assertSame($expected, $runtimeStateFactory());
    }

    public function testFactoryUsesInjectedDefaultRuntimeStateProviders(): void
    {
        $calls = [
            'serviceListenerState' => 0,
            'serviceSingleState' => 0,
            'metaMakerState' => 0,
            'specFileImageRowState' => 0,
        ];
        $provider = (new bootstrap_runtime_state_defaults_provider_factory(
            viewLoaderStateFactory: static fn(): object => (object)['name' => 'view-loader'],
            serviceListenerStateProvider: static function () use (&$calls): object {
                ++$calls['serviceListenerState'];

                return (object)['name' => 'listener-provider'];
            },
            serviceSingleStateProvider: static function () use (&$calls): object {
                ++$calls['serviceSingleState'];

                return (object)['name' => 'single-provider'];
            },
            metaMakerStateProvider: static function () use (&$calls): object {
                ++$calls['metaMakerState'];

                return (object)['name' => 'meta-maker-provider'];
            },
            specFileImageRowStateProvider: static function () use (&$calls): object {
                ++$calls['specFileImageRowState'];

                return (object)['name' => 'spec-file-image-row-provider'];
            }
        ))();

        $runtimeStateFactory = $provider->runtimeStateFactory();
        $state = $runtimeStateFactory();

        $this->assertSame(1, $calls['serviceListenerState']);
        $this->assertSame(1, $calls['serviceSingleState']);
        $this->assertSame(1, $calls['metaMakerState']);
        $this->assertSame(1, $calls['specFileImageRowState']);
        $this->assertSame('listener-provider', $state['serviceListenerState']->name);
        $this->assertSame('single-provider', $state['serviceSingleState']->name);
        $this->assertSame('meta-maker-provider', $state['metaMakerState']->name);
        $this->assertSame('spec-file-image-row-provider', $state['specFileImageRowState']->name);
    }

    public function testFactoryUsesInjectedViewLoaderStateCompositionFactories(): void
    {
        $jsonKeeperFactory = static fn(): object => (object)['name' => 'json'];
        $textKeeperFactory = static fn(): object => (object)['name' => 'text'];
        $viewLoaderState = (object)['name' => 'view-loader'];
        $receivedJsonKeeperFactory = null;
        $receivedTextKeeperFactory = null;
        $provider = (new bootstrap_runtime_state_defaults_provider_factory(
            viewLoaderStateFactoryFactory: static function (
                callable $receivedJson,
                callable $receivedText
            ) use (
                $viewLoaderState,
                &$receivedJsonKeeperFactory,
                &$receivedTextKeeperFactory
            ): callable {
                $receivedJsonKeeperFactory = $receivedJson;
                $receivedTextKeeperFactory = $receivedText;

                return static fn(): object => $viewLoaderState;
            },
            viewLoaderJsonKeeperFactory: static fn(): callable => $jsonKeeperFactory,
            viewLoaderTextKeeperFactory: static fn(): callable => $textKeeperFactory
        ))();

        $runtimeStateFactory = $provider->runtimeStateFactory();
        $state = $runtimeStateFactory();

        $this->assertSame($viewLoaderState, $state['viewLoaderState']);
        $this->assertSame('json', $receivedJsonKeeperFactory()->name);
        $this->assertSame('text', $receivedTextKeeperFactory()->name);
    }

    public function testFactoryUsesInjectedDefaultViewLoaderProviders(): void
    {
        $stateProviderCalls = 0;
        $jsonProviderCalls = 0;
        $textProviderCalls = 0;
        $receivedJsonKeeperFactory = null;
        $receivedTextKeeperFactory = null;
        $provider = (new bootstrap_runtime_state_defaults_provider_factory(
            viewLoaderStateFactoryProvider: static function (
                callable $jsonKeeperFactory,
                callable $textKeeperFactory
            ) use (
                &$stateProviderCalls,
                &$receivedJsonKeeperFactory,
                &$receivedTextKeeperFactory
            ): callable {
                ++$stateProviderCalls;
                $receivedJsonKeeperFactory = $jsonKeeperFactory;
                $receivedTextKeeperFactory = $textKeeperFactory;

                return static fn(): object => (object)[
                    'name' => 'view-loader',
                    'json' => $jsonKeeperFactory()->name,
                    'text' => $textKeeperFactory()->name,
                ];
            },
            viewLoaderJsonKeeperFactoryProvider: static function () use (&$jsonProviderCalls): callable {
                ++$jsonProviderCalls;

                return static fn(): object => (object)['name' => 'json-provider'];
            },
            viewLoaderTextKeeperFactoryProvider: static function () use (&$textProviderCalls): callable {
                ++$textProviderCalls;

                return static fn(): object => (object)['name' => 'text-provider'];
            }
        ))();

        $runtimeStateFactory = $provider->runtimeStateFactory();
        $state = $runtimeStateFactory();

        $this->assertSame(1, $stateProviderCalls);
        $this->assertSame(1, $jsonProviderCalls);
        $this->assertSame(1, $textProviderCalls);
        $this->assertIsCallable($receivedJsonKeeperFactory);
        $this->assertIsCallable($receivedTextKeeperFactory);
        $this->assertSame('view-loader', $state['viewLoaderState']->name);
        $this->assertSame('json-provider', $state['viewLoaderState']->json);
        $this->assertSame('text-provider', $state['viewLoaderState']->text);
    }

    public function testSourceOwnsBootstrapRuntimeStateDefaultsBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/bootstrap_runtime_state_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class bootstrap_runtime_state_defaults_provider_factory', $source);
        $this->assertStringContainsString('public function __invoke(): bootstrap_runtime_state_defaults_factory', $source);
        $this->assertStringContainsString('private \Closure $serviceListenerStateFactory;', $source);
        $this->assertStringContainsString('private \Closure $serviceSingleStateFactory;', $source);
        $this->assertStringContainsString('private \Closure $viewLoaderStateFactory;', $source);
        $this->assertStringContainsString('private \Closure $metaMakerStateFactory;', $source);
        $this->assertStringContainsString('private \Closure $specFileImageRowStateFactory;', $source);
        $this->assertStringContainsString('private \Closure $viewLoaderStateFactoryFactory;', $source);
        $this->assertStringContainsString('private \Closure $viewLoaderJsonKeeperFactory;', $source);
        $this->assertStringContainsString('private \Closure $viewLoaderTextKeeperFactory;', $source);
        $this->assertStringContainsString('private \Closure $viewLoaderStateFactoryProvider;', $source);
        $this->assertStringContainsString('private \Closure $viewLoaderJsonKeeperFactoryProvider;', $source);
        $this->assertStringContainsString('private \Closure $viewLoaderTextKeeperFactoryProvider;', $source);
        $this->assertStringContainsString('private \Closure $serviceListenerStateProvider;', $source);
        $this->assertStringContainsString('private \Closure $serviceSingleStateProvider;', $source);
        $this->assertStringContainsString('private \Closure $metaMakerStateProvider;', $source);
        $this->assertStringContainsString('private \Closure $specFileImageRowStateProvider;', $source);
        $this->assertStringContainsString('public function __construct(', $source);
        $this->assertStringContainsString('?callable $viewLoaderStateFactoryProvider = null,', $source);
        $this->assertStringContainsString('?callable $viewLoaderJsonKeeperFactoryProvider = null,', $source);
        $this->assertStringContainsString('?callable $viewLoaderTextKeeperFactoryProvider = null', $source);
        $this->assertStringContainsString('?callable $serviceListenerStateProvider = null,', $source);
        $this->assertStringContainsString('?callable $serviceSingleStateProvider = null,', $source);
        $this->assertStringContainsString('?callable $metaMakerStateProvider = null,', $source);
        $this->assertStringContainsString('?callable $specFileImageRowStateProvider = null', $source);
        $this->assertStringContainsString('$this->viewLoaderStateFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->viewLoaderJsonKeeperFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->viewLoaderTextKeeperFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->serviceListenerStateProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->serviceSingleStateProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->metaMakerStateProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->specFileImageRowStateProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->viewLoaderStateFactoryFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->viewLoaderJsonKeeperFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->viewLoaderTextKeeperFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->serviceListenerStateFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->serviceSingleStateFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->viewLoaderStateFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->metaMakerStateFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->specFileImageRowStateFactory = \Closure::fromCallable(', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_runtime_state_defaults_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../service/service_listener_state.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../service/service_single_state.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../view/router/loader_state.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../factory/view_loader_json_keeper_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../factory/view_loader_text_keeper_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../factory/view_loader_state_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../base/meta/maker_state.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../base/model/spec_file/image/row_state.php';", $source);
        $this->assertStringContainsString('return new bootstrap_runtime_state_defaults_factory(', $source);
        $this->assertStringContainsString('$serviceListenerStateFactory = $this->serviceListenerStateFactory;', $source);
        $this->assertStringContainsString('$serviceSingleStateFactory = $this->serviceSingleStateFactory;', $source);
        $this->assertStringContainsString('$viewLoaderStateFactory = $this->viewLoaderStateFactory;', $source);
        $this->assertStringContainsString('$metaMakerStateFactory = $this->metaMakerStateFactory;', $source);
        $this->assertStringContainsString('$specFileImageRowStateFactory = $this->specFileImageRowStateFactory;', $source);
        $this->assertStringContainsString('$jsonKeeperFactory = ($this->viewLoaderJsonKeeperFactory)();', $source);
        $this->assertStringContainsString('$textKeeperFactory = ($this->viewLoaderTextKeeperFactory)();', $source);
        $this->assertStringContainsString('$viewLoaderStateFactory = ($this->viewLoaderStateFactoryFactory)(', $source);
        $this->assertStringContainsString('?? fn(callable $jsonKeeperFactory, callable $textKeeperFactory): callable => ($this->viewLoaderStateFactoryProvider)($jsonKeeperFactory, $textKeeperFactory)', $source);
        $this->assertStringContainsString('?? fn(): callable => ($this->viewLoaderJsonKeeperFactoryProvider)()', $source);
        $this->assertStringContainsString('?? fn(): callable => ($this->viewLoaderTextKeeperFactoryProvider)()', $source);
        $this->assertStringContainsString('?? fn(): object => ($this->serviceListenerStateProvider)()', $source);
        $this->assertStringContainsString('?? fn(): object => ($this->serviceSingleStateProvider)()', $source);
        $this->assertStringContainsString('?? fn(): object => ($this->metaMakerStateProvider)()', $source);
        $this->assertStringContainsString('?? fn(): object => ($this->specFileImageRowStateProvider)()', $source);
        $this->assertStringContainsString('?? static fn(callable $jsonKeeperFactory, callable $textKeeperFactory): callable => new view_loader_state_factory($jsonKeeperFactory, $textKeeperFactory)', $source);
        $this->assertStringContainsString('?? static fn(): callable => new view_loader_json_keeper_factory()', $source);
        $this->assertStringContainsString('?? static fn(): callable => new view_loader_text_keeper_factory()', $source);
        $this->assertStringContainsString('?? static fn(): object => new service_listener_state()', $source);
        $this->assertStringContainsString('?? static fn(): object => new service_single_state()', $source);
        $this->assertStringContainsString('?? static fn(): object => new maker_state()', $source);
        $this->assertStringContainsString('?? static fn(): object => new row_state()', $source);
        $this->assertStringNotContainsString('private ?\Closure', $source);
        $this->assertStringNotContainsString('=== null ? null : \Closure::fromCallable', $source);
        $this->assertStringNotContainsString('private function serviceListenerStateFactory(): \Closure', $source);
        $this->assertStringNotContainsString('private function serviceSingleStateFactory(): \Closure', $source);
        $this->assertStringNotContainsString('private function viewLoaderStateFactory(): \Closure', $source);
        $this->assertStringNotContainsString('private function metaMakerStateFactory(): \Closure', $source);
        $this->assertStringNotContainsString('private function specFileImageRowStateFactory(): \Closure', $source);
        $this->assertStringNotContainsString('private function viewLoaderStateFactoryFactory(): \Closure', $source);
        $this->assertStringNotContainsString('private function viewLoaderJsonKeeperFactory(): \Closure', $source);
        $this->assertStringNotContainsString('private function viewLoaderTextKeeperFactory(): \Closure', $source);
        $this->assertStringNotContainsString('private function viewLoaderStateFactoryProvider(): \Closure', $source);
        $this->assertStringNotContainsString('private function viewLoaderJsonKeeperFactoryProvider(): \Closure', $source);
        $this->assertStringNotContainsString('private function viewLoaderTextKeeperFactoryProvider(): \Closure', $source);
        $this->assertStringNotContainsString('private function serviceListenerStateProvider(): \Closure', $source);
        $this->assertStringNotContainsString('private function serviceSingleStateProvider(): \Closure', $source);
        $this->assertStringNotContainsString('private function metaMakerStateProvider(): \Closure', $source);
        $this->assertStringNotContainsString('private function specFileImageRowStateProvider(): \Closure', $source);
        $this->assertStringContainsString("'serviceListenerState' => \$serviceListenerStateFactory()", $source);
        $this->assertStringContainsString("'serviceSingleState' => \$serviceSingleStateFactory()", $source);
        $this->assertStringContainsString("'viewLoaderState' => \$viewLoaderStateFactory()", $source);
        $this->assertStringContainsString("'metaMakerState' => \$metaMakerStateFactory()", $source);
        $this->assertStringContainsString("'specFileImageRowState' => \$specFileImageRowStateFactory()", $source);
    }
}
