<?php

declare(strict_types=1);

use fan\core\view\router\simple;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\block\base;
use fan\core\view\router;


class ViewRouterSimpleTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/view/router/simple.php';

    public function testSimpleRouterHasSingleDataKeeperAndDefaultDataKey(): void
    {
        $router = new simple(new ViewRouterSimpleBlockDouble());

        $this->assertCount(1, $router);
        $this->assertTrue(isset($router['data']));
        $this->assertSame('data', $this->property($router, 'defaultKey'));
        $this->assertSame(['data' => null], $this->property($router, 'keepers'));
    }

    private function property(simple $router, string $name): mixed
    {
        $property = new ReflectionProperty(router::class, $name);
        return $property->getValue($router);
    }
}

final class ViewRouterSimpleBlockDouble extends base
{
    public function __construct()
    {
    }
}
