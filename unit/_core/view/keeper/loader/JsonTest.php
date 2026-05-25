<?php

declare(strict_types=1);

use fan\core\view\keeper\loader\json;
use fan\core\view\router\loader;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\base\data;
use fan\core\block\base;


class ViewKeeperLoaderJsonTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/view/keeper/loader/json.php';

    public function testConstructorStoresRouterBlockAndEnablesFullRewrite(): void
    {
        $block = new ViewKeeperLoaderJsonBlockDouble();
        $router = new ViewKeeperLoaderJsonRouterDouble($block);
        $keeper = new json($router);

        $this->assertSame($router, $keeper->getRouter());
        $this->assertSame($block, $keeper->getBlock());
        $this->assertTrue($keeper->isFullRewrite());
    }

    public function testAddRouterRegistersAnotherRouterAndBlockAsSetters(): void
    {
        $keeper = new json(new ViewKeeperLoaderJsonRouterDouble(new ViewKeeperLoaderJsonBlockDouble()));
        $secondBlock = new ViewKeeperLoaderJsonBlockDouble();
        $secondRouter = new ViewKeeperLoaderJsonRouterDouble($secondBlock);

        $keeper->addRouter($secondRouter);

        $setter = $this->setter($keeper);
        $this->assertTrue(in_array($secondRouter, $setter, true));
        $this->assertTrue(in_array($secondBlock, $setter, true));
    }

    private function setter(json $keeper): array
    {
        $property = new ReflectionProperty(data::class, 'setter');
        return $property->getValue($keeper);
    }
}

final class ViewKeeperLoaderJsonRouterDouble extends loader
{
    public function __construct(private base $blockDouble)
    {
    }

    public function getBlock(): base
    {
        return $this->blockDouble;
    }
}

final class ViewKeeperLoaderJsonBlockDouble extends base
{
    public function __construct()
    {
    }
}
