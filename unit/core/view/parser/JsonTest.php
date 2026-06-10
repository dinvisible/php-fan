<?php

declare(strict_types=1);

use fan\core\view\parser\json;
use FanTest\core\SourceFileContractTestCase;
use fan\core\block\base;
use fan\core\service\header;
use fan\core\view\parser;
use fan\core\view\router\json as router_json;


class ViewParserJsonTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/view/parser/json.php';

    public function testFormatIsJson(): void
    {
        $this->assertSame('json', json::getFormat());
    }

    public function testGetRouterUsesInjectedViewRouterFactory(): void
    {
        $block = new ViewParserJsonBlockDouble('main');
        $router = new router_json($block);
        $factoryCalls = [];

        $result = json::getRouter(
            $block,
            static function (string $viewClass, base $receivedBlock) use (&$factoryCalls, $router): router_json {
                $factoryCalls[] = [$viewClass, $receivedBlock];

                return $router;
            }
        );

        $this->assertSame($router, $result);
        $this->assertSame([[json::class, $block]], $factoryCalls);
    }

    public function testGetRouterRequiresInjectedViewRouterFactory(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('View router factory is not configured for view parser.');

        json::getRouter(new ViewParserJsonBlockDouble('main'));
    }

    public function testSourceNoLongerCreatesViewRouterFactoryFallback(): void
    {
        $this->assertStringNotContainsString('new \fan\core\di\view_router_factory', $this->sourceCode());
    }

    public function testFinalContentEncodesResultAndSetsJsonHeaders(): void
    {
        $jsonFactory = new ViewParserJsonFactoryDouble();
        $parser = new ViewParserJsonProbe(
            new ViewParserJsonBlockDouble('main'),
            $jsonFactory(...)
        );
        $parser->setRootBlock(new ViewParserJsonBlockDouble('root', useBase64: true));
        $parser->setResult(['answer' => 42]);

        $this->assertSame('{"answer":42}', $parser->getFinalContent());
        $this->assertSame([true], $jsonFactory->useBase64Calls);
        $this->assertSame([
            ['length', 13],
            ['contentType', 'application/json'],
            ['encoding', 'charset=utf-8'],
        ], $parser->header->headers);
    }
}

final class ViewParserJsonProbe extends json
{
    public ViewParserJsonHeaderDouble $header;

    public function __construct(base $mainBlock, ?callable $jsonFactory = null)
    {
        $header = new ViewParserJsonHeaderDouble();
        parent::__construct($mainBlock, $jsonFactory, null, $header, new ViewParserJsonLocaleDouble());
        $this->header = $header;
    }

    public function setRootBlock(object $rootBlock): void
    {
        $property = new ReflectionProperty(parser::class, 'rootBlock');
        $property->setValue($this, $rootBlock);
    }

    public function setResult(array $result): void
    {
        $property = new ReflectionProperty(parser::class, 'result');
        $property->setValue($this, $result);
    }
}

final class ViewParserJsonFactoryDouble
{
    public array $useBase64Calls = [];

    public function __invoke(bool $useBase64): object
    {
        $this->useBase64Calls[] = $useBase64;

        return new ViewParserJsonServiceDouble();
    }
}

final class ViewParserJsonBlockDouble extends base
{
    public function __construct(private string $name, private bool $useBase64 = false)
    {
    }

    public function getBlockName(): string
    {
        return $this->name;
    }

    public function getView(): object
    {
        return new class($this->useBase64) {
            public function __construct(private bool $useBase64)
            {
            }

            public function isUseBase64(): bool
            {
                return $this->useBase64;
            }
        };
    }
}

final class ViewParserJsonServiceDouble
{
    public function encode(mixed $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}

final class ViewParserJsonHeaderDouble extends header
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

final class ViewParserJsonLocaleDouble
{
    public function getCharacterSet(): string
    {
        return 'utf-8';
    }
}
