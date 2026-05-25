<?php

declare(strict_types=1);

use fan\core\service\bootstrap_runtime;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\base\meta\maker_state;
use fan\core\base\model\spec_file\image\row_state;
use fan\core\bootstrap\initializer;
use fan\core\bootstrap\loader;
use fan\core\bootstrap\runner;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;
use fan\core\view\router\loader_state;


final class ServiceBootstrapRuntimeTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/bootstrap_runtime.php';

    public function testRuntimeExposesBootstrapOperationsAsInstanceMethods(): void
    {
        $runtime = new bootstrap_runtime();

        foreach ([
            'getLoader',
            'getRunner',
            'getInitializer',
            'parsePath',
            'loadClass',
            'logError',
            'handleError',
            'getGlobalPath',
            'getConfigCache',
            'getPid',
            'isCli',
            'serviceListenerState',
            'serviceSingleState',
            'serviceEngineFactory',
            'serviceExceptionFactory',
            'classNameResolver',
            'arrayValueReader',
            'viewLoaderState',
            'metaMakerState',
            'specFileImageRowState',
        ] as $method) {
            $this->assertTrue(method_exists($runtime, $method), $method . ' should be available.');
        }
    }

    public function testRuntimeKeepsOneListenerStateInstance(): void
    {
        $state = new service_listener_state();
        $runtime = new bootstrap_runtime($state);

        $this->assertSame($state, $runtime->serviceListenerState());
        $this->assertSame($state, $runtime->serviceListenerState());
    }

    public function testRuntimeKeepsOneSingleServiceStateInstance(): void
    {
        $state = new service_single_state();
        $runtime = new bootstrap_runtime(null, $state);

        $this->assertSame($state, $runtime->serviceSingleState());
        $this->assertSame($state, $runtime->serviceSingleState());
    }

    public function testRuntimeKeepsOneViewLoaderStateInstance(): void
    {
        $state = new loader_state();
        $runtime = new bootstrap_runtime(null, null, $state);

        $this->assertSame($state, $runtime->viewLoaderState());
        $this->assertSame($state, $runtime->viewLoaderState());
    }

    public function testRuntimeKeepsOneMetaMakerStateInstance(): void
    {
        $state = new maker_state();
        $runtime = new bootstrap_runtime(null, null, null, $state);

        $this->assertSame($state, $runtime->metaMakerState());
        $this->assertSame($state, $runtime->metaMakerState());
    }

    public function testRuntimeKeepsOneSpecFileImageRowStateInstance(): void
    {
        $state = new row_state();
        $runtime = new bootstrap_runtime(null, null, null, null, $state);

        $this->assertSame($state, $runtime->specFileImageRowState());
        $this->assertSame($state, $runtime->specFileImageRowState());
    }

    public function testRuntimeRequiresStateDependenciesToBeInjected(): void
    {
        foreach ([
            'serviceListenerState' => 'Bootstrap runtime service listener state is not configured.',
            'serviceSingleState' => 'Bootstrap runtime service single state is not configured.',
            'viewLoaderState' => 'Bootstrap runtime view loader state is not configured.',
            'metaMakerState' => 'Bootstrap runtime meta maker state is not configured.',
            'specFileImageRowState' => 'Bootstrap runtime spec-file image row state is not configured.',
        ] as $method => $message) {
            $runtime = new bootstrap_runtime();

            try {
                $runtime->{$method}();
                $this->fail($method . ' should require an injected state dependency.');
            } catch (\RuntimeException $exception) {
                $this->assertSame($message, $exception->getMessage());
            }
        }
    }

    public function testRuntimeExposesInjectedServiceEngineFactory(): void
    {
        $factory = static fn(string $class): object => (object)['class' => $class];
        $runtime = new bootstrap_runtime(serviceEngineFactory: $factory);

        $this->assertSame($factory, $runtime->serviceEngineFactory());
        $this->assertEquals((object)['class' => 'ExampleEngine'], ($runtime->serviceEngineFactory())('ExampleEngine'));
    }

    public function testRuntimeExposesInjectedServiceExceptionFactory(): void
    {
        $factory = static fn(): \Throwable => new RuntimeException('service exception');
        $runtime = new bootstrap_runtime(serviceExceptionFactory: $factory);

        $this->assertSame($factory, $runtime->serviceExceptionFactory());
        $this->assertSame('service exception', ($runtime->serviceExceptionFactory())()->getMessage());
    }

    public function testRuntimeExposesInjectedBaseServiceSupportHelpers(): void
    {
        $classNameResolver = static fn(object $object): string => 'resolved-' . basename(str_replace('\\', '/', get_class($object)));
        $arrayValueReader = static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
        $runtime = new bootstrap_runtime(classNameResolver: $classNameResolver, arrayValueReader: $arrayValueReader);

        $this->assertSame($classNameResolver, $runtime->classNameResolver());
        $this->assertSame('resolved-ServiceBootstrapRuntimeTest', ($runtime->classNameResolver())($this));
        $this->assertSame($arrayValueReader, $runtime->arrayValueReader());
        $this->assertSame('fallback', ($runtime->arrayValueReader())([], 'missing', 'fallback'));
    }

    public function testRuntimeUsesInjectedBootstrapOperations(): void
    {
        $loader = $this->createStub(loader::class);
        $runner = $this->createStub(runner::class);
        $initializer = $this->createStub(initializer::class);
        $calls = [];
        $runtime = new bootstrap_runtime(
            bootstrapOperations: [
                'getLoader' => static fn(): object => $loader,
                'getRunner' => static fn(): object => $runner,
                'getInitializer' => static fn(): object => $initializer,
                'parsePath' => static function (string $path) use (&$calls): string {
                    $calls[] = ['parsePath', $path];
                    return '/resolved/' . trim($path, '{}');
                },
                'loadClass' => static function (string $class, bool $makeAlias = true) use (&$calls): bool {
                    $calls[] = ['loadClass', $class, $makeAlias];
                    return $makeAlias;
                },
                'logError' => static function (string $message) use (&$calls): void {
                    $calls[] = ['logError', $message];
                },
                'handleError' => static function (
                    int|float $errNo,
                    string $errMsg,
                    ?string $fileName = null,
                    int|float|null $lineNum = null,
                    mixed $errContext = null
                ) use (&$calls): bool {
                    $calls[] = ['handleError', $errNo, $errMsg, $fileName, $lineNum, $errContext];
                    return true;
                },
                'getGlobalPath' => static function (string $key, mixed $altPath = null) use (&$calls): ?string {
                    $calls[] = ['getGlobalPath', $key, $altPath];
                    return '/global/' . $key;
                },
                'getConfigCache' => static fn(): array => ['cache' => 'injected'],
                'getPid' => static fn(): string => 'pid-123',
                'isCli' => static fn(): bool => true,
            ]
        );

        $this->assertSame($loader, $runtime->getLoader());
        $this->assertSame($runner, $runtime->getRunner());
        $this->assertSame($initializer, $runtime->getInitializer());
        $this->assertSame('/resolved/TEMP', $runtime->parsePath('{TEMP}'));
        $this->assertTrue($runtime->loadClass('ExampleClass'));
        $runtime->logError('boom');
        $this->assertTrue($runtime->handleError(123, 'message', 'file.php', 45, ['ctx' => true]));
        $this->assertSame('/global/log', $runtime->getGlobalPath('log', '/alt'));
        $this->assertSame(['cache' => 'injected'], $runtime->getConfigCache());
        $this->assertSame('pid-123', $runtime->getPid());
        $this->assertTrue($runtime->isCli());
        $this->assertSame([
            ['parsePath', '{TEMP}'],
            ['loadClass', 'ExampleClass', true],
            ['logError', 'boom'],
            ['handleError', 123, 'message', 'file.php', 45, ['ctx' => true]],
            ['getGlobalPath', 'log', '/alt'],
        ], $calls);
    }

    public function testRuntimeRequiresUnknownBootstrapOperationsToBeInjected(): void
    {
        $runtime = new bootstrap_runtime();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown bootstrap runtime operation "parsePath".');

        $runtime->parsePath('{TEMP}');
    }

    public function testRuntimeSourceCallsBootstrapThroughOperationBoundary(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('private array $bootstrapOperations;', $source);
        $this->assertStringContainsString('private function bootstrapOperation(string $name): callable', $source);
        $this->assertStringContainsString('return ($this->bootstrapOperation(\'parsePath\'))($path);', $source);
        $this->assertStringContainsString('Bootstrap runtime service listener state is not configured.', $source);
        $this->assertStringContainsString('Class name resolver is not configured.', $source);
        $this->assertStringContainsString('Array value reader is not configured.', $source);
        $this->assertStringNotContainsString('new service_listener_state()', $source);
        $this->assertStringNotContainsString('new service_single_state()', $source);
        $this->assertStringNotContainsString('new \fan\core\view\router\loader_state()', $source);
        $this->assertStringNotContainsString('new \fan\core\base\meta\maker_state()', $source);
        $this->assertStringNotContainsString('new \fan\core\base\model\spec_file\image\row_state()', $source);
        $this->assertStringNotContainsString('\bootstrap::', $source);
    }
}
