<?php

declare(strict_types=1);

use FanTest\_core\base\meta\TestMetaBlock;
use FanTest\_core\base\meta\TestMetaMaker;

require_once __DIR__ . '/../../../mock/_core/base/meta/MetaDoubles.php';

class MetaMakerTest extends \PHPUnit\Framework\TestCase
{
    private array $errors = [];
    private bool $capturingErrors = false;

    protected function tearDown(): void
    {
        if ($this->capturingErrors) {
            restore_error_handler();
        }
        $this->capturingErrors = false;
        $this->errors = [];
    }

    public function handleError($type, $message): bool
    {
        $this->errors[] = [$type, $message];
        return true;
    }

    private function captureErrors(): void
    {
        $this->errors = [];
        set_error_handler([$this, 'handleError']);
        $this->capturingErrors = true;
    }

    private function makerWithSources(): TestMetaMaker
    {
        $maker = new TestMetaMaker(new TestMetaBlock('content'));
        $maker->replaceSource('folder', [
            'common' => ['order' => ['folderCommon' => true], 'winner' => 'folderCommon'],
            'own' => ['order' => ['folderOwn' => true], 'winner' => 'folderOwn'],
            'content' => ['folderSpecific' => true],
            'sidebar' => ['folderSidebar' => true],
        ]);
        $maker->replaceSource('parent', [
            'common' => ['order' => ['parentCommon' => true], 'parentOnly' => true],
            'own' => ['order' => ['parentOwn' => true]],
            'sidebar' => ['parentSidebar' => true],
        ]);
        $maker->replaceSource('block', [
            'common' => ['order' => ['blockCommon' => true], 'blockCommon' => true],
            'own' => ['order' => ['blockOwn' => true], 'blockOnly' => true],
            'content' => ['currentBlockShouldBeExcludedFromOther' => true],
            'sidebar' => ['blockSidebar' => true],
        ]);
        $maker->replaceSource('container', [
            'common' => ['order' => ['containerCommon' => true], 'containerCommon' => true],
            'own' => ['order' => ['containerOwn' => true], 'containerOwn' => true],
        ]);

        return $maker;
    }

    public function testAssembleTabMergesConfiguredSourcesInTabOrder(): void
    {
        $data = $this->makerWithSources()->assembleTab();

        $this->assertSame([
            'folderCommon' => true,
            'parentCommon' => true,
            'blockCommon' => true,
            'parentOwn' => true,
            'folderOwn' => true,
            'blockOwn' => true,
        ], $data['order']);
        $this->assertSame('folderOwn', $data['winner']);
        $this->assertTrue($data['parentOnly']);
        $this->assertTrue($data['blockOnly']);
        $this->assertTrue($data['folderSpecific']);
    }

    public function testAssembleBlockBuildsRootRowAndLetsMakerReadAndWriteIt(): void
    {
        $maker = $this->makerWithSources();
        $maker->getBlock()->getTab()->mainMeta['content'] = [
            'mainOnly' => true,
            'order' => ['mainBlock' => true],
        ];
        $maker->setMainBlockMeta();

        $row = $maker->assembleBlock();

        $this->assertInstanceOf(\fan\project\base\meta\row::class, $row);
        $this->assertSame([
            'folderCommon' => true,
            'parentCommon' => true,
            'blockCommon' => true,
            'containerCommon' => true,
            'parentOwn' => true,
            'folderOwn' => true,
            'blockOwn' => true,
            'containerOwn' => true,
            'mainBlock' => true,
        ], $row->get('order')->toArray());
        $this->assertTrue($maker->getMeta('mainOnly'));
        $this->assertSame($maker, $maker->setMeta(['runtime', 'flag'], 'set'));
        $this->assertSame('set', $maker->getMeta(['runtime', 'flag']));
    }

    public function testAssembleOtherAndEmbeddedExcludeCurrentBlockAndMergeNamedBlocks(): void
    {
        $maker = $this->makerWithSources();

        $this->assertSame([
            'sidebar' => [
                'folderSidebar' => true,
                'parentSidebar' => true,
                'blockSidebar' => true,
            ],
        ], $maker->assembleOther());

        $this->assertSame([
            'parentSidebar' => true,
            'blockSidebar' => true,
        ], $maker->assembleEmbeded('sidebar'));
    }

    public function testMixSourceMetaCollectsFolderParentBlockAndContainerCommonData(): void
    {
        $mix = $this->makerWithSources()->getMixSrcMeta();

        $this->assertSame([
            'folderCommon' => true,
            'parentCommon' => true,
            'blockCommon' => true,
            'containerCommon' => true,
        ], $mix['common']['order']);
        $this->assertSame(['parentSidebar' => true, 'blockSidebar' => true], $mix['sidebar']);
        $this->assertSame(['currentBlockShouldBeExcludedFromOther' => true], $mix['content']);
    }

    public function testMakeActiveMetaSupportsDelayedAndImmediateEvaluation(): void
    {
        $maker = new TestMetaMaker();

        $delayed = $maker->exposeMakeActiveMeta('buildValue', ['left', 'right']);

        $this->assertInstanceOf(\fan\project\base\meta\delayed::class, $delayed);
        $this->assertSame('left:right', $delayed->getValue());
        $this->assertSame('single', $maker->exposeMakeActiveMeta('buildValue', 'single', null, false));
    }

    public function testMissingBlockMetaFileLoadsAsEmptyArray(): void
    {
        $maker = new TestMetaMaker();

        $this->assertSame(
            [],
            $maker->exposeLoadBlockSource('MissingMeta_' . uniqid('', true), sys_get_temp_dir() . '/missing-block.php')
        );
    }

    public function testBlockMetaFileMustReturnArray(): void
    {
        $dir = sys_get_temp_dir() . '/fan-meta-' . uniqid('', true);
        mkdir($dir);
        $blockPath = $dir . '/Invalid.php';
        $metaPath = $dir . '/Invalid.meta.php';
        file_put_contents($metaPath, '<?php return null;');

        $maker = new TestMetaMaker();

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('must return an array');

        try {
            $maker->exposeLoadBlockSource('InvalidMeta_' . uniqid('', true), $blockPath);
        } finally {
            unlink($metaPath);
            rmdir($dir);
        }
    }

    public function testUnknownOrderKeyThrowsException(): void
    {
        $maker = new TestMetaMaker();

        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('Get Undefined Order of Meta-data "missing"');

        $maker->getOrder('missing');
    }
}
