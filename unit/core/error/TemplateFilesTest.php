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

class ErrorTemplateRenderer
{
    public function __construct(private array $tplVars = [])
    {
    }

    public function render(string $file): array
    {
        return require dirname(__DIR__, 3) . '/' . $file;
    }

    public function setResponseHeader(int $code): string
    {
        return 'header:' . $code . ';';
    }

    public function setContentType(string $type): string
    {
        return 'type:' . $type;
    }

    public function setDoctype(): string
    {
        return '<!doctype html>';
    }

    public function getTplVar(): array
    {
        return $this->tplVars;
    }

    public function convArrayToSting(array $data, string $separator = "\n"): string
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[] = $key . '=' . $value;
        }

        return implode($separator, $result);
    }
}
