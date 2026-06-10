<?php

declare(strict_types=1);

use fan\core\view\router\loader;
use fan\core\view\router\loader_state;
use FanTest\core\SourceFileContractTestCase;
use fan\core\view\keeper\loader\json;
use fan\core\view\keeper\loader\text;


final class ViewRouterLoaderStateTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/view/router/loader_state.php';

    public function testStateCreatesAndReusesJsonKeeper(): void
    {
        $createdFor = [];
        $state = new loader_state(
            static function (loader $router) use (&$createdFor): ViewRouterLoaderStateJsonKeeperDouble {
                $createdFor[] = $router;

                return new ViewRouterLoaderStateJsonKeeperDouble();
            }
        );
        $firstRouter = new ViewRouterLoaderStateRouterDouble();
        $secondRouter = new ViewRouterLoaderStateRouterDouble();

        $keeper = $state->jsonKeeper($firstRouter);

        $this->assertSame($keeper, $state->jsonKeeper($secondRouter));
        $this->assertSame([$firstRouter], $createdFor);
        $this->assertSame([$secondRouter], $keeper->routers);
    }

    public function testStateCreatesAndReusesTextKeeper(): void
    {
        $createdFor = [];
        $state = new loader_state(
            null,
            static function (loader $router) use (&$createdFor): ViewRouterLoaderStateTextKeeperDouble {
                $createdFor[] = $router;

                return new ViewRouterLoaderStateTextKeeperDouble();
            }
        );
        $firstRouter = new ViewRouterLoaderStateRouterDouble();
        $secondRouter = new ViewRouterLoaderStateRouterDouble();

        $keeper = $state->textKeeper($firstRouter);

        $this->assertSame($keeper, $state->textKeeper($secondRouter));
        $this->assertSame([$firstRouter], $createdFor);
        $this->assertSame([$secondRouter], $keeper->routers);
    }

    public function testClearDropsCachedKeepers(): void
    {
        $created = 0;
        $state = new loader_state(
            static function () use (&$created): ViewRouterLoaderStateJsonKeeperDouble {
                $created++;

                return new ViewRouterLoaderStateJsonKeeperDouble();
            }
        );
        $router = new ViewRouterLoaderStateRouterDouble();

        $first = $state->jsonKeeper($router);
        $state->clear();
        $second = $state->jsonKeeper($router);

        $this->assertNotSame($first, $second);
        $this->assertSame(2, $created);
    }

    public function testSourceUsesInjectedKeeperFactoriesDirectly(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('($this->jsonKeeperFactory)($router)', $source);
        $this->assertStringContainsString('($this->textKeeperFactory)($router)', $source);
        $this->assertStringNotContainsString('call_user_func', $source);
    }
}

final class ViewRouterLoaderStateRouterDouble extends loader
{
    public function __construct()
    {
    }
}

final class ViewRouterLoaderStateJsonKeeperDouble extends json
{
    public array $routers = [];

    public function __construct()
    {
    }

    public function addRouter(loader $router): void
    {
        $this->routers[] = $router;
    }
}

final class ViewRouterLoaderStateTextKeeperDouble extends text
{
    public array $routers = [];

    public function __construct()
    {
    }

    public function addRouter(loader $router): static
    {
        $this->routers[] = $router;

        return $this;
    }
}
