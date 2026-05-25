<?php

declare(strict_types=1);

use fan\core\di\matcher_item_component_factory;
use PHPUnit\Framework\TestCase;
use fan\core\service\matcher\item;

final class MatcherItemComponentFactoryTest extends TestCase
{
    public function testFactoryCreatesComponentForMatcherItem(): void
    {
        require_once dirname(__DIR__, 3) . '/_core/service/matcher/item.php';

        $item = new class extends item {
            public function __construct()
            {
            }
        };

        $delegatedClass = null;
        $delegatedArguments = null;
        $factory = new matcher_item_component_factory(
            static function (string $className, array $arguments) use (&$delegatedClass, &$delegatedArguments): object {
                $delegatedClass = $className;
                $delegatedArguments = $arguments;

                return new $className(...$arguments);
            }
        );
        $component = $factory(MatcherItemComponentFactoryComponentDouble::class, $item);

        $this->assertSame(MatcherItemComponentFactoryComponentDouble::class, $delegatedClass);
        $this->assertSame([$item], $delegatedArguments);
        $this->assertInstanceOf(MatcherItemComponentFactoryComponentDouble::class, $component);
        $this->assertSame($item, $component->item);
    }}

final class MatcherItemComponentFactoryComponentDouble
{
    public function __construct(public item $item)
    {
    }
}
