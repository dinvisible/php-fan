<?php

declare(strict_types=1);

use fan\core\di\view_keeper_factory;
use fan\core\di\view_loader_json_keeper_factory;
use fan\core\di\view_loader_state_factory;
use fan\core\di\view_loader_text_keeper_factory;
use PHPUnit\Framework\TestCase;
use fan\core\block\base;
use fan\core\view\keeper;
use fan\core\view\keeper\loader\json;
use fan\core\view\keeper\loader\text;
use fan\core\view\router\loader;
use fan\core\view\router\simple;


require_once dirname(__DIR__, 3) . '/core/view/router.php';
require_once dirname(__DIR__, 3) . '/core/view/router/simple.php';
require_once dirname(__DIR__, 3) . '/core/view/router/loader_state.php';
require_once dirname(__DIR__, 3) . '/core/view/router/loader.php';

final class ViewKeeperFactoriesTest extends TestCase
{
    public function testFactoriesCreateProjectViewKeepers(): void
    {
        $block = new ViewKeeperFactoriesBlockDouble();
        $router = new simple($block, new view_keeper_factory());

        $keeper = (new view_keeper_factory())($router);

        $this->assertInstanceOf(keeper::class, $keeper);
        $this->assertSame($router, $keeper->getRouter());
    }

    public function testLoaderStateFactoryWiresLoaderKeepers(): void
    {
        $state = (new view_loader_state_factory(
            new view_loader_json_keeper_factory(),
            new view_loader_text_keeper_factory()
        ))();
        $router = new loader(
            new ViewKeeperFactoriesBlockDouble(),
            $state,
            new view_keeper_factory()
        );

        $this->assertInstanceOf(json::class, $state->jsonKeeper($router));
        $this->assertInstanceOf(text::class, $state->textKeeper($router));
    }}

final class ViewKeeperFactoriesBlockDouble extends base
{
    public function __construct()
    {
    }
}
