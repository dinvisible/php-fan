<?php

declare(strict_types=1);

use fan\core\block\error\error500;
use FanTest\core\SourceFileContractTestCase;

class BlockErrorError500Test extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/block/error/error500.php';

    public function testSetViewVarsPopulatesViewAndLoaderPayload(): void
    {
        $block = new BlockError500Probe('loader');

        $block->setViewVars('500', 'Server error', '500 Server error');

        $this->assertSame('500', $block->view->error);
        $this->assertSame('Server error', $block->view->message);
        $this->assertSame('/home', $block->view->homeUri);
        $this->assertSame([
            ['error', '500'],
            ['message', 'Server error'],
        ], $block->view->jsonCalls);
        $this->assertSame(['500 Server error'], $block->view->textCalls);
    }
}

final class BlockError500Probe extends error500
{
    public object $view;

    private string $format;

    public function __construct(string $format)
    {
        $this->format = $format;
        $this->view = new BlockError500ViewDouble();
        $this->tab = new BlockError500TabDouble();
    }

    public function getViewFormat(): string
    {
        return $this->format;
    }
}

final class BlockError500ViewDouble
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

final class BlockError500TabDouble
{
    public function getURI(string $urn): string
    {
        return $urn === '~/' ? '/home' : $urn;
    }
}
