<?php

declare(strict_types=1);

use fan\core\view\parser\debug1;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\block\base;


class GeneratedPendingViewParserDebug1Test extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/view/parser/debug1.php';

    public function testDebugExternalFilesUseBooleanMode(): void
    {
        $this->assertStringContainsString('$this->debug->setExtFiles($rootBlock, true);', $this->sourceCode());
    }

    public function testDebugServiceIsInjectedIntoResultRendering(): void
    {
        $child = new ViewParserDebug1BlockDouble('child', ['child']);
        $root = new ViewParserDebug1BlockDouble('root', ['root-'], [$child]);
        $debug = new ViewParserDebug1DebugDouble();
        $parser = new debug1(new ViewParserDebug1BlockDouble('main'), null, $debug);

        $this->assertSame(['root' => 'root-wrapped(root:wrapped(child:child))'], $parser->getResultData($root));
        $this->assertSame([$root], $debug->setExtFilesBlocks);
    }
}

final class ViewParserDebug1DebugDouble
{
    public array $setExtFilesBlocks = [];

    public function setExtFiles(base $block, bool $wrapped): void
    {
        $this->setExtFilesBlocks[] = $block;
    }

    public function wrapHtmlCode(string $html, base $block): string
    {
        return 'wrapped(' . $block->getBlockName() . ':' . $html . ')';
    }
}

final class ViewParserDebug1BlockDouble extends base
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
