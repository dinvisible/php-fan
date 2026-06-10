<?php

declare(strict_types=1);

use fan\core\block\admin\data_info;
use FanTest\core\SourceFileContractTestCase;

class BlockAdminDataInfoTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/block/admin/data_info.php';

    public function testGetExtraDataAddsParsingScriptWhenConfigured(): void
    {
        $block = new BlockAdminDataInfoProbe([
            'tagId' => 'details',
            'parsingScript' => 'parseDetails',
        ]);
        $block->setBlockDependencies([
            'recursiveMerger' => static fn(mixed ...$values): array => array_replace_recursive(...$values),
        ]);

        $this->assertSame([
            'tagId' => 'cont_details',
            'parsingScript' => 'parseDetails',
        ], $block->getExtraData());
    }
}

final class BlockAdminDataInfoProbe extends data_info
{
    public function __construct(private array $metaData = [])
    {
    }

    public function getMeta(string|array|null $key = null, mixed $default = null, bool $convToArray = false): mixed
    {
        return is_string($key) && array_key_exists($key, $this->metaData) ? $this->metaData[$key] : $default;
    }
}
