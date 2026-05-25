<?php

declare(strict_types=1);

use FanTest\_core\base\meta\TestMetaBlock;
use FanTest\_core\base\meta\TestMetaMaker;
use fan\core\base\meta\maker_state;
use fan\core\base\meta\delayed as meta_delayed;
use fan\core\base\meta\maker;
use fan\core\block\base;
use fan\project\base\meta\delayed;
use fan\project\base\meta\row;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../../mock/_core/base/meta/MetaDoubles.php';
require_once dirname(__DIR__, 4) . '/_core/block/base.php';

class MetaMakerTest extends TestCase
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

    private function makerWithoutHelperClosures(): MetaMakerBareMaker
    {
        return new MetaMakerBareMaker(
            new MetaMakerBareBlock('content'),
            new class {
                public function getParentPaths(object $block): array
                {
                    return [
                        get_class($block) => __FILE__,
                    ];
                }
            },
            new maker_state(),
            null,
            null,
            null,
            null,
            new class {
                public function exists(string $path): bool
                {
                    return false;
                }
            }
        );
    }

    public function testSourceUsesInjectedReflectorInsteadOfBlockServiceLocator(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/_core/base/meta/maker.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('object $reflector', $source);
        $this->assertStringContainsString('$reflector->getParentPaths($this->block)', $source);
        $this->assertStringContainsString('private $phpArrayFileLoader = null;', $source);
        $this->assertStringContainsString('private $rowFactory = null;', $source);
        $this->assertStringContainsString('private $delayedFactory = null;', $source);
        $this->assertStringContainsString('private $blockExceptionFactory = null;', $source);
        $this->assertStringContainsString('private ?object $fileStorage = null;', $source);
        $this->assertStringContainsString('private \Closure $recursiveMerger;', $source);
        $this->assertStringContainsString('private \Closure $arrayAdducer;', $source);
        $this->assertStringContainsString('private \Closure $classNameResolver;', $source);
        $this->assertStringContainsString('$this->recursiveMerger = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->arrayAdducer = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->classNameResolver = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('if (!isset($this->recursiveMerger)) {', $source);
        $this->assertStringContainsString('if (!isset($this->arrayAdducer)) {', $source);
        $this->assertStringContainsString('if (!isset($this->classNameResolver)) {', $source);
        $this->assertStringContainsString('private function loadPhpArrayFile(string $path, mixed $default = null): mixed', $source);
        $this->assertStringContainsString('private function fileStorage(): object', $source);
        $this->assertStringContainsString('private function recursiveMerger(): callable', $source);
        $this->assertStringContainsString('private function arrayAdducer(): callable', $source);
        $this->assertStringContainsString('private function className(object $object): string', $source);
        $this->assertStringContainsString('public function getRowFactory(): callable', $source);
        $this->assertStringContainsString('private function createDelayedMeta(object|string $object, string $method, mixed $arguments): delayed', $source);
        $this->assertStringContainsString('private function createBlockFatalException(string $message, int $code = E_USER_ERROR, ?\Exception $previous = null): \Throwable', $source);
        $this->assertStringContainsString('$this->rootRow = $this->createRow($data);', $source);
        $this->assertStringContainsString('$this->fileStorage()->exists($folderPath)', $source);
        $this->assertStringContainsString('$this->fileStorage()->exists($metaPath)', $source);
        $this->assertStringNotContainsString('getContainerService(', $source);
        $this->assertStringNotContainsString('containerService(', $source);
        $this->assertStringNotContainsString('php_array_file::load', $source);
        $this->assertStringNotContainsString('new \fan\project\base\meta\row', $source);
        $this->assertStringNotContainsString('new \fan\project\base\meta\delayed', $source);
        $this->assertStringNotContainsString('new \fan\project\exception\block\fatal', $source);
        $this->assertStringNotContainsString('array_merge_recursive_alt(', $source);
        $this->assertStringNotContainsString('adduceToArray(', $source);
        $this->assertStringNotContainsString('get_class_alt(', $source);
        $this->assertStringNotContainsString('private mixed $recursiveMerger = null;', $source);
        $this->assertStringNotContainsString('private mixed $arrayAdducer = null;', $source);
        $this->assertStringNotContainsString('private mixed $classNameResolver = null;', $source);
        $this->assertStringNotContainsString('$this->recursiveMerger = $recursiveMerger === null ? null : \Closure::fromCallable($recursiveMerger);', $source);
        $this->assertStringNotContainsString('$this->arrayAdducer = $arrayAdducer === null ? null : \Closure::fromCallable($arrayAdducer);', $source);
        $this->assertStringNotContainsString('$this->classNameResolver = $classNameResolver === null ? null : \Closure::fromCallable($classNameResolver);', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\bfile_exists\s*\(/',
            $source
        );
    }

    public function testMetaCacheIsStoredInInjectedStateInsteadOfStaticProperty(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/_core/base/meta/maker.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('protected maker_state $state;', $source);
        $this->assertStringContainsString('Meta maker state is not configured for meta maker.', $source);
        $this->assertStringContainsString('$this->state     = $state ?? throw new \RuntimeException', $source);
        $this->assertStringNotContainsString('new maker_state()', $source);
        $this->assertStringNotContainsString('protected static array $metaCache', $source);
        $this->assertStringNotContainsString('self::$metaCache', $source);
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

        $this->assertInstanceOf(row::class, $row);
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

    public function testMixSourceMetaUsesInjectedRecursiveMerger(): void
    {
        $calls = [];
        $maker = new TestMetaMaker(
            recursiveMerger: static function (mixed ...$values) use (&$calls): array {
                $calls[] = $values;

                return ['mergedBy' => 'injected'];
            }
        );

        $this->assertSame(['mergedBy' => 'injected'], $maker->getMixSrcMeta());
        $this->assertCount(1, $calls);
        $this->assertCount(4, $calls[0]);
    }

    public function testMissingRecursiveMergerFailsAtUseTime(): void
    {
        $maker = $this->makerWithoutHelperClosures();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Recursive merger is not configured for meta maker.');

        $maker->getMixSrcMeta();
    }

    public function testMakeActiveMetaSupportsDelayedAndImmediateEvaluation(): void
    {
        $maker = new TestMetaMaker();

        $delayed = $maker->exposeMakeActiveMeta('buildValue', ['left', 'right']);

        $this->assertInstanceOf(delayed::class, $delayed);
        $this->assertSame('left:right', $delayed->getValue());
        $this->assertSame('single', $maker->exposeMakeActiveMeta('buildValue', 'single', null, false));
    }

    public function testMakeActiveMetaUsesInjectedDelayedFactory(): void
    {
        $calls = [];
        $maker = new TestMetaMaker(
            delayedFactory: static function (object|string $object, string $method, mixed $arguments) use (&$calls): object {
                $calls[] = [$object, $method, $arguments];

                return new meta_delayed($object, $method, $arguments);
            }
        );

        $delayed = $maker->exposeMakeActiveMeta('buildValue', ['left', 'right']);

        $this->assertInstanceOf(meta_delayed::class, $delayed);
        $this->assertSame('left:right', $delayed->getValue());
        $this->assertCount(1, $calls);
        $this->assertSame($maker->getBlock(), $calls[0][0]);
        $this->assertSame('buildValue', $calls[0][1]);
        $this->assertSame(['left', 'right'], $calls[0][2]);
    }

    public function testMakeActiveMetaUsesInjectedArrayAdducer(): void
    {
        $calls = [];
        $maker = new TestMetaMaker(
            arrayAdducer: static function (mixed $value) use (&$calls): array {
                $calls[] = $value;

                return [$value, 'right'];
            }
        );

        $this->assertSame('left:right', $maker->exposeMakeActiveMeta('buildValue', 'left', null, false));
        $this->assertSame(['left'], $calls);
    }

    public function testMissingArrayAdducerFailsAtUseTime(): void
    {
        $maker = $this->makerWithoutHelperClosures();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Array adducer is not configured for meta maker.');

        $maker->exposeMakeActiveMeta('buildValue', 'left', null, false);
    }

    public function testUnknownSourceTypeUsesInjectedBlockExceptionFactory(): void
    {
        $calls = [];
        $maker = new TestMetaMaker(
            blockExceptionFactory: static function (
                string $exceptionClass,
                object $block,
                string $message,
                int $code,
                ?\Exception $previous = null
            ) use (&$calls): \Throwable {
                $calls[] = [$exceptionClass, $block, $message, $code, $previous];

                return new \RuntimeException($message, $code, $previous);
            }
        );

        try {
            $maker->replaceSource('unknown', []);
            $this->fail('Expected injected block exception factory to provide the thrown exception.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Unknown type "unknown" of source Meta-data', $exception->getMessage());
        }

        $this->assertCount(1, $calls);
        $this->assertSame('\fan\project\exception\block\fatal', $calls[0][0]);
        $this->assertSame($maker->getBlock(), $calls[0][1]);
        $this->assertSame('Unknown type "unknown" of source Meta-data', $calls[0][2]);
        $this->assertSame(E_USER_ERROR, $calls[0][3]);
        $this->assertNull($calls[0][4]);
    }

    public function testMissingBlockMetaFileLoadsAsEmptyArray(): void
    {
        $maker = new TestMetaMaker();

        $this->assertSame(
            [],
            $maker->exposeLoadBlockSource('MissingMeta_' . uniqid('', true), sys_get_temp_dir() . '/missing-block.php')
        );
    }

    public function testBlockMetaSourceUsesInjectedStateCache(): void
    {
        $dir = sys_get_temp_dir() . '/fan-meta-cache-' . uniqid('', true);
        mkdir($dir);
        $blockPath = $dir . '/Cached.php';
        $metaPath = $dir . '/Cached.meta.php';
        file_put_contents($metaPath, '<?php return ["own" => ["version" => 1]];');
        $state = new maker_state();
        $firstMaker = new TestMetaMaker(state: $state);
        $secondMaker = new TestMetaMaker(state: $state);

        try {
            $this->assertSame(['own' => ['version' => 1]], $firstMaker->exposeLoadBlockSource('CachedBlock', $blockPath));
            file_put_contents($metaPath, '<?php return ["own" => ["version" => 2]];');

            $this->assertSame(['own' => ['version' => 1]], $secondMaker->exposeLoadBlockSource('CachedBlock', $blockPath));
            $this->assertSame([
                'CachedBlock' => ['own' => ['version' => 1]],
            ], $state->blockSources());
        } finally {
            unlink($metaPath);
            rmdir($dir);
        }
    }

    public function testBlockMetaSourceUsesInjectedPhpArrayLoader(): void
    {
        $dir = sys_get_temp_dir() . '/fan-meta-loader-' . uniqid('', true);
        mkdir($dir);
        $blockPath = $dir . '/Loaded.php';
        $metaPath = $dir . '/Loaded.meta.php';
        file_put_contents($metaPath, '<?php return ["ignored" => true];');
        $loadedPhpArrayFiles = [];
        $maker = new TestMetaMaker(
            phpArrayFileLoader: static function (string $path, mixed $default = null) use (&$loadedPhpArrayFiles): mixed {
                $loadedPhpArrayFiles[] = [$path, $default];

                return ['own' => ['fromLoader' => true]];
            }
        );

        try {
            $this->assertSame(['own' => ['fromLoader' => true]], $maker->exposeLoadBlockSource('LoadedBlock', $blockPath));
            $this->assertSame([
                [$metaPath, null],
            ], $loadedPhpArrayFiles);
        } finally {
            unlink($metaPath);
            rmdir($dir);
        }
    }

    public function testBlockMetaSourceUsesInjectedFileStorageBeforeLoading(): void
    {
        $loadedPhpArrayFiles = [];
        $checkedPaths = [];
        $maker = new TestMetaMaker(
            phpArrayFileLoader: static function (string $path, mixed $default = null) use (&$loadedPhpArrayFiles): mixed {
                $loadedPhpArrayFiles[] = [$path, $default];

                return ['own' => ['fromStorage' => true]];
            },
            fileStorage: new class ($checkedPaths) {
                public function __construct(private array &$checkedPaths)
                {
                }

                public function exists(string $path): bool
                {
                    $this->checkedPaths[] = $path;

                    return true;
                }
            }
        );

        $blockPath = sys_get_temp_dir() . '/fan-meta-storage/Stored.php';
        $metaPath = sys_get_temp_dir() . '/fan-meta-storage/Stored.meta.php';

        $this->assertSame(['own' => ['fromStorage' => true]], $maker->exposeLoadBlockSource('StoredBlock', $blockPath));
        $this->assertSame([$metaPath], $checkedPaths);
        $this->assertSame([[$metaPath, null]], $loadedPhpArrayFiles);
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
        $maker = new TestMetaMaker(
            classNameResolver: static fn(object $object): string => 'InjectedBlockName'
        );

        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage('Get Undefined Order of Meta-data "missing" in block "content", class "InjectedBlockName".');

        $maker->getOrder('missing');
    }

    public function testMissingClassNameResolverFailsAtUseTime(): void
    {
        $maker = $this->makerWithoutHelperClosures();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Class name resolver is not configured for meta maker.');

        $maker->getOrder('missing');
    }
}

final class MetaMakerBareMaker extends maker
{
    public function exposeMakeActiveMeta(string $method, $arguments = [], string|object|null $obj = null, bool $delayed = true): mixed
    {
        return $this->_makeActiveMeta($method, $arguments, $obj, $delayed);
    }
}

final class MetaMakerBareBlock extends base
{
    public function __construct(string $blockName)
    {
        $this->blockName = $blockName;
    }
}
