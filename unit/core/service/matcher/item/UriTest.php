<?php

declare(strict_types=1);

use fan\core\service\matcher\item;
use fan\core\service\matcher\item\uri;
use FanTest\core\SourceFileContractTestCase;

class ServiceMatcherItemUriTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/matcher/item/uri.php';

    public function testUriStoresAllowedPartsAndCastsToFullUri(): void
    {
        $uri = new uri($this->item());

        $uri['scheme'] = 'https';
        $uri['host'] = 'example.test';
        $uri['path'] = '/docs';
        $uri['query'] = 'page=1';
        $uri['full'] = 'https://example.test/docs?page=1';

        $this->assertSame('https', $uri->scheme);
        $this->assertSame('example.test', $uri['host']);
        $this->assertSame('/docs', $uri->get('path'));
        $this->assertTrue(isset($uri['query']));
        $this->assertSame('https://example.test/docs?page=1', (string)$uri);
    }

    public function testUriExportsAllKnownPartsInStableOrder(): void
    {
        $uri = new uri($this->item());
        $uri->scheme = 'http';
        $uri->host = 'example.test';
        $uri->path = '/index';
        $uri->full = 'http://example.test/index';

        $this->assertSame([
            'scheme' => 'http',
            'host' => 'example.test',
            'user' => null,
            'pass' => null,
            'path' => '/index',
            'query' => null,
            'fragment' => null,
            'full' => 'http://example.test/index',
        ], $uri->toArray());

        $this->assertSame(
            ['http', 'example.test', null, null, '/index', null, null, 'http://example.test/index'],
            iterator_to_array($uri, false)
        );
    }

    public function testUriPartsAreImmutableAfterTheyAreSet(): void
    {
        $uri = new uri($this->item());
        $uri->host = 'example.test';

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Error. Try to change existing property.');

        $uri->host = 'other.test';
    }

    private function item(): item
    {
        return new class extends item {
            public function __construct()
            {
            }
        };
    }
}
