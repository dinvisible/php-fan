<?php

declare(strict_types=1);

use fan\core\di\view_router_factory;
use PHPUnit\Framework\TestCase;
use fan\core\block\base;
use fan\core\view\parser;
use fan\core\view\parser\html as parser_html;
use fan\core\view\parser\json as parser_json;
use fan\core\view\parser\loader as parser_loader;
use fan\core\view\router\html;
use fan\core\view\router\json;
use fan\core\view\router\loader;
use fan\core\view\router\loader_state;
use fan\core\view\router\simple;


require_once dirname(__DIR__, 3) . '/core/factory/view_keeper_factory.php';
require_once dirname(__DIR__, 3) . '/core/view/router.php';
require_once dirname(__DIR__, 3) . '/core/view/router/simple.php';
require_once dirname(__DIR__, 3) . '/core/view/router/html.php';
require_once dirname(__DIR__, 3) . '/core/view/router/json.php';
require_once dirname(__DIR__, 3) . '/core/view/router/loader_state.php';
require_once dirname(__DIR__, 3) . '/core/view/router/loader.php';
require_once dirname(__DIR__, 3) . '/core/view/parser.php';
require_once dirname(__DIR__, 3) . '/core/view/parser/html.php';
require_once dirname(__DIR__, 3) . '/core/view/parser/json.php';
require_once dirname(__DIR__, 3) . '/core/view/parser/loader.php';

final class ViewRouterFactoryTest extends TestCase
{
    public function testFactoryCreatesProjectViewRouters(): void
    {
        $factory = new view_router_factory();
        $block = new ViewRouterFactoryBlockDouble('main');

        $this->assertInstanceOf(
            simple::class,
            $factory(parser::class, $block)
        );
        $this->assertInstanceOf(
            html::class,
            $factory(parser_html::class, $block)
        );
        $this->assertInstanceOf(
            json::class,
            $factory(parser_json::class, $block)
        );
        $this->assertInstanceOf(
            loader::class,
            $factory(parser_loader::class, $block, new loader_state())
        );
    }

    public function testLoaderRouterRequiresLoaderState(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Loader state is not configured for loader view router.');

        (new view_router_factory())(parser_loader::class, new ViewRouterFactoryBlockDouble('main'));
    }

    public function testFactoryPassesBlockExceptionFactoryToCreatedRouter(): void
    {
        $calls = [];
        $block = new ViewRouterFactoryBlockDouble('main');
        $blockExceptionFactory = static function (
            string $exceptionClass,
            base $block,
            string $message,
            int $code,
            ?\Exception $previous = null
        ) use (&$calls): \Throwable {
            $calls[] = [$exceptionClass, $block, $message, $code, $previous];

            return new \RuntimeException($message, $code, $previous);
        };

        $router = (new view_router_factory())(
            parser::class,
            $block,
            null,
            $blockExceptionFactory
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
    }}

final class ViewRouterFactoryBlockDouble extends base
{
    public function __construct(private string $name)
    {
    }

    public function getBlockName(): string
    {
        return $this->name;
    }
}
