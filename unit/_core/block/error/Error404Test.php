<?php

declare(strict_types=1);

use fan\core\block\error\error404;
use FanTest\_core\SourceFileContractTestCase;

class BlockErrorError404Test extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/block/error/error404.php';

    public function testSetViewVarsPopulatesViewAndLoaderPayload(): void
    {
        $block = new BlockError404Probe('loader');

        $block->setViewVars('404', 'Not found', '404 Not found');

        $this->assertSame('404', $block->view->error);
        $this->assertSame('Not found', $block->view->message);
        $this->assertSame('/home', $block->view->homeUri);
        $this->assertSame([
            ['error', '404'],
            ['message', 'Not found'],
        ], $block->view->jsonCalls);
        $this->assertSame(['404 Not found'], $block->view->textCalls);
    }
}

final class BlockError404Probe extends error404
{
    public object $view;

    private string $format;

    public function __construct(string $format)
    {
        $this->format = $format;
        $this->view = new BlockError404ViewDouble();
        $this->tab = new BlockError404TabDouble();
    }

    public function getViewFormat(): string
    {
        return $this->format;
    }
}

final class BlockError404ViewDouble
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

final class BlockError404TabDouble
{
    public function getURI(string $urn): string
    {
        return $urn === '~/' ? '/home' : $urn;
    }
}
