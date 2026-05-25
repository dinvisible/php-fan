<?php

declare(strict_types=1);

use fan\core\view\parser\loader;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\block\base;
use fan\core\service\header;
use fan\core\view\keeper\loader\json;
use fan\core\view\keeper\loader\text;
use fan\core\view\router\loader as router_loader;
use fan\core\view\router\loader_state;
use fan\project\adapter\data_loader;


if (!function_exists('adduceToArray')) {
    function adduceToArray(mixed $src): array
    {
        if (empty($src)) {
            return [];
        }

        return match (gettype($src)) {
            'array' => $src,
            'object' => method_exists($src, 'toArray') ? $src->toArray() : (array)$src,
            'integer', 'double', 'string' => [$src],
            default => [],
        };
    }
}

if (!function_exists('array_merge_recursive_alt')) {
    function array_merge_recursive_alt(mixed $arrFirst): mixed
    {
        if (!is_array($arrFirst)) {
            $arrFirst = $arrFirst === null ? [] : [$arrFirst];
        }
        foreach (array_slice(func_get_args(), 1) as $arrNext) {
            if ($arrNext === null) {
                continue;
            }
            $arrNext = is_array($arrNext) ? $arrNext : [$arrNext];
            foreach ($arrNext as $key => $value) {
                $arrFirst[$key] = isset($arrFirst[$key]) && (is_array($arrFirst[$key]) || is_array($value))
                    ? array_merge_recursive_alt($arrFirst[$key], $value)
                    : $value;
            }
        }

        return $arrFirst;
    }
}

class ViewParserLoaderTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/view/parser/loader.php';

    public function testFormatIsLoader(): void
    {
        $this->assertSame('loader', loader::getFormat());
    }

    public function testGetRouterAcceptsInjectedLoaderState(): void
    {
        $state = new loader_state(
            static fn(router_loader $router): ViewParserLoaderJsonKeeperDouble => new ViewParserLoaderJsonKeeperDouble(),
            static fn(router_loader $router): ViewParserLoaderTextKeeperDouble => new ViewParserLoaderTextKeeperDouble()
        );
        $block = new ViewParserLoaderBlockDouble('main');

        $router = loader::getRouter($block, $state, self::viewRouterFactory());

        $this->assertInstanceOf(router_loader::class, $router);
        $this->assertSame($router, $router->setJson('from-state', true));
        $this->assertTrue(loader::getRouter($block, $state, self::viewRouterFactory())->getJson('from-state'));
    }

    public function testGetRouterRequiresInjectedLoaderState(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('Loader state is not configured for loader view parser.', $source);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Loader state is not configured for loader view parser.');

        loader::getRouter(new ViewParserLoaderBlockDouble('main'));
    }

    public function testGetRouterRequiresInjectedViewRouterFactory(): void
    {
        $state = new loader_state(
            static fn(router_loader $router): ViewParserLoaderJsonKeeperDouble => new ViewParserLoaderJsonKeeperDouble(),
            static fn(router_loader $router): ViewParserLoaderTextKeeperDouble => new ViewParserLoaderTextKeeperDouble()
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('View router factory is not configured for view parser.');

        loader::getRouter(new ViewParserLoaderBlockDouble('main'), $state);
    }

    public function testSourceNoLongerCreatesViewRouterFactoryFallback(): void
    {
        $this->assertStringNotContainsString('new \fan\core\di\view_router_factory', $this->sourceCode());
    }

    public function testTplResultCombinesHtmlDataAndEmbeddedBlockOutput(): void
    {
        $child = new ViewParserLoaderBlockDouble('child', htmlData: ['child']);
        $root = new ViewParserLoaderBlockDouble('root', htmlData: ['Hello ', 'root'], embedded: [$child]);
        $parser = new ViewParserLoaderProbe(new ViewParserLoaderBlockDouble('main'));

        $this->assertSame(['root' => 'Hello rootchild'], $parser->_getTplResult($root));
    }

    public function testResultDataUsesLoaderRouterJsonHtmlAndTextChannels(): void
    {
        $block = new ViewParserLoaderBlockDouble(
            'root',
            ['HTML'],
            [],
            ['answer' => 42],
            'plain text'
        );
        $parser = new ViewParserLoaderProbe(new ViewParserLoaderBlockDouble('main'));

        $this->assertSame([
            'json' => ['answer' => 42],
            'html' => 'HTML',
            'text' => 'plain text',
        ], $parser->getResultData($block));
    }

    public function testFinalContentCreatesDataLoaderWithInjectedServicesWhenBlockDoesNotProvideOne(): void
    {
        $json = new ViewParserLoaderJsonDouble();
        $parser = new ViewParserLoaderProbe(new ViewParserLoaderBlockDouble('main'), new ViewParserLoaderInputDouble(), $json);
        $parser->startParsing(new ViewParserLoaderBlockDouble('root', ['HTML'], [], ['answer' => 42], 'plain text'));

        $this->assertSame('encoded-loader-payload', $parser->getFinalContent());
        $this->assertSame([[
            'json' => ['answer' => 42],
            'text' => 'plain text',
            'html' => 'HTML',
        ]], $json->payloads);
        $this->assertSame([['encoded-loader-payload', 'application/json', false]], $parser->headers);
    }

    public function testSourceUsesInjectedDataLoaderFactoryDirectly(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('return ($this->dataLoaderFactory)();', $source);
        $this->assertStringNotContainsString('call_user_func', $source);
    }

    private static function viewRouterFactory(): callable
    {
        return static fn(
            string $viewClass,
            base $block,
            ?loader_state $loaderState = null
        ): router_loader => new router_loader($block, $loaderState);
    }
}

final class ViewParserLoaderProbe extends loader
{
    public array $headers = [];

    public function __construct(base $mainBlock, private ?object $input = null, private ?object $json = null)
    {
        parent::__construct(
            $mainBlock,
            null,
            null,
            null,
            null,
            fn(): object => new data_loader(
                $this->input,
                $this->json,
                static fn(mixed $value): array => is_array($value) ? $value : [$value],
                static fn(mixed ...$values): array => array_replace_recursive(...$values)
            )
        );
    }

    protected function _setHeaders($result, $contentType = 'text/plain', $encoding = null): header
    {
        $this->headers[] = [$result, $contentType, $encoding];

        return new ViewParserLoaderHeaderDouble();
    }
}

final class ViewParserLoaderBlockDouble extends base
{
    public function __construct(
        private string $name,
        private array $htmlData = [],
        private array $embedded = [],
        private array $jsonData = [],
        private string $textData = ''
    ) {
    }

    public function getBlockName(): string
    {
        return $this->name;
    }

    public function getView(): object
    {
        return new ViewParserLoaderViewDouble($this->htmlData, $this->jsonData, $this->textData);
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

final class ViewParserLoaderInputDouble
{
    public function request(): array
    {
        return [];
    }
}

final class ViewParserLoaderHeaderDouble extends header
{
    public function __construct()
    {
    }
}

final class ViewParserLoaderJsonDouble
{
    public array $payloads = [];

    public function encode(mixed $payload): string
    {
        $this->payloads[] = $payload;

        return 'encoded-loader-payload';
    }
}

final class ViewParserLoaderJsonKeeperDouble extends json
{
    private array $values = [];

    public function __construct()
    {
    }

    public function set(mixed $key, mixed $value, bool $rewriteExisting = true, ?bool $convArray = null): static
    {
        $this->values[(string)$key] = $value;

        return $this;
    }

    public function get(mixed $key = null, mixed $default = null, bool $logError = true): mixed
    {
        return $key === null ? $this->values : ($this->values[(string)$key] ?? $default);
    }
}

final class ViewParserLoaderTextKeeperDouble extends text
{
    public function __construct()
    {
    }
}

final class ViewParserLoaderViewDouble
{
    public object $html;

    public function __construct(private array $htmlData, private array $jsonData, private string $textData)
    {
        $this->html = new class($htmlData) {
            public function __construct(private array $htmlData)
            {
            }

            public function toArray(): array
            {
                return $this->htmlData;
            }
        };
    }

    public function getJson(): array
    {
        return $this->jsonData;
    }

    public function getText(): string
    {
        return $this->textData;
    }
}
