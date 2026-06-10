<?php

declare(strict_types=1);

use fan\core\view\parser;
use FanTest\core\SourceFileContractTestCase;
use fan\core\block\base;
use fan\core\service\header;
use fan\core\view\router;


class ViewParserTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/view/parser.php';

    public function testResultDataIncludesEmbeddedBlockDataByBlockName(): void
    {
        $child = new ViewParserBlockDouble('child', ['value' => 2]);
        $root = new ViewParserBlockDouble('root', ['value' => 1], [$child]);
        $parser = new ViewParserProbe(new ViewParserBlockDouble('main'));

        $this->assertSame([
            'value' => 1,
            'child' => ['value' => 2],
        ], $parser->getResultData($root));
    }

    public function testStartParsingStoresRootBlockAndResult(): void
    {
        $root = new ViewParserBlockDouble('root', ['value' => 1]);
        $parser = new ViewParserProbe(new ViewParserBlockDouble('main'));

        $this->assertSame($parser, $parser->startParsing($root));
        $this->assertSame($root, $parser->rootBlock());
        $this->assertSame(['value' => 1], $parser->result());
    }

    public function testMixEmbeddedDataLetsBlockDataOverrideEmbeddedValues(): void
    {
        $parser = new ViewParserProbe(new ViewParserBlockDouble('main'));

        $this->assertSame([
            'shared' => 'block',
            'child' => 1,
            'own' => true,
        ], $parser->exposeMixEmbededData(
            ['shared' => 'block', 'own' => true],
            [['shared' => 'embedded', 'child' => 1]]
        ));
    }

    public function testParseTemplateConcatenatesScalarViewDataWhenNoTemplateIsSet(): void
    {
        $parser = new ViewParserProbe(new ViewParserBlockDouble('main'));

        $this->assertSame('hello42object', $parser->exposeParseTemplate(
            new ViewParserBlockDouble('block'),
            ['hello', 42, ['ignored'], new ViewParserStringableDouble('object')]
        ));
    }

    public function testParseTemplateReturnsEmptyStringWhenRoleConditionIsPresent(): void
    {
        $parser = new ViewParserProbe(new ViewParserBlockDouble('main'));

        $this->assertSame('', $parser->exposeParseTemplate(
            new ViewParserBlockDouble('block', [], [], ['hidden' => true]),
            ['visible']
        ));
    }

    public function testParseTemplateUsesInjectedTemplateFactory(): void
    {
        $template = new ViewParserTemplateDouble();
        $factoryCalls = [];
        $parser = new ViewParserProbe(
            new ViewParserBlockDouble('main'),
            null,
            function (string $path, mixed $parentClass, base $block) use ($template, &$factoryCalls): object {
                $factoryCalls[] = [$path, $parentClass, $block->getBlockName()];

                return $template;
            }
        );

        $this->assertSame('rendered-template', $parser->exposeParseTemplate(
            new ViewParserBlockDouble('block', template: 'path.tpl', tplParentClass: 'parent-template'),
            ['title' => 'Hello']
        ));
        $this->assertSame([['path.tpl', 'parent-template', 'block']], $factoryCalls);
        $this->assertSame(['title' => 'Hello'], $template->assigned);
    }

    public function testSetHeadersUsesInjectedHeaderAndLocale(): void
    {
        $header = new ViewParserHeaderDouble();
        $parser = new ViewParserProbe(
            new ViewParserBlockDouble('main'),
            null,
            null,
            $header,
            new ViewParserLocaleDouble()
        );

        $this->assertSame($header, $parser->exposeSetHeaders('payload', 'text/plain'));
        $this->assertSame([
            ['length', 7],
            ['contentType', 'text/plain'],
            ['encoding', 'charset=utf-8'],
        ], $header->headers);
    }

    public function testSourceUsesInjectedFactoriesDirectly(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('return ($this->templateFactory)($template, $tplParentClass, $block);', $source);
        $this->assertStringContainsString('return ($this->jsonFactory)($useBase64);', $source);
        $this->assertStringContainsString('createUnsupportedParserException(', $source);
        $this->assertStringContainsString('protected static function viewRouterFactory(?callable $viewRouterFactory): callable', $source);
        $this->assertStringContainsString('View router factory is not configured for view parser.', $source);
        $this->assertStringNotContainsString('new \\fan\\project\\exception\\error500', $source);
        $this->assertStringNotContainsString('new \fan\core\di\view_router_factory', $source);
        $this->assertStringNotContainsString('call_user_func', $source);
    }

    public function testGetRouterUsesInjectedViewRouterFactory(): void
    {
        $block = new ViewParserBlockDouble('main');
        $router = new ViewParserRouterDouble($block);
        $factoryCalls = [];

        $result = parser::getRouter(
            $block,
            static function (string $viewClass, base $receivedBlock) use (&$factoryCalls, $router): router {
                $factoryCalls[] = [$viewClass, $receivedBlock];

                return $router;
            }
        );

        $this->assertSame($router, $result);
        $this->assertSame([[parser::class, $block]], $factoryCalls);
    }

    public function testGetRouterRequiresInjectedViewRouterFactory(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('View router factory is not configured for view parser.');

        parser::getRouter(new ViewParserBlockDouble('main'));
    }

    public function testBaseParserFormatUsesInjectedExceptionFactory(): void
    {
        $expected = new RuntimeException('unsupported parser');
        $calls = [];

        try {
            parser::getFormat(static function (string $message) use (&$calls, $expected): Throwable {
                $calls[] = $message;

                return $expected;
            });
            $this->fail('Base parser format must throw the injected exception.');
        } catch (Throwable $exception) {
            $this->assertSame($expected, $exception);
        }

        $this->assertSame([
            'Class "fan\core\view\parser" can\'t be use for define View-type',
        ], $calls);
    }
}

final class ViewParserProbe extends parser
{
    public function __construct(
        base $mainBlock,
        ?callable $jsonFactory = null,
        ?callable $templateFactory = null,
        ?object $header = null,
        ?object $locale = null
    ) {
        parent::__construct($mainBlock, $jsonFactory, $templateFactory, $header, $locale);
    }

    public function exposeMixEmbededData(array $blockData, array $embededData): array
    {
        return $this->_mixEmbededData($blockData, $embededData);
    }

    public function exposeParseTemplate(base $block, mixed $tplVar): string
    {
        return $this->_parseTemplate($block, $tplVar);
    }

    public function exposeSetHeaders(mixed $result, string $contentType = 'text/plain', mixed $encoding = null): header
    {
        return $this->_setHeaders($result, $contentType, $encoding);
    }

    public function rootBlock(): ?object
    {
        $property = new ReflectionProperty(parser::class, 'rootBlock');
        return $property->getValue($this);
    }

    public function result(): ?array
    {
        $property = new ReflectionProperty(parser::class, 'result');
        return $property->getValue($this);
    }
}

final class ViewParserBlockDouble extends base
{
    public function __construct(
        private string $name,
        private array $viewData = [],
        private array $embedded = [],
        private ?array $roleConditionDouble = null,
        private ?string $template = null,
        private mixed $tplParentClass = null
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
        return $this->roleConditionDouble;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function getMeta(array|string|null $key = null, mixed $default = null, bool $convToArray = false): mixed
    {
        return $key === 'tpl_parent_class' ? $this->tplParentClass : $default;
    }
}

final class ViewParserTemplateDouble
{
    public array $assigned = [];

    public function assign(string $key, mixed $value): void
    {
        $this->assigned[$key] = $value;
    }

    public function fetch(): string
    {
        return 'rendered-template';
    }
}

final class ViewParserHeaderDouble extends header
{
    public array $headers = [];

    public function __construct()
    {
    }

    public function addHeader(string $key, mixed $value): static
    {
        $this->headers[] = [$key, $value];

        return $this;
    }
}

final class ViewParserLocaleDouble
{
    public function getCharacterSet(): string
    {
        return 'utf-8';
    }
}

final class ViewParserRouterDouble extends router
{
    protected array $keepers = [
        'data' => null,
    ];
}

final class ViewParserStringableDouble
{
    public function __construct(private string $value)
    {
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
