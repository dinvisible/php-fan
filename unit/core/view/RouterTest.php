<?php

declare(strict_types=1);

use fan\core\view\keeper;
use fan\core\view\router;
use FanTest\core\SourceFileContractTestCase;
use fan\core\block\base;


class ViewRouterTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/view/router.php';

    public function testRouterConstructorStoresBlockAndCountsConfiguredKeepers(): void
    {
        $block = new ViewRouterBlockDouble();
        $router = new ViewRouterProbe($block);

        $this->assertSame($block, $router->getBlock());
        $this->assertCount(1, $router);
        $this->assertTrue(isset($router['data']));
        $this->assertFalse(isset($router['missing']));
    }

    public function testGetReturnsConfiguredKeeperOrDelegatesToDefaultKeeper(): void
    {
        $router = new ViewRouterProbe(new ViewRouterBlockDouble());

        $this->assertInstanceOf(ViewRouterKeeperDouble::class, $router->get('data'));
        $this->assertSame('fallback', $router->get('missing', 'fallback'));
    }

    public function testSetIsAllowedWhenCalledByOwningBlock(): void
    {
        $block = new ViewRouterBlockDouble();
        $router = new ViewRouterProbe($block);

        $this->assertSame($router, $block->writeToRouter($router, 'title', 'Hello'));

        $this->assertSame('Hello', $router->get('title'));
        $this->assertSame(['title' => 'Hello'], $router->toArray());
    }

    public function testSetSeveralDelegatesEveryPairThroughSetterRules(): void
    {
        $block = new ViewRouterBlockDouble();
        $router = new ViewRouterProbe($block);

        $block->writeSeveralToRouter($router, ['first' => 1, 'second' => 2]);

        $this->assertSame(['first' => 1, 'second' => 2], $router->getAll());
    }

    public function testGetAllUsesInjectedArrayAdducerForMultipleKeepers(): void
    {
        $calls = [];
        $router = new ViewRouterMultiKeeperProbe(
            new ViewRouterBlockDouble(),
            null,
            null,
            static function (mixed $value) use (&$calls): array {
                $calls[] = $value;

                return ['converted' => $value === null ? 'empty' : 'value'];
            }
        );

        $this->assertSame([
            'first' => ['converted' => 'empty'],
            'second' => ['converted' => 'empty'],
        ], $router->getAll());
        $this->assertSame([null, null], $calls);
    }

    public function testGetUsesInjectedKeeperFactoryForDefaultKeeper(): void
    {
        $calls = [];
        $router = new ViewRouterDefaultKeeperProbe(
            new ViewRouterBlockDouble(),
            static function (router $router) use (&$calls): keeper {
                $calls[] = $router;

                return new ViewRouterKeeperDouble();
            }
        );

        $this->assertSame('fallback', $router->get('missing', 'fallback'));
        $this->assertSame([$router], $calls);
    }

    public function testMissingKeeperFactoryFailsAtUseTime(): void
    {
        $router = new ViewRouterDefaultKeeperProbe(new ViewRouterBlockDouble());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('View keeper factory is not configured for view router.');

        $router->get('missing', 'fallback');
    }

    public function testMissingArrayAdducerFailsAtUseTime(): void
    {
        $router = new ViewRouterMultiKeeperProbe(new ViewRouterBlockDouble());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Array adducer is not configured for view router.');

        $router->getAll();
    }

    public function testUnknownKeeperUsesInjectedBlockExceptionFactory(): void
    {
        $calls = [];
        $block = new ViewRouterBlockDouble();
        $router = new ViewRouterProbe(
            $block,
            null,
            static function (
                string $exceptionClass,
                base $block,
                string $message,
                int $code,
                ?\Exception $previous = null
            ) use (&$calls): \Throwable {
                $calls[] = [$exceptionClass, $block, $message, $code, $previous];

                return new \RuntimeException($message, $code, $previous);
            }
        );

        try {
            $router->_getKeeper('missing');
            $this->fail('Expected injected block exception factory to provide the thrown exception.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Incorrect name of Keeper "missing"', $exception->getMessage());
        }

        $this->assertCount(1, $calls);
        $this->assertSame('\fan\project\exception\block\fatal', $calls[0][0]);
        $this->assertSame($block, $calls[0][1]);
        $this->assertSame('Incorrect name of Keeper "missing"', $calls[0][2]);
        $this->assertSame(E_USER_ERROR, $calls[0][3]);
        $this->assertNull($calls[0][4]);
    }

    public function testMissingBlockExceptionFactoryFailsAtUseTime(): void
    {
        $router = new ViewRouterProbe(new ViewRouterBlockDouble());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Block exception factory is not configured for view router.');

        $router->_getKeeper('missing');
    }

    public function testSourceDoesNotCreateBlockFatalExceptionDirectly(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/view/router.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private ?\Closure $keeperFactory = null;', $source);
        $this->assertStringContainsString('private ?\Closure $blockExceptionFactory = null;', $source);
        $this->assertStringContainsString('private ?\Closure $arrayAdducer = null;', $source);
        $this->assertStringContainsString('$this->keeperFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->blockExceptionFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->arrayAdducer = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('private function keeperFactory(): callable', $source);
        $this->assertStringContainsString('private function blockExceptionFactory(): callable', $source);
        $this->assertStringContainsString('if (!isset($this->keeperFactory)) {', $source);
        $this->assertStringContainsString('if (!isset($this->blockExceptionFactory)) {', $source);
        $this->assertStringContainsString('if (!isset($this->arrayAdducer)) {', $source);
        $this->assertStringContainsString('private function createBlockFatalException(string $message, int $code = E_USER_ERROR, ?\Exception $previous = null): \Throwable', $source);
        $this->assertStringContainsString('$this->blockExceptionFactory', $source);
        $this->assertStringNotContainsString('new \fan\project\exception\block\fatal', $source);
        $this->assertStringNotContainsString('adduceToArray(', $source);
        $this->assertStringNotContainsString('private $keeperFactory;', $source);
        $this->assertStringNotContainsString('private $blockExceptionFactory;', $source);
        $this->assertStringNotContainsString('private $arrayAdducer;', $source);
        $this->assertStringNotContainsString('$this->keeperFactory = $keeperFactory === null ? null : \Closure::fromCallable($keeperFactory);', $source);
        $this->assertStringNotContainsString('$this->blockExceptionFactory = $blockExceptionFactory === null ? null : \Closure::fromCallable($blockExceptionFactory);', $source);
        $this->assertStringNotContainsString('$this->arrayAdducer = $arrayAdducer === null ? null : \Closure::fromCallable($arrayAdducer);', $source);
    }
}

final class ViewRouterProbe extends router
{
    protected array $keepers = [
        'data' => null,
    ];

    protected ?string $defaultKey = 'data';

    protected function _getDataKeeper(): keeper
    {
        return new ViewRouterKeeperDouble();
    }
}

final class ViewRouterMultiKeeperProbe extends router
{
    protected array $keepers = [
        'first' => null,
        'second' => null,
    ];

    protected ?string $defaultKey = 'first';
}

final class ViewRouterDefaultKeeperProbe extends router
{
    protected array $keepers = [
        'data' => null,
    ];

    protected ?string $defaultKey = 'data';
}

final class ViewRouterKeeperDouble extends keeper
{
    private array $values = [];

    public function __construct()
    {
    }

    public function set(mixed $key, mixed $value, bool $rewriteExisting = true, ?bool $convArray = null): static
    {
        $this->values[(string)$key] = $value;

        return $this;
    }

    public function get(mixed $key = null, mixed $default = null, bool $logError = true): mixed
    {
        return $key === null ? $this->values : ($this->values[(string)$key] ?? $default);
    }

    public function toArray(): array
    {
        return $this->values;
    }
}

final class ViewRouterBlockDouble extends base
{
    public function __construct()
    {
    }

    public function writeToRouter(router $router, string $key, mixed $value): router
    {
        return $router->set($key, $value);
    }

    public function writeSeveralToRouter(router $router, array $values): router
    {
        return $router->setSeveral($values);
    }
}
