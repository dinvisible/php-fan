<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;


class TemplateFilesTest extends TestCase
{
    public function testDefaultTemplateRendersTextResponseFromTplVars(): void
    {
        $renderer = new ErrorTemplateRenderer(['message' => 'plain']);

        $result = $renderer->render('core/error/template/default.php');

        $this->assertSame('header:200;type:text', $result[0]);
        $this->assertSame('message=plain', $result['text']);
    }

    public function testError403TemplateSetsForbiddenHtmlResponse(): void
    {
        $renderer = new ErrorTemplateRenderer();

        $result = $renderer->render('core/error/template/error_403.php');

        $this->assertSame('header:403;type:html', $result[0]);
        $this->assertSame('<!doctype html>', $result['doctype']);
        $this->assertArrayNotHasKey('text', $result);
    }

    public function testError404TemplateSetsNotFoundHtmlResponse(): void
    {
        $renderer = new ErrorTemplateRenderer();

        $result = $renderer->render('core/error/template/error_404.php');

        $this->assertSame('header:404;type:html', $result[0]);
        $this->assertSame('<!doctype html>', $result['doctype']);
    }

    public function testError500TemplateRendersXhtmlTextParagraphs(): void
    {
        $renderer = new ErrorTemplateRenderer(['first' => 'one', 'second' => 'two']);

        $result = $renderer->render('core/error/template/error_500.php');

        $this->assertSame('header:500;type:xhtml', $result[0]);
        $this->assertSame('<!doctype html>', $result['doctype']);
        $this->assertSame('first=one</p><p>second=two', $result['text']);
    }
}

class ErrorTemplateRenderer implements \fan\core\error\error_template_context
{
    public function __construct(private array $tplVars = [])
    {
    }

    public function render(string $file): array
    {
        $provider = require dirname(__DIR__, 3) . '/' . $file;

        return $provider($this);
    }

    public function setResponseHeader(mixed $code): string
    {
        return 'header:' . $code . ';';
    }

    public function setContentType(mixed $type): string
    {
        return 'type:' . $type;
    }

    public function setDoctype(): string
    {
        return '<!doctype html>';
    }

    public function getTplVar(?string $key = null): mixed
    {
        return $key === null ? $this->tplVars : ($this->tplVars[$key] ?? '');
    }

    public function convArrayToSting(mixed $data, string $separator = "\n"): string
    {
        $result = [];
        foreach ((array)$data as $key => $value) {
            $result[] = $key . '=' . $value;
        }

        return implode($separator, $result);
    }
}
