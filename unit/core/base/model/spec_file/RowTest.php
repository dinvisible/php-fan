<?php

declare(strict_types=1);

use fan\core\base\model\file_data\row as FileDataRow;
use fan\core\base\model\spec_file\row;
use FanTest\core\SourceFileContractTestCase;

class BaseModelSpecFileRowTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/base/model/spec_file/row.php';

    public function testAccessMethodsDelegateToEntityFile(): void
    {
        $file = new BaseModelSpecFileRowFileDataDouble();
        $row = new BaseModelSpecFileRowProbe($file);

        $row->setAccessType('public', false);
        $row->setPersonalAccess('owner', '2026-06-01', 2, 77);
        $row->removePersonalAccess(3, 77);

        $this->assertSame('source.jpg', $row->get_src_name());
        $this->assertTrue($row->checkAccess());
        $this->assertFalse($row->checkIsOwner());
        $this->assertSame([
            ['setAccessType', 'public', false],
            ['setPersonalAccess', 'owner', '2026-06-01', 2, 77],
            ['removePersonalAccess', 3, 77],
        ], $file->calls);
    }

    public function testSourceUsesInjectedNamespaceResolverBoundary(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('$ns = $this->namespaceName($this, 2);', $source);
        $this->assertStringNotContainsString('get_ns_name(', $source);
    }
}

final class BaseModelSpecFileRowProbe extends row
{
    public function __construct(FileDataRow $entityFile)
    {
        $this->entityFile = $entityFile;
    }
}

final class BaseModelSpecFileRowFileDataDouble extends FileDataRow
{
    public array $calls = [];

    public function __construct()
    {
    }

    public function get_src_name(mixed $defaultVal = null, bool $allowException = true): mixed
    {
        return 'source.jpg';
    }

    public function setAccessType(string $key, bool $save = true): void
    {
        $this->calls[] = ['setAccessType', $key, $save];
    }

    public function setPersonalAccess(string $membType = 'owner', ?string $expireDate = null, int|float $accessQtt = -1, int|float $membId = 0): void
    {
        $this->calls[] = ['setPersonalAccess', $membType, $expireDate, $accessQtt, $membId];
    }

    public function removePersonalAccess(int $removeType = 1, ?int $membId = null): void
    {
        $this->calls[] = ['removePersonalAccess', $removeType, $membId];
    }

    public function checkAccess(): bool
    {
        return true;
    }

    public function checkIsOwner(): bool
    {
        return false;
    }
}
