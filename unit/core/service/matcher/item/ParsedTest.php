<?php

declare(strict_types=1);

use fan\core\service\matcher\item;
use fan\core\service\matcher\item\parsed;
use FanTest\core\SourceFileContractTestCase;

class ServiceMatcherItemParsedTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/matcher/item/parsed.php';

    public function testParsedBuildsRequestsLazilyThroughOwningItem(): void
    {
        $item = new ServiceMatcherParsedItemDouble();
        $parsed = new parsed($item);
        $item->parsed = $parsed;

        $this->assertSame(['catalog'], $parsed->main_request);
        $this->assertSame(['42'], $parsed->add_request);
        $this->assertSame(1, $item->parseCalls);

        $this->assertSame(['catalog', '42'], $parsed->both_request);
        $this->assertSame(1, $item->parseCalls);
    }

    public function testParsedBuildsUrnFromLanguagePrefixAndBothRequest(): void
    {
        $parsed = new parsed($this->item());
        $parsed->language = 'en';
        $parsed->app_prefix = '/admin/';
        $parsed->main_request = ['users'];
        $parsed->add_request = ['edit', '42'];

        $this->assertSame(['users', 'edit', '42'], $parsed->both_request);
        $this->assertSame('/en/admin/users/edit/42', $parsed->urn);
        $this->assertSame('/en/admin/users/edit/42', (string)$parsed);
    }

    public function testParsedClassIsEmptyWhenMainRequestIsEmpty(): void
    {
        $parsed = new parsed($this->item());
        $parsed->main_request = [];

        $this->assertSame('', $parsed->class);
    }

    public function testParsedFileUsesOwningItemMainBlockPath(): void
    {
        $item = new ServiceMatcherParsedItemDouble();
        $parsed = new parsed($item);
        $parsed->main_request = ['catalog', 'show'];

        $this->assertSame('/project/app/main/catalog/show.php', $parsed->file);
    }

    public function testSourceNoLongerUsesFacadeAsServiceLocator(): void
    {
        $this->assertStringNotContainsString('getContainerService(', $this->sourceCode());
    }

    public function testParsedPartsAreImmutableAfterTheyAreSet(): void
    {
        $parsed = new parsed($this->item());
        $parsed->app_name = 'frontend';

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Error. Try to change existing property.');

        $parsed->app_name = 'admin';
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

final class ServiceMatcherParsedItemDouble extends item
{
    public ?parsed $parsed = null;

    public int $parseCalls = 0;

    public function __construct()
    {
    }

    public function parseRequest(): static
    {
        $this->parseCalls++;
        $this->parsed['main_request'] = ['catalog'];
        $this->parsed['add_request'] = ['42'];

        return $this;
    }

    public function getMainBlockBasePath(): string
    {
        return '/project/app/main';
    }
}
