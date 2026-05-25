<?php

declare(strict_types=1);

use fan\core\view\parser\html;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\block\base;
use fan\core\view\router\html as router_html;


class ViewParserHtmlTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/view/parser/html.php';

    public function testFormatIsHtml(): void
    {
        $this->assertSame('html', html::getFormat());
    }

    public function testGetRouterUsesInjectedViewRouterFactory(): void
    {
        $block = new ViewParserHtmlBlockDouble('main');
        $router = new router_html($block);
        $factoryCalls = [];

        $result = html::getRouter(
            $block,
            static function (string $viewClass, base $receivedBlock) use (&$factoryCalls, $router): router_html {
                $factoryCalls[] = [$viewClass, $receivedBlock];

                return $router;
            }
        );

        $this->assertSame($router, $result);
        $this->assertSame([[html::class, $block]], $factoryCalls);
    }

    public function testGetRouterRequiresInjectedViewRouterFactory(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('View router factory is not configured for view parser.');

        html::getRouter(new ViewParserHtmlBlockDouble('main'));
    }

    public function testSourceNoLongerCreatesViewRouterFactoryFallback(): void
    {
        $this->assertStringNotContainsString('new \fan\core\di\view_router_factory', $this->sourceCode());
    }

    public function testResultDataParsesBlockAndEmbeddedBlocksIntoNamedHtml(): void
    {
        $child = new ViewParserHtmlBlockDouble('child', ['child text']);
        $root = new ViewParserHtmlBlockDouble('root', ['Hello ', 'world'], [$child]);
        $parser = new ViewParserHtmlProbe(new ViewParserHtmlBlockDouble('main'));

        $this->assertSame([
            'root' => 'Hello worldchild text',
        ], $parser->getResultData($root));
    }
}

final class ViewParserHtmlProbe extends html
{
}

final class ViewParserHtmlBlockDouble extends base
{
    public function __construct(
        private string $name,
        private array $viewData = [],
        private array $embedded = []
    ) {
    }

    public function getBlockName(): string
    {
        return $this->name;
    }

    public function getViewData(): array
    {
        return $this->viewData;
    }

    public function getEmbeddedBlocks(): array
    {
        return $this->embedded;
    }

    public function getRoleCondition(): ?array
    {
        return null;
    }

    public function getTemplate(): ?string
    {
        return null;
    }
}
