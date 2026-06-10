<?php

declare(strict_types=1);

use fan\core\service\matcher\item;
use fan\core\service\matcher\item\source;
use FanTest\core\SourceFileContractTestCase;

class ServiceMatcherItemSourceTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/matcher/item/source.php';

    public function testSourceCastsHttpRequestToHostAndRequest(): void
    {
        $source = new source($this->item());

        $source['host'] = 'example.test';
        $source['request'] = '/catalog?page=1';

        $this->assertSame('example.test', $source->host);
        $this->assertSame('/catalog?page=1', $source['request']);
        $this->assertSame('example.test/catalog?page=1', (string)$source);
    }

    public function testSourceCastsCliRequestToPathAndFileWhenRequestIsEmpty(): void
    {
        $source = new source($this->item());

        $source->path = '/var/www/app';
        $source->file = 'cli.php';

        $this->assertSame('/var/www/app/cli.php', (string)$source);
        $this->assertSame([
            'request' => null,
            'host' => null,
            'file' => 'cli.php',
            'path' => '/var/www/app',
        ], $source->toArray());
    }

    public function testSourcePartsAreImmutableAfterTheyAreSet(): void
    {
        $source = new source($this->item());
        $source->request = '/first';

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Error. Try to change existing property.');

        $source->request = '/second';
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
