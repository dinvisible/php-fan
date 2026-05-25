<?php

declare(strict_types=1);

use fan\core\view\router\html;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\block\base;
use fan\core\view\router;


class ViewRouterHtmlTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/view/router/html.php';

    public function testHtmlRouterHasSingleTplKeeperAndDefaultTplKey(): void
    {
        $router = new html(new ViewRouterHtmlBlockDouble());

        $this->assertCount(1, $router);
        $this->assertTrue(isset($router['tpl']));
        $this->assertSame('tpl', $this->property($router, 'defaultKey'));
        $this->assertSame(['tpl' => null], $this->property($router, 'keepers'));
    }

    private function property(html $router, string $name): mixed
    {
        $property = new ReflectionProperty(router::class, $name);
        return $property->getValue($router);
    }
}

final class ViewRouterHtmlBlockDouble extends base
{
    public function __construct()
    {
    }
}
