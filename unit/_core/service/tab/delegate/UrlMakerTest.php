<?php

declare(strict_types=1);

use fan\core\service\tab\delegate\urlMaker;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\base\service;


class ServiceTabDelegateUrlMakerTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/tab/delegate/urlMaker.php';

    public function testAddQueryUsesConfiguredSeparatorAndAvoidsDuplicatePair(): void
    {
        $maker = $this->maker([
            'GET_SEPARATOR' => '&',
        ]);

        $this->assertSame('/path?a=1&b=two', $maker->addQuery('/path?a=1', 'b', 'two'));
        $this->assertSame('/path?a=1&b=two', $maker->addQuery('/path?a=1&b=two', 'b', 'two'));
    }

    public function testAddQueryEscapesValuesAndIgnoresEmptyKeyOrValue(): void
    {
        $maker = $this->maker();

        $this->assertSame('/path?q=a%26amp%3Bb', $maker->addQuery('/path', 'q', 'a&b'));
        $this->assertSame('/path', $maker->addQuery('/path', '', 'value'));
        $this->assertSame('/path', $maker->addQuery('/path', 'key', ''));
    }

    public function testDefaultExtensionIsTrimmedThroughFacadeConfig(): void
    {
        $maker = $this->maker([
            'DEFAULT_EXT' => ' .json ',
        ]);

        $this->assertSame('json', $maker->getDefaultExtension());
    }

    public function testReduceExtRemovesMatchingTrailingExtensionOnly(): void
    {
        $maker = $this->maker();

        $this->assertSame('/path/file', $maker->reduceExt('/path/file.html'));
        $this->assertSame('/path/file.longext', $maker->reduceExt('/path/file.longext'));
        $this->assertSame('/path/file', $maker->reduceExt('/path/file.longext', 2, 7));
    }

    public function testIsUseHttpsReadsConfigAsArrayAccess(): void
    {
        $this->assertTrue($this->maker(['USE_HTTPS' => true])->isUseHttps());
        $this->assertFalse($this->maker(['USE_HTTPS' => false])->isUseHttps());
    }

    public function testGetUriBuildsAbsoluteUrlFromInjectedServerInputWhenProtocolChanges(): void
    {
        $maker = $this->maker([
            'USE_HTTPS' => true,
        ], new ServiceTabDelegateUrlMakerInputDouble([
            'HTTPS' => 'on',
            'HTTP_HOST' => 'example.test',
        ]));

        $this->assertSame('http://example.test/secure', $maker->getURI('/secure', 'plain', false, false));
    }

    public function testCurrentUriArrayOptionsUseInjectedArrayValueReader(): void
    {
        $arrayValueReaderCalls = [];
        $maker = $this->maker(
            request: new ServiceTabDelegateUrlMakerRequestDouble(['catalog', 'item']),
            matcher: new ServiceTabDelegateUrlMakerMatcherDouble(),
            sessionFactory: static fn(): object => new ServiceTabDelegateUrlMakerSessionDouble(),
            arrayValueReader: static function (array|\ArrayAccess $array, mixed $key, mixed $default = null) use (&$arrayValueReaderCalls): mixed {
                $arrayValueReaderCalls[] = [$key, $default];

                return $array[$key] ?? $default;
            }
        );

        $this->assertSame('/catalog/item', $maker->getCurrentURI([
            'correct_language' => false,
            'add_extension' => false,
            'add_query_string' => false,
            'add_session_id' => false,
        ]));
        $this->assertSame([
            ['correct_language', true],
            ['add_extension', true],
            ['add_query_string', true],
            ['add_session_id', null],
            ['query_separator', null],
        ], $arrayValueReaderCalls);
    }

    public function testSourceUsesInjectedSessionFactoryDirectly(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('($this->sessionFactory)();', $source);
        $this->assertStringNotContainsString('call_user_func($this->sessionFactory', $source);
        $this->assertStringContainsString('private function arrayValueReader(): callable', $source);
        $this->assertStringNotContainsString('array_val(', $source);
    }

    private function maker(
        array $config = [],
        ?ServiceTabDelegateUrlMakerInputDouble $input = null,
        ?object $request = null,
        ?object $matcher = null,
        ?callable $sessionFactory = null,
        ?callable $arrayValueReader = null
    ): ServiceTabDelegateUrlMakerProbe
    {
        $configDouble = new ServiceTabDelegateUrlMakerConfigDouble($config);
        $maker = new ServiceTabDelegateUrlMakerProbe(
            matcher: $matcher,
            request: $request,
            locale: new ServiceTabDelegateUrlMakerLocaleDouble(),
            sessionFactory: $sessionFactory,
            input: $input ?? new ServiceTabDelegateUrlMakerInputDouble(),
            arrayValueReader: $arrayValueReader ?? static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default
        );
        $maker->setFacade(new ServiceTabDelegateUrlMakerFacadeDouble($configDouble));

        return $maker;
    }
}

final class ServiceTabDelegateUrlMakerProbe extends urlMaker
{
    public function __construct(
        ?object $matcher = null,
        ?object $request = null,
        ?object $locale = null,
        ?callable $sessionFactory = null,
        ?object $input = null,
        ?callable $arrayValueReader = null
    )
    {
        parent::__construct($matcher, $request, $locale, $sessionFactory, $input, $arrayValueReader);
    }
}

final class ServiceTabDelegateUrlMakerInputDouble
{
    public function __construct(private array $server = [])
    {
    }

    public function serverValue(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }
}

final class ServiceTabDelegateUrlMakerLocaleDouble
{
    public function isEnabled(): bool
    {
        return false;
    }

    public function getLanguage(): string
    {
        return '';
    }

    public function isUriParsing(): bool
    {
        return false;
    }
}

final class ServiceTabDelegateUrlMakerRequestDouble
{
    public function __construct(private array $baseRequest = [])
    {
    }

    public function getAll(string $source, mixed $default = null, bool $usePrefix = true): mixed
    {
        return $source === 'B' ? $this->baseRequest : $default;
    }

    public function getQueryString(bool $includeGet = true, bool $includeAdd = true, string $separator = '&amp;'): string
    {
        return '';
    }
}

final class ServiceTabDelegateUrlMakerMatcherDouble
{
    public function getCurrentItem(): object
    {
        return (object)[
            'parsed' => (object)[
                'src_path' => '',
                'app_prefix' => '',
            ],
        ];
    }
}

final class ServiceTabDelegateUrlMakerSessionDouble
{
    public function isByCookies(): bool
    {
        return true;
    }
}

final class ServiceTabDelegateUrlMakerFacadeDouble extends service
{
    public function __construct(private ServiceTabDelegateUrlMakerConfigDouble $configDouble)
    {
    }

    public function isSingleton(): bool
    {
        return false;
    }

    public function getConfig($key = null, $default = null): mixed
    {
        if ($key === null) {
            return $this->configDouble;
        }

        return $this->configDouble->get($key, $default);
    }
}

final class ServiceTabDelegateUrlMakerConfigDouble implements ArrayAccess
{
    public function __construct(private array $data = [])
    {
    }

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->data : ($this->data[$key] ?? $default);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->data[] = $value;
            return;
        }

        $this->data[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }
}
