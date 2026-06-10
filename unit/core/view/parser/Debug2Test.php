<?php

declare(strict_types=1);

use fan\core\view\parser\debug2;
use FanTest\core\SourceFileContractTestCase;
use fan\core\block\base;


class ViewParserDebug2Test extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/view/parser/debug2.php';

    public function testInternalResultDataRecursivelyBuildsRowsForEmbeddedBlocks(): void
    {
        $child = new ViewParserDebug2BlockDouble('child');
        $root = new ViewParserDebug2BlockDouble('root', [$child]);
        $debug = new ViewParserDebug2DebugDouble();
        $parser = $this->parser($debug);

        $this->assertSame('row:root:view:1', $parser->_getInternalResultData($root, true));
        $this->assertSame([
            ['block' => 'child', 'incl' => [], 'isView' => true],
            ['block' => 'root', 'incl' => ['row:child:view:1'], 'isView' => true],
        ], $debug->rowCalls);
    }

    public function testResultDataWrapsRowsInSecondDebugCode(): void
    {
        $root = new ViewParserDebug2BlockDouble('root');
        $debug = new ViewParserDebug2DebugDouble();
        $parser = $this->parser($debug);

        $this->assertSame([
            'root' => 'code:Debug Info:row:root:view:0',
        ], $parser->getResultData($root));
    }

    public function testResultDataUsesBlockTitleWhenAvailable(): void
    {
        $root = new ViewParserDebug2TitledBlockDouble('root', 'Custom title');
        $parser = $this->parser(new ViewParserDebug2DebugDouble());

        $this->assertSame([
            'root' => 'code:Custom title:row:root:view:0',
        ], $parser->getResultData($root));
    }

    public function testFormatUsesInjectedExceptionFactory(): void
    {
        $expected = new RuntimeException('unsupported debug parser');
        $calls = [];

        try {
            debug2::getFormat(static function (string $message) use (&$calls, $expected): Throwable {
                $calls[] = $message;

                return $expected;
            });
            $this->fail('Debug2 parser format must throw the injected exception.');
        } catch (Throwable $exception) {
            $this->assertSame($expected, $exception);
        }

        $this->assertSame([
            'Class "\fan\core\view\parser\debug2" can\'t be use for define View-type',
        ], $calls);
        $this->assertStringNotContainsString('new \\fan\\project\\exception\\error500', $this->sourceCode());
    }

    private function parser(object $debug): debug2
    {
        return new debug2(new ViewParserDebug2BlockDouble('main'), null, $debug);
    }
}

final class ViewParserDebug2DebugDouble
{
    public array $rowCalls = [];

    public function getSecondDebugRow(base $block, array $incl, bool $isView): string
    {
        $this->rowCalls[] = [
            'block' => $block->getBlockName(),
            'incl' => $incl,
            'isView' => $isView,
        ];

        return 'row:' . $block->getBlockName() . ':view:' . (int)$isView;
    }

    public function getSecondDebugCode(string $blockInfo, string $title): string
    {
        return 'code:' . $title . ':' . $blockInfo;
    }
}

class ViewParserDebug2BlockDouble extends base
{
    public function __construct(private string $name, private array $embedded = [])
    {
    }

    public function getBlockName(): string
    {
        return $this->name;
    }

    public function getEmbeddedBlocks(): array
    {
        return $this->embedded;
    }
}

final class ViewParserDebug2TitledBlockDouble extends ViewParserDebug2BlockDouble
{
    public function __construct(string $name, private string $title)
    {
        parent::__construct($name);
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
