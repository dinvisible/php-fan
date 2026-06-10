<?php

declare(strict_types=1);

use fan\core\block\error\error403;
use FanTest\core\SourceFileContractTestCase;

class BlockErrorError403Test extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/block/error/error403.php';

    public function testSetViewVarsPopulatesViewAndLoaderPayload(): void
    {
        $block = new BlockError403Probe('loader');

        $block->setViewVars('403', 'Forbidden', '403 Forbidden');

        $this->assertSame('403', $block->view->error);
        $this->assertSame('Forbidden', $block->view->message);
        $this->assertSame('/home', $block->view->homeUri);
        $this->assertSame([
            ['error', '403'],
            ['message', 'Forbidden'],
        ], $block->view->jsonCalls);
        $this->assertSame(['403 Forbidden'], $block->view->textCalls);
    }
}

final class BlockError403Probe extends error403
{
    public object $view;

    private string $format;

    public function __construct(string $format)
    {
        $this->format = $format;
        $this->view = new BlockError403ViewDouble();
        $this->tab = new BlockError403TabDouble();
    }

    public function getViewFormat(): string
    {
        return $this->format;
    }
}

final class BlockError403ViewDouble
{
    public ?string $error = null;

    public ?string $message = null;

    public ?string $homeUri = null;

    public array $jsonCalls = [];

    public array $textCalls = [];

    public function setJson(string $key, mixed $value): void
    {
        $this->jsonCalls[] = [$key, $value];
    }

    public function setText(string $text): void
    {
        $this->textCalls[] = $text;
    }
}

final class BlockError403TabDouble
{
    public function getURI(string $urn): string
    {
        return $urn === '~/' ? '/home' : $urn;
    }
}
