<?php

declare(strict_types=1);

use fan\core\view\router\json;
use FanTest\core\SourceFileContractTestCase;
use fan\core\block\base;


class ViewRouterJsonTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/view/router/json.php';

    public function testJsonRouterStartsWithoutBase64AndCanToggleIt(): void
    {
        $router = new json(new ViewRouterJsonBlockDouble());

        $this->assertFalse($router->isUseBase64());
        $this->assertSame($router, $router->useBase64());
        $this->assertTrue($router->isUseBase64());
        $this->assertSame($router, $router->useBase64(false));
        $this->assertFalse($router->isUseBase64());
    }

    public function testJsonRouterKeepsSimpleRouterDataKeeperConfiguration(): void
    {
        $router = new json(new ViewRouterJsonBlockDouble());

        $this->assertCount(1, $router);
        $this->assertTrue(isset($router['data']));
    }
}

final class ViewRouterJsonBlockDouble extends base
{
    public function __construct()
    {
    }
}
