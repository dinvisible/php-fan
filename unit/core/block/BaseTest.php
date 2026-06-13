<?php

declare(strict_types=1);

use FanTest\core\block\DelegateTarget;
use FanTest\core\block\FakeLocale;
use FanTest\core\block\FakeReflector;
use FanTest\core\block\FakeRequest;
use FanTest\core\block\FakeRoleRegistry;
use FanTest\core\block\FakeRootBlock;
use FanTest\core\block\FakeServiceContainer;
use FanTest\core\block\FakeServiceRegistry;
use FanTest\core\block\FakeTab;
use FanTest\core\block\FakeViewClass;
use FanTest\core\block\FakeViewRouter;
use FanTest\core\block\ObjectWithToArray;
use FanTest\core\block\TestableBaseBlock;
use FanTest\core\block\FakeMetaMaker;
use fan\core\base\meta\delayed;
use fan\core\base\meta\maker;
use fan\core\base\meta\maker_state;
use fan\core\base\meta\row as meta_row;
use fan\core\block\base;
use fan\project\base\meta\row;
use fan\project\exception\block\fatal;
use fan\project\exception\block\local;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../mock/core/block/GlobalFunctions.php';
require_once __DIR__ . '/../../mock/core/block/FrameworkStubs.php';
require_once __DIR__ . '/../../mock/core/block/FakeServices.php';
require_once __DIR__ . '/../../../core/base/meta/maker_state.php';
require_once __DIR__ . '/../../../core/block/base.php';
require_once __DIR__ . '/../../mock/core/block/TestableBaseBlock.php';

final class BaseMetaMakerFactoryProbeBlock extends base
{
    protected function _transferor(): void
    {
    }
}

class BaseTest extends TestCase
{
    private array $errors = [];
    private bool $capturingErrors = false;

    protected function setUp(): void
    {
        FakeServiceRegistry::reset();
        FakeServiceRegistry::installContainer(new FakeServiceContainer());
        TestableBaseBlock::useMeta([]);
        FakeViewClass::$lastFormatExceptionFactory = null;
        $this->errors = [];
    }

    protected function tearDown(): void
    {
        if ($this->capturingErrors) {
            restore_error_handler();
        }
        $this->errors = [];
        $this->capturingErrors = false;
    }

    public function handleError($type, $message, $fileName = null, $lineNum = null, $errContext = null): bool
    {
        $this->errors[] = [$type, $message];
        return true;
    }

    private function captureErrors(): void
    {
        if ($this->capturingErrors) {
            restore_error_handler();
        }
        $this->errors = [];
        set_error_handler([$this, 'handleError']);
        $this->capturingErrors = true;
    }

    /**
     * @param mixed $file File path or file descriptor handled by the operation.
     */
    private function fixturePath($file): string
    {
        return __DIR__ . '/../../mock/core/block/' . $file;
    }

    private function makeMetaRow(TestableBaseBlock $block, array $data): object
    {
        $maker = $block->getMetaMaker();
        $class = class_exists('\fan\project\base\meta\row', false)
            ? row::class
            : meta_row::class;
        $constructor = new \ReflectionMethod($class, '__construct');
        $firstParameter = $constructor->getParameters()[0] ?? null;
        $firstType = $firstParameter?->getType();

        return $firstType instanceof \ReflectionNamedType && $firstType->getName() === maker::class
            ? new $class($maker, $data)
            : new $class($data);
    }

    public function testConstructorWiresCoreDependenciesWithoutFullConstruction(): void
    {
        $tab = new FakeTab();
        $request = new FakeRequest();
        FakeServiceRegistry::set('request', $request);
        TestableBaseBlock::useMeta(['alpha' => 'beta']);

        $block = new TestableBaseBlock('content', $tab, null, [], false);

        $this->assertSame('content', $block->getBlockName());
        $this->assertSame($tab, $block->getTab());
        $this->assertSame($request, $block->getRequest());
        $this->assertSame($block, $tab->currentBlock);
        $this->assertCount(1, $tab->currentBlockCalls);
        $this->assertSame('beta', $block->getMeta('alpha'));
        $this->assertSame(1, $block->transferorCalls);
        $this->assertInstanceOf('FanTest\core\block\FakeMetaMaker', $block->getMetaMaker());
    }

    public function testConstructorUsesInjectedMetaMakerFactory(): void
    {
        $tab = new FakeTab();
        $request = new FakeRequest();
        $metaFileStorage = new class {
            public function exists(string $path): bool
            {
                return false;
            }
        };
        $calls = [];
        $blockExceptionFactory = static fn(
            string $exceptionClass,
            base $block,
            string $message,
            int $code,
            ?\Exception $previous = null
        ): \Throwable => new \RuntimeException($message, $code, $previous);

        $block = new BaseMetaMakerFactoryProbeBlock('content', $tab, null, [], false, null, [
            'tab' => $tab,
            'request' => $request,
            'reflectorFactory' => static fn(): object => new FakeReflector(),
            'metaMakerState' => new maker_state(),
            'metaMakerFactory' => static function (
                base $block,
                object $reflector,
                maker_state $state,
                callable $phpArrayFileLoader,
                callable $rowFactory,
                object $fileStorage,
                ?callable $blockExceptionFactory = null
            ) use (&$calls): object {
                $calls[] = [$block, $reflector, $state, $phpArrayFileLoader, $rowFactory, $fileStorage, $blockExceptionFactory];

                return new FakeMetaMaker($block, ['factory' => 'used']);
            },
            'phpArrayFileLoader' => static fn(string $path, mixed $default = null): mixed => $default,
            'metaRowFactory' => static fn(
                maker $maker,
                array $data,
                ?meta_row $parent = null,
                int|string|null $keyName = null,
                ?callable $rowFactory = null
            ): object => new row($maker, $data, $parent, $keyName, $rowFactory),
            'metaFileStorage' => $metaFileStorage,
            'blockExceptionFactory' => $blockExceptionFactory,
        ]);

        $this->assertSame('used', $block->getMeta('factory'));
        $this->assertCount(1, $calls);
        $this->assertSame($block, $calls[0][0]);
        $this->assertInstanceOf(FakeReflector::class, $calls[0][1]);
        $this->assertInstanceOf(maker_state::class, $calls[0][2]);
        $this->assertIsCallable($calls[0][3]);
        $this->assertIsCallable($calls[0][4]);
        $this->assertSame($metaFileStorage, $calls[0][5]);
        $this->assertSame($blockExceptionFactory, $calls[0][6]);
    }

    public function testConstructorDoesNotSetCurrentBlockWhenBlockNameIsEmpty(): void
    {
        $tab = new FakeTab();

        new TestableBaseBlock('', $tab, null, [], false);

        $this->assertNull($tab->currentBlock);
        $this->assertSame([], $tab->currentBlockCalls);
    }

    public function testConstructorResolvesBlockDependenciesThroughServiceContainerResolver(): void
    {
        $request = new FakeRequest();
        FakeServiceRegistry::set('request', $request);

        $block = new TestableBaseBlock('content', null, null, [], false, new FakeServiceContainer());

        $this->assertInstanceOf(FakeTab::class, $block->getTab());
        $this->assertSame($request, $block->getRequest());
        $this->assertSame($block, $block->getTab()->currentBlock);
    }

    public function testFinishConstructCompletesHappyPathAndUsesContainerMeta(): void
    {
        $template = $this->fixturePath('ExplicitTemplate.tpl');
        $tab = new FakeTab();
        $tab->blockStatus = [true, false];
        $container = new TestableBaseBlock('container', $tab, null, [], false);
        TestableBaseBlock::useMeta(['template' => $template]);

        $block = new TestableBaseBlock('content', $tab, $container, ['own' => ['x' => 1]], true);

        $this->assertSame($container, $block->getContainer());
        $this->assertSame(['own' => ['x' => 1]], $block->getMetaMaker()->containerMeta);
        $this->assertSame(1, $block->getMetaMaker()->setMainBlockMetaCalls);
        $this->assertSame(1, $block->getMetaMaker()->assembleBlockCalls);
        $this->assertSame($block, $tab->tabBlocks['content']);
        $this->assertSame($template, $block->getTemplate());
        $this->assertSame(1, $block->postCreateCalls);
        $this->assertInstanceOf('FanTest\core\block\FakeViewRouter', $block->getView());
    }

    public function testFinishConstructStopsAfterRoleFailure(): void
    {
        FakeRoleRegistry::set('admin', false);
        TestableBaseBlock::useMeta([
            'roles' => [
                ['condition' => 'admin', 'redirect' => '/login'],
            ],
        ]);
        $tab = new FakeTab();

        $block = new TestableBaseBlock('secure', $tab, null, [], true);

        $this->assertSame(['condition' => 'admin', 'redirect' => '/login'], $block->getRoleCondition());
        $this->assertFalse(isset($tab->tabBlocks['secure']));
        $this->assertNull($block->getTemplate());
        $this->assertSame(0, $block->postCreateCalls);
        $this->assertSame(['admin'], FakeRoleRegistry::$calls);
    }

    public function testMetaAccessMutationAndDeprecatedAliases(): void
    {
        TestableBaseBlock::useMeta([
            'plain'  => 'value',
            'nested' => ['key' => 'inside'],
        ]);
        $block = new TestableBaseBlock('meta', new FakeTab(), null, [], false);

        $this->assertSame('value', $block->getMeta('plain'));
        $this->assertSame('fallback', $block->getMeta('missing', 'fallback'));
        $this->assertSame(['key' => 'inside'], $block->getMeta('nested', null, true));

        $this->assertSame($block, $block->exposeSetMeta('plain', 'changed'));
        $this->assertSame('changed', $block->getMeta('plain'));
        $this->assertSame($block, $block->exposeAddMeta(['added' => 'yes']));
        $this->assertSame('yes', $block->getMeta('added'));

        $this->captureErrors();
        $this->assertSame('changed', $block->getMetaVar('plain'));
        $this->assertSame($block, $block->exposeSetMetaVar('legacy', 'ok'));
        $this->assertSame('ok', $block->getMeta('legacy'));
        $this->assertCount(2, $this->errors);
        $this->assertSame(E_USER_DEPRECATED, $this->errors[0][0]);
        $this->assertStringContainsString('getMeta', $this->errors[0][1]);
        $this->assertSame(E_USER_DEPRECATED, $this->errors[1][0]);
        $this->assertStringContainsString('setMeta', $this->errors[1][1]);
    }

    public function testDelayedAndDynamicMetaAreResolvedAndMergedOnce(): void
    {
        $block = new TestableBaseBlock('dyn', new FakeTab(), null, [], false);
        $resolver = new class {
            /**
             * @param mixed $value Value that should be applied or transformed.
             */
            public function value($value): mixed
            {
                return $value;
            }
        };
        $makeDelayed = function ($value) use ($resolver) {
            $class = delayed::class;
            $constructor = (new \ReflectionClass($class))->getConstructor();
            return $constructor && $constructor->getNumberOfParameters() >= 3
                ? new $class($resolver, 'value', $value)
                : new $class($value);
        };
        $metaData = [
            'first' => $makeDelayed('resolved'),
            'nested' => [
                'second' => $makeDelayed('deep'),
            ],
        ];
        $meta = $this->makeMetaRow($block, $metaData);
        $block->setMetaRowForTest($meta);
        $block->dynamicMeta = ['dynamic' => 'added'];

        $this->assertSame($block, $block->setDynamicMeta());
        $this->assertSame([
            'first' => 'resolved',
            'nested' => ['second' => 'deep'],
            'dynamic' => 'added',
        ], $meta->toArray());

        $block->setDynamicMeta();
        $this->assertSame(1, $block->dynamicMetaCalls);
    }

    public function testDelayedMetaAvailabilityCheckIsInjected(): void
    {
        $checkedClasses = [];
        $block = new TestableBaseBlock('dyn', new FakeTab(), null, [], false);
        $block->setBlockDependencies([
            'delayedMetaClassExists' => static function (string $className) use (&$checkedClasses): bool {
                $checkedClasses[] = $className;

                return false;
            },
        ]);
        $resolver = new class {
            public function value(): mixed
            {
                throw new RuntimeException('Delayed meta should not be resolved.');
            }
        };
        $delayed = new delayed($resolver, 'value', null);
        $meta = $this->makeMetaRow($block, ['first' => $delayed]);
        $block->setMetaRowForTest($meta);

        $this->assertSame($block, $block->setDynamicMeta());
        $this->assertSame(['\fan\core\base\meta\delayed'], $checkedClasses);
        $this->assertSame($delayed, $block->getMeta('first'));
    }

    public function testForcedDynamicMetaCanBeEnabledByMetaFlag(): void
    {
        TestableBaseBlock::useMeta(['force_dynamic_meta' => true]);
        $block = new TestableBaseBlock('dyn', new FakeTab(), null, [], true);
        $block->dynamicMeta = ['later' => 'ignored because construct already forced'];

        $this->assertSame(1, $block->dynamicMetaCalls);
    }

    public function testRoleConditionReturnsNullForEmptyOrAllowedRolesAndCachesDeniedRole(): void
    {
        TestableBaseBlock::useMeta([]);
        $noRoles = new TestableBaseBlock('open', new FakeTab(), null, [], false);
        $this->assertNull($noRoles->getRoleCondition());

        TestableBaseBlock::useMeta(['roles' => [['condition' => 'member']]]);
        FakeRoleRegistry::set('member', true);
        $allowed = new TestableBaseBlock('allowed', new FakeTab(), null, [], false);
        $this->assertNull($allowed->getRoleCondition());

        TestableBaseBlock::useMeta(['roles' => [['condition' => 'blocked', 'reason' => 'private']]]);
        FakeRoleRegistry::set('blocked', false);
        $denied = new TestableBaseBlock('denied', new FakeTab(), null, [], false);
        $this->assertSame(['condition' => 'blocked', 'reason' => 'private'], $denied->getRoleCondition());
        FakeRoleRegistry::set('blocked', true);
        $this->assertSame(['condition' => 'blocked', 'reason' => 'private'], $denied->getRoleCondition());
    }

    public function testViewMethodsDelegateToTabAndRouter(): void
    {
        $tab = new FakeTab();
        $tab->viewDefiner->parserName = 'jsonParser';
        FakeViewClass::$format = 'json';
        $view = new FakeViewRouter();
        $block = new TestableBaseBlock('view', $tab, null, [], false);
        $block->setViewForTest($view);

        $this->assertSame('jsonParser', $block->getViewParserName());
        $this->assertSame('json', $block->getViewFormat());
        $this->assertSame($block, $block->exposeSetViewVar('answer', 42));
        $this->assertSame(['answer' => 42], $block->getViewData());
        $this->assertSame(1, $block->preOutputCalls);
        $this->assertSame(1, $view->getAllCalls);
    }

    public function testViewFormatPassesInjectedParserExceptionFactory(): void
    {
        $tab = new FakeTab();
        $exceptionFactory = static fn(string $message): \Throwable => new RuntimeException($message);
        FakeViewClass::$format = 'html';
        $block = new TestableBaseBlock('view', $tab, null, [], false);
        $block->setBlockDependencies(['viewParserExceptionFactory' => $exceptionFactory]);

        $this->assertSame('html', $block->getViewFormat());
        $this->assertSame($exceptionFactory, FakeViewClass::$lastFormatExceptionFactory);
    }

    public function testTemplateSelectionValidationAndLocalizedSuffixes(): void
    {
        $locale = new FakeLocale();
        $locale->language = 'pl';
        FakeServiceRegistry::set('locale', $locale);
        TestableBaseBlock::useMeta(['useMultiLanguage' => true]);
        $block = new TestableBaseBlock('tpl', new FakeTab(), null, [], false);

        $this->assertSame(['_pl', ''], $block->exposeGetTplSuffixes());
        $this->assertTrue($block->exposeCheckTemplate($this->fixturePath('Any.php'), 'LocalizedTemplate.tpl', ['_pl', '']));
        $this->assertSame($this->fixturePath('LocalizedTemplate_pl.tpl'), $block->getTemplate());

        $this->assertTrue($block->setTemplate($this->fixturePath('ExplicitTemplate.tpl')));
        $this->assertFalse($block->setTemplate($this->fixturePath('missing.tpl'), false));

        $exceptionCalls = [];
        $block->setBlockDependencies([
            'blockExceptionFactory' => static function (
                string $exceptionClass,
                base $block,
                string $message,
                int $code,
                ?\Exception $previous = null
            ) use (&$exceptionCalls): \Throwable {
                $exceptionCalls[] = [$exceptionClass, $block, $message, $code, $previous];

                return new $exceptionClass($block, $message, $code, $previous);
            },
        ]);

        try {
            $block->setTemplate($this->fixturePath('missing.tpl'), true);
            $this->fail('Expected fatal block exception for an invalid template path.');
        } catch (fatal $exception) {
            $this->assertStringContainsString('Incorrect template path', $exception->getMessage());
        }

        $this->assertCount(1, $exceptionCalls);
        $this->assertSame('\fan\project\exception\block\fatal', $exceptionCalls[0][0]);
        $this->assertSame($block, $exceptionCalls[0][1]);
        $this->assertStringContainsString('Incorrect template path', $exceptionCalls[0][2]);
        $this->assertSame(E_USER_ERROR, $exceptionCalls[0][3]);
        $this->assertNull($exceptionCalls[0][4]);
    }

    public function testSetTemplateFindsTemplateByClassParentPath(): void
    {
        $reflector = new FakeReflector();
        $reflector->parentPaths = [
            'FanTest\core\block\TestableBaseBlock' => $this->fixturePath('TestableBaseBlock.php'),
        ];
        FakeServiceRegistry::set('reflector', $reflector);
        TestableBaseBlock::useMeta([]);
        $block = new TestableBaseBlock('tpl', new FakeTab(), null, [], false);

        $this->assertSame($block, $block->exposeSetTemplateInternal(''));
        $this->assertSame($this->fixturePath('TestableBaseBlock.tpl'), $block->getTemplate());
    }

    public function testSetTemplateUsesInjectedShortClassNameResolver(): void
    {
        $reflector = new FakeReflector();
        $reflector->parentPaths = [
            'FanTest\core\block\TestableBaseBlock' => $this->fixturePath('TestableBaseBlock.php'),
        ];
        FakeServiceRegistry::set('reflector', $reflector);
        TestableBaseBlock::useMeta([]);
        $seenClass = null;
        $block = new TestableBaseBlock('tpl', new FakeTab(), null, [], false);
        $block->setBlockDependencies([
            'shortClassNameResolver' => static function (object|string $class) use (&$seenClass): string {
                $seenClass = $class;

                return 'TestableBaseBlock';
            },
        ]);

        $this->assertSame($block, $block->exposeSetTemplateInternal(''));
        $this->assertSame('FanTest\core\block\TestableBaseBlock', $seenClass);
        $this->assertSame($this->fixturePath('TestableBaseBlock.tpl'), $block->getTemplate());
    }

    public function testTemplateServiceUsesInjectedTemplateFactory(): void
    {
        $calls = [];
        $template = new stdClass();
        $block = new TestableBaseBlock('tpl', new FakeTab(), null, [], false);
        $block->setBlockDependencies([
            'templateFactory' => static function (mixed ...$arguments) use (&$calls, $template): object {
                $calls[] = $arguments;

                return $template;
            },
        ]);

        $this->assertSame($template, $block->exposeTemplateService('/template.tpl', $block));
        $this->assertSame([['/template.tpl', $block]], $calls);
    }

    public function testRootParametersAndTemplateVariablesAreCopiedFromMeta(): void
    {
        $root = new FakeRootBlock('root', new FakeTab(), null, [], false);
        $view = new FakeViewRouter();
        $block = new TestableBaseBlock('content', new FakeTab(), null, [], false);
        $block->setViewForTest($view);
        $block->setMetaRowForTest($this->makeMetaRow($block, [
            'meta_tag'    => ['description'],
            'externalCss' => ['/theme.css'],
            'embedCss'    => ['body{}'],
            'externalJS'  => ['/app.js'],
            'embedJS'     => [
                'head' => ['var a = 1;', 'var b = 2;'],
                'tail' => 'console.log(1);',
            ],
        ]));

        $this->assertSame($block, $block->exposeSetRootBlockParameters($root));
        $this->assertSame([
            ['setMetaTag', 'description'],
            ['setExternalCss', ['/theme.css']],
            ['setEmbedCssByMeta', ['body{}']],
            ['setExternalJs', ['/app.js']],
            ['setEmbedJs', 'var a = 1;', 'head'],
            ['setEmbedJs', 'var b = 2;', 'head'],
            ['setEmbedJs', 'console.log(1);', 'tail'],
        ], $root->rootCalls);

        $this->assertSame($block, $block->exposeSetTplVarsByMeta([
            'plain' => 'value',
            'object' => new ObjectWithToArray(['x' => 1]),
        ]));
        $this->assertSame(['plain' => 'value', 'object' => ['x' => 1]], $view->data);
    }

    public function testPreparseMetaUpdatesRootAndTplVarsForHtmlView(): void
    {
        $tab = new FakeTab();
        $root = new FakeRootBlock('root', $tab, null, [], false);
        $tab->tabBlocks['root'] = $root;
        $view = new FakeViewRouter();
        TestableBaseBlock::useMeta([
            'tplVars' => ['title' => 'Hello'],
            'externalCss' => ['/screen.css'],
        ]);
        $block = new TestableBaseBlock('content', $tab, null, [], false);
        $block->setMetaRowForTest($block->getMeta());
        $block->setViewForTest($view);
        FakeViewClass::$format = 'html';

        $this->assertSame($block, $block->exposePreparseMeta());
        $this->assertSame(['title' => 'Hello'], $view->data);
        $this->assertSame([['setExternalCss', ['/screen.css']]], $root->rootCalls);
    }

    public function testEmbeddedBlocksCreateNewBlocksAndReuseMainPlaceholder(): void
    {
        $tab = new FakeTab();
        $main = new TestableBaseBlock('main', $tab, null, [], false);
        $tab->setMainBlock($main);
        $tab->loadBlockMap['project/child'] = 'FanTest\core\block\FakeEmbeddedBlock';
        TestableBaseBlock::useMeta(['template' => $this->fixturePath('ExplicitTemplate.tpl')]);
        $block = new TestableBaseBlock('parent', $tab, null, [], false);
        $block->setMetaRowForTest($this->makeMetaRow($block, [
            'embeddedBlocks' => [
                'child' => 'project/child',
                'mainSlot' => '{MAIN}',
                'nullSlot' => null,
            ],
        ]));

        $this->assertSame($block, $block->exposeSetEmbeddedBlocks());
        $embedded = $block->getEmbeddedBlocks();
        $this->assertInstanceOf('FanTest\core\block\FakeEmbeddedBlock', $embedded['child']);
        $this->assertSame($block, $embedded['child']->getContainer());
        $this->assertSame($main, $embedded['mainSlot']);
        $this->assertFalse(isset($embedded['nullSlot']));
        $this->assertSame($block, $tab->currentBlock);
    }

    public function testEmbeddedMainPlaceholderRequiresExistingMainBlock(): void
    {
        $block = new TestableBaseBlock('parent', new FakeTab(), null, [], false);
        $block->setMetaRowForTest($this->makeMetaRow($block, [
            'embeddedBlocks' => ['mainSlot' => '{MAIN}'],
        ]));

        try {
            $block->exposeSetEmbeddedBlocks();
            $this->fail('Expected fatal exception when main block placeholder has no tab main block.');
        } catch (fatal $exception) {
            $this->assertStringContainsString('Main Block', $exception->getMessage());
        }
    }

    public function testEmbeddedBlockLookupThrowsForUnknownKey(): void
    {
        $child = new TestableBaseBlock('child', new FakeTab(), null, [], false);
        $block = new TestableBaseBlock('parent', new FakeTab(), null, [], false);
        $block->setEmbeddedBlocksForTest(['child' => $child]);
        $exceptionCalls = [];
        $block->setBlockDependencies([
            'blockExceptionFactory' => static function (
                string $exceptionClass,
                base $block,
                string $message,
                int $code,
                ?\Exception $previous = null
            ) use (&$exceptionCalls): \Throwable {
                $exceptionCalls[] = [$exceptionClass, $block, $message, $code, $previous];

                return new $exceptionClass($block, $message, $code, $previous);
            },
        ]);

        $this->assertSame($child, $block->getEmbeddedBlock('child'));

        try {
            $block->getEmbeddedBlock('missing');
            $this->fail('Expected local block exception for unknown embedded block.');
        } catch (local $exception) {
            $this->assertStringContainsString('unknown Embedded Block', $exception->getMessage());
        }

        $this->assertCount(1, $exceptionCalls);
        $this->assertSame('\fan\project\exception\block\local', $exceptionCalls[0][0]);
        $this->assertSame($block, $exceptionCalls[0][1]);
        $this->assertSame('Call to unknown Embedded Block "missing"', $exceptionCalls[0][2]);
        $this->assertSame(E_USER_WARNING, $exceptionCalls[0][3]);
        $this->assertNull($exceptionCalls[0][4]);
    }

    public function testBlockLookupClassParsingAndBlockExceptions(): void
    {
        $tab = new FakeTab();
        $tab->loadBlockMap['known/path'] = 'FanTest\core\block\FakeEmbeddedBlock';
        $block = new TestableBaseBlock('content', $tab, null, [], false);

        $this->assertNull($block->exposeGetBlock('missing', false));
        $this->assertSame('FanTest\core\block\FakeEmbeddedBlock', $block->exposeParseClassName('known/path'));
        $this->assertNull($block->exposeParseClassName('missing/path', false));

        try {
            $block->exposeParseClassName('missing/path', true);
            $this->fail('Expected fatal exception for unknown embedded class path.');
        } catch (fatal $exception) {
            $this->assertStringContainsString('Unknown block path', $exception->getMessage());
        }

        try {
            $block->exposeMakeBlockException('local failed', 'local');
            $this->fail('Expected local block exception.');
        } catch (local $exception) {
            $this->assertSame('nothing', $block->getExceptionDbOper());
            $this->assertSame('local failed', $exception->getMessage());
        }

        try {
            $block->exposeMakeBlockException('unknown failed', 'unknown');
            $this->fail('Expected fatal fallback exception.');
        } catch (fatal $exception) {
            $this->assertSame('rollback', $block->getExceptionDbOper());
        }

        try {
            $block->exposeMakeBlockException('commit failed', 'fatal', 'commit');
            $this->fail('Expected fatal exception with explicit DB operation.');
        } catch (fatal $exception) {
            $this->assertSame('commit', $block->getExceptionDbOper());
        }
    }

    public function testBlockExceptionClassAvailabilityCheckIsInjected(): void
    {
        $checkedClasses = [];
        $block = new TestableBaseBlock('content', new FakeTab(), null, [], false);
        $block->setBlockDependencies([
            'blockExceptionClassExists' => static function (string $class) use (&$checkedClasses): bool {
                $checkedClasses[] = $class;

                return false;
            },
        ]);

        try {
            $block->exposeMakeBlockException('fallback failed', 'local');
            $this->fail('Expected fatal fallback exception.');
        } catch (fatal $exception) {
            $this->assertSame('fallback failed', $exception->getMessage());
            $this->assertSame('rollback', $block->getExceptionDbOper());
        }

        $this->assertSame(['\fan\project\exception\block\local'], $checkedClasses);
    }

    public function testMagicGetAndDelegatedCalls(): void
    {
        $tab = new FakeTab();
        $block = new TestableBaseBlock('magic', $tab, null, [], false);

        $this->assertSame('magic', $block->blockName);
        try {
            $block->unknownProperty;
            $this->fail('Expected undefined property exception.');
        } catch (\OutOfBoundsException $exception) {
            $this->assertStringContainsString('Undefined property', $exception->getMessage());
        }

        $this->assertSame('stored', $block->setSessionData('key', 'stored'));
        $this->assertSame('stored', $block->getSessionData('key'));
        $this->assertNull($block->removeSessionData('key'));
        $session = FakeServiceRegistry::get('session');
        $this->assertSame([get_class($block), 'block'], $session->args);

        $this->assertSame($tab->subscriber, $block->_subscribeForEvent('changed'));
        $this->assertSame('subscribeForEvent', $tab->subscriber->calls[0][0]);
        $this->assertSame($block, $tab->subscriber->calls[0][1]);
        $this->assertSame('changed', $tab->subscriber->calls[0][2]);
        $this->assertSame('eventHandler', $tab->subscriber->calls[0][3]);
    }

    public function testSourceNoLongerUsesContainerAwareTrait(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/block/base.php');
        $resolverSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_resolver.php');
        $defaultsSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_defaults.php');
        $contextGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_context_group.php');
        $factoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_factory_group.php');
        $applicationDataFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_application_data_factory_group.php');
        $databaseApplicationDataFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_database_application_data_factory_group.php');
        $userApplicationDataFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_user_application_data_factory_group.php');
        $applicationFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_application_factory_group.php');
        $applicationRuntimeFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_application_runtime_factory_group.php');
        $applicationServiceRuntimeFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_application_service_runtime_factory_group.php');
        $configApplicationRuntimeFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_config_application_runtime_factory_group.php');
        $applicationSupportFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_application_support_factory_group.php');
        $errorApplicationSupportFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_error_application_support_factory_group.php');
        $dateApplicationSupportFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_date_application_support_factory_group.php');
        $arrayHelperGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_array_helper_group.php');
        $arrayTransformHelperGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_array_transform_helper_group.php');
        $arrayAdducerHelperGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_array_adducer_helper_group.php');
        $recursiveMergerHelperGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_recursive_merger_helper_group.php');
        $arrayReadHelperGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_array_read_helper_group.php');
        $arrayValueReaderHelperGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_array_value_reader_helper_group.php');
        $arrayLikeCheckerHelperGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_array_like_checker_helper_group.php');
        $classHelperGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_class_helper_group.php');
        $dataCoreFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_data_core_factory_group.php');
        $dataFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_data_factory_group.php');
        $dataLoaderFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_data_loader_factory_group.php');
        $helperGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_helper_group.php');
        $mediaCoreFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_media_core_factory_group.php');
        $mediaErrorHelperGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_media_error_helper_group.php');
        $mediaFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_media_factory_group.php');
        $mediaTransferFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_media_transfer_factory_group.php');
        $metaMakerGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_meta_maker_group.php');
        $metaMakerStateGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_meta_maker_state_group.php');
        $metaMakerFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_meta_maker_factory_group.php');
        $metaLoaderGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_meta_loader_group.php');
        $metaRowLoaderGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_meta_row_loader_group.php');
        $phpArrayFileLoaderGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_php_array_file_loader_group.php');
        $metaRowFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_meta_row_factory_group.php');
        $navigationContextGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_navigation_context_group.php');
        $runtimeContextGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_runtime_context_group.php');
        $reflectorContextGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_reflector_context_group.php');
        $routeLocaleContextGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_route_locale_context_group.php');
        $localeFactoryContextGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_locale_factory_context_group.php');
        $matcherFactoryContextGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_matcher_factory_context_group.php');
        $blockFileStorageGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_block_file_storage_group.php');
        $blockFileStorageBlockGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_block_file_storage_block_group.php');
        $blockFileStorageMetaGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_block_file_storage_meta_group.php');
        $projectFileStorageGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_project_file_storage_group.php');
        $projectToolFileStorageGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_project_tool_file_storage_group.php');
        $rootHtmlFileStorageGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_root_html_file_storage_group.php');
        $requestContextGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_request_context_group.php');
        $requestInputContextGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_request_input_context_group.php');
        $requestRoleContextGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_request_role_context_group.php');
        $requestFactoryContextGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_request_factory_context_group.php');
        $roleFactoryContextGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_role_factory_context_group.php');
        $storageGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_storage_group.php');
        $sessionContextGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_session_context_group.php');
        $tabRuntimeContextGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_tab_runtime_context_group.php');
        $tabServiceRuntimeContextGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_tab_service_runtime_context_group.php');
        $bootstrapRuntimeContextGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_bootstrap_runtime_context_group.php');
        $uploadLimitStorageGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_upload_limit_storage_group.php');
        $viewFactoryLoaderGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_view_factory_loader_group.php');
        $viewParserExceptionFactoryLoaderGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_view_parser_exception_factory_loader_group.php');
        $viewRouterFactoryLoaderGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_view_router_factory_loader_group.php');
        $viewLoaderGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_view_loader_group.php');
        $viewStateLoaderGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_view_state_loader_group.php');
        $viewMetaGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_view_meta_group.php');
        $blockFactoryContextGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_block_factory_context_group.php');
        $blockFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_block_factory_group.php');
        $blockExceptionFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_block_exception_factory_group.php');
        $entityDataCoreFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_entity_data_core_factory_group.php');
        $jsonDataCoreFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_json_data_core_factory_group.php');
        $dataLoaderServiceFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_data_loader_service_factory_group.php');
        $pagerDataLoaderFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_pager_data_loader_factory_group.php');
        $obfuscatorMediaCoreFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_obfuscator_media_core_factory_group.php');
        $imageModifyMediaCoreFactoryGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_image_modify_media_core_factory_group.php');
        $imageMetadataMediaErrorHelperGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_image_metadata_media_error_helper_group.php');
        $errorLogMediaErrorHelperGroupSource = file_get_contents(dirname(__DIR__, 3) . '/core/block/base_dependency_error_log_media_error_helper_group.php');

        $this->assertStringNotContainsString('container_aware_trait', $source);
        $this->assertStringNotContainsString('function containerService(', $source);
        $this->assertStringNotContainsString('function blockService(', $source);
        $this->assertStringNotContainsString('serviceFactory', $source);
        $this->assertStringNotContainsString('container_registry::get()', $source);
        $this->assertStringNotContainsString('new $class($k, $this->tab, $this, $containerMeta', $source);
        $this->assertStringContainsString('private mixed $blockFactory = null;', $source);
        $this->assertStringContainsString('private mixed $metaMakerFactory = null;', $source);
        $this->assertStringContainsString('private mixed $viewRouterFactory = null;', $source);
        $this->assertStringContainsString('private mixed $templateFactory = null;', $source);
        $this->assertStringContainsString('private ?object $imageMetadataReader = null;', $source);
        $this->assertStringContainsString('private ?object $fileStorage = null;', $source);
        $this->assertStringContainsString('private ?object $projectToolFileStorage = null;', $source);
        $this->assertStringContainsString('private mixed $uploadSizeLimitProviderDependency = null;', $source);
        $this->assertStringContainsString('private \Closure $delayedMetaClassExists;', $source);
        $this->assertStringContainsString('private \Closure $blockExceptionClassExists;', $source);
        $this->assertStringContainsString('$dependencies = array_merge((new base_dependency_defaults())->dependencies(), $dependencies);', $source);
        $this->assertStringContainsString('(new base_dependency_defaults())->dependencies()', (string)$resolverSource);
        $this->assertStringContainsString("'delayedMetaClassExists' => static fn(string \$className): bool => class_exists(\$className, false)", (string)$defaultsSource);
        $this->assertStringContainsString("'blockExceptionClassExists' => static fn(string \$class): bool => class_exists(\$class)", (string)$defaultsSource);
        $this->assertStringContainsString('return (new base_dependency_resolver())->resolve($tab, $container);', $source);
        $this->assertStringContainsString('new base_dependency_context_group($container)', (string)$resolverSource);
        $this->assertStringContainsString('new base_dependency_factory_group($container)', (string)$resolverSource);
        $this->assertStringContainsString('new base_dependency_helper_group($container)', (string)$resolverSource);
        $this->assertStringContainsString('new base_dependency_storage_group($container)', (string)$resolverSource);
        $this->assertStringContainsString('new base_dependency_view_meta_group($container)', (string)$resolverSource);
        $this->assertStringContainsString('new base_dependency_block_file_storage_group($container)', (string)$storageGroupSource);
        $this->assertStringContainsString('new base_dependency_block_file_storage_block_group($container)', (string)$blockFileStorageGroupSource);
        $this->assertStringContainsString('new base_dependency_block_file_storage_meta_group($container)', (string)$blockFileStorageGroupSource);
        $this->assertStringContainsString('new base_dependency_project_file_storage_group($container)', (string)$storageGroupSource);
        $this->assertStringContainsString('new base_dependency_project_tool_file_storage_group($container)', (string)$projectFileStorageGroupSource);
        $this->assertStringContainsString('new base_dependency_root_html_file_storage_group($container)', (string)$projectFileStorageGroupSource);
        $this->assertStringContainsString('new base_dependency_upload_limit_storage_group($container)', (string)$storageGroupSource);
        $this->assertStringContainsString('new base_dependency_runtime_context_group($container)', (string)$contextGroupSource);
        $this->assertStringContainsString('new base_dependency_request_context_group($container)', (string)$contextGroupSource);
        $this->assertStringContainsString('new base_dependency_navigation_context_group($container)', (string)$contextGroupSource);
        $this->assertStringContainsString('new base_dependency_request_role_context_group($container)', (string)$requestContextGroupSource);
        $this->assertStringContainsString('new base_dependency_request_factory_context_group($container)', (string)$requestRoleContextGroupSource);
        $this->assertStringContainsString('new base_dependency_role_factory_context_group($container)', (string)$requestRoleContextGroupSource);
        $this->assertStringContainsString('new base_dependency_session_context_group($container)', (string)$requestContextGroupSource);
        $this->assertStringContainsString('new base_dependency_tab_runtime_context_group($container)', (string)$runtimeContextGroupSource);
        $this->assertStringContainsString('new base_dependency_tab_service_runtime_context_group($container)', (string)$tabRuntimeContextGroupSource);
        $this->assertStringContainsString('new base_dependency_bootstrap_runtime_context_group($container)', (string)$tabRuntimeContextGroupSource);
        $this->assertStringContainsString('new base_dependency_request_input_context_group($container)', (string)$runtimeContextGroupSource);
        $this->assertStringContainsString('new base_dependency_application_factory_group($container)', (string)$factoryGroupSource);
        $this->assertStringContainsString('new base_dependency_data_factory_group($container)', (string)$factoryGroupSource);
        $this->assertStringContainsString('new base_dependency_media_factory_group($container)', (string)$factoryGroupSource);
        $this->assertStringContainsString('new base_dependency_reflector_context_group($container)', (string)$navigationContextGroupSource);
        $this->assertStringContainsString('new base_dependency_route_locale_context_group($container)', (string)$navigationContextGroupSource);
        $this->assertStringContainsString('new base_dependency_locale_factory_context_group($container)', (string)$routeLocaleContextGroupSource);
        $this->assertStringContainsString('new base_dependency_matcher_factory_context_group($container)', (string)$routeLocaleContextGroupSource);
        $this->assertStringContainsString('new base_dependency_media_core_factory_group($container)', (string)$mediaFactoryGroupSource);
        $this->assertStringContainsString('new base_dependency_media_transfer_factory_group($container)', (string)$mediaFactoryGroupSource);
        $this->assertStringContainsString('new base_dependency_application_runtime_factory_group($container)', (string)$applicationFactoryGroupSource);
        $this->assertStringContainsString('new base_dependency_application_data_factory_group($container)', (string)$applicationFactoryGroupSource);
        $this->assertStringContainsString('new base_dependency_application_support_factory_group($container)', (string)$applicationFactoryGroupSource);
        $this->assertStringContainsString('new base_dependency_application_service_runtime_factory_group($container)', (string)$applicationRuntimeFactoryGroupSource);
        $this->assertStringContainsString('new base_dependency_config_application_runtime_factory_group($container)', (string)$applicationRuntimeFactoryGroupSource);
        $this->assertStringContainsString('new base_dependency_error_application_support_factory_group($container)', (string)$applicationSupportFactoryGroupSource);
        $this->assertStringContainsString('new base_dependency_date_application_support_factory_group($container)', (string)$applicationSupportFactoryGroupSource);
        $this->assertStringContainsString('new base_dependency_database_application_data_factory_group($container)', (string)$applicationDataFactoryGroupSource);
        $this->assertStringContainsString('new base_dependency_user_application_data_factory_group($container)', (string)$applicationDataFactoryGroupSource);
        $this->assertStringContainsString('new base_dependency_view_loader_group($container)', (string)$viewMetaGroupSource);
        $this->assertStringContainsString('new base_dependency_meta_loader_group($container)', (string)$viewMetaGroupSource);
        $this->assertStringContainsString('new base_dependency_block_factory_context_group($container)', (string)$viewMetaGroupSource);
        $this->assertStringContainsString('new base_dependency_block_factory_group($container)', (string)$blockFactoryContextGroupSource);
        $this->assertStringContainsString('new base_dependency_block_exception_factory_group($container)', (string)$blockFactoryContextGroupSource);
        $this->assertStringContainsString('new base_dependency_view_factory_loader_group($container)', (string)$viewLoaderGroupSource);
        $this->assertStringContainsString('new base_dependency_view_parser_exception_factory_loader_group($container)', (string)$viewFactoryLoaderGroupSource);
        $this->assertStringContainsString('new base_dependency_view_router_factory_loader_group($container)', (string)$viewFactoryLoaderGroupSource);
        $this->assertStringContainsString('new base_dependency_view_state_loader_group($container)', (string)$viewLoaderGroupSource);
        $this->assertStringContainsString('new base_dependency_data_core_factory_group($container)', (string)$dataFactoryGroupSource);
        $this->assertStringContainsString('new base_dependency_entity_data_core_factory_group($container)', (string)$dataCoreFactoryGroupSource);
        $this->assertStringContainsString('new base_dependency_json_data_core_factory_group($container)', (string)$dataCoreFactoryGroupSource);
        $this->assertStringContainsString('new base_dependency_data_loader_factory_group($container)', (string)$dataFactoryGroupSource);
        $this->assertStringContainsString('new base_dependency_data_loader_service_factory_group($container)', (string)$dataLoaderFactoryGroupSource);
        $this->assertStringContainsString('new base_dependency_pager_data_loader_factory_group($container)', (string)$dataLoaderFactoryGroupSource);
        $this->assertStringContainsString('new base_dependency_meta_maker_group($container)', (string)$metaLoaderGroupSource);
        $this->assertStringContainsString('new base_dependency_meta_maker_state_group($container)', (string)$metaMakerGroupSource);
        $this->assertStringContainsString('new base_dependency_meta_maker_factory_group($container)', (string)$metaMakerGroupSource);
        $this->assertStringContainsString('new base_dependency_meta_row_loader_group($container)', (string)$metaLoaderGroupSource);
        $this->assertStringContainsString('new base_dependency_php_array_file_loader_group($container)', (string)$metaRowLoaderGroupSource);
        $this->assertStringContainsString('new base_dependency_meta_row_factory_group($container)', (string)$metaRowLoaderGroupSource);
        $this->assertStringContainsString('new base_dependency_array_helper_group($container)', (string)$helperGroupSource);
        $this->assertStringContainsString('new base_dependency_class_helper_group($container)', (string)$helperGroupSource);
        $this->assertStringContainsString('new base_dependency_media_error_helper_group($container)', (string)$helperGroupSource);
        $this->assertStringContainsString('new base_dependency_obfuscator_media_core_factory_group($container)', (string)$mediaCoreFactoryGroupSource);
        $this->assertStringContainsString('new base_dependency_image_modify_media_core_factory_group($container)', (string)$mediaCoreFactoryGroupSource);
        $this->assertStringContainsString('new base_dependency_image_metadata_media_error_helper_group($container)', (string)$mediaErrorHelperGroupSource);
        $this->assertStringContainsString('new base_dependency_error_log_media_error_helper_group($container)', (string)$mediaErrorHelperGroupSource);
        $this->assertStringContainsString('new base_dependency_array_transform_helper_group($container)', (string)$arrayHelperGroupSource);
        $this->assertStringContainsString('new base_dependency_array_read_helper_group($container)', (string)$arrayHelperGroupSource);
        $this->assertStringContainsString('new base_dependency_array_adducer_helper_group($container)', (string)$arrayTransformHelperGroupSource);
        $this->assertStringContainsString('new base_dependency_recursive_merger_helper_group($container)', (string)$arrayTransformHelperGroupSource);
        $this->assertStringContainsString('new base_dependency_array_value_reader_helper_group($container)', (string)$arrayReadHelperGroupSource);
        $this->assertStringContainsString('new base_dependency_array_like_checker_helper_group($container)', (string)$arrayReadHelperGroupSource);
        $this->assertStringNotContainsString("'imageMetadataReader' => \$container->get('image_metadata_reader')", $source);
        $this->assertStringNotContainsString("'metaMakerFactory' => \$container->get('meta_maker_factory')", $source);
        $this->assertStringNotContainsString("'viewRouterFactory' => \$container->get('view_router_factory')", $source);
        $this->assertStringContainsString("'applicationFactory' => fn(): mixed => \$this->container->get('application')", (string)$applicationServiceRuntimeFactoryGroupSource);
        $this->assertStringContainsString("'configFactory' => fn(mixed ...\$arguments): mixed => \$this->container->get('config', ...\$arguments)", (string)$configApplicationRuntimeFactoryGroupSource);
        $this->assertStringContainsString("'databaseFactory' => fn(mixed ...\$arguments): mixed => \$this->container->get('database', ...\$arguments)", (string)$databaseApplicationDataFactoryGroupSource);
        $this->assertStringContainsString("'userFactory' => fn(mixed ...\$arguments): mixed => \$this->container->get('user', ...\$arguments)", (string)$userApplicationDataFactoryGroupSource);
        $this->assertStringContainsString("'errorFactory' => fn(): mixed => \$this->container->get('error')", (string)$errorApplicationSupportFactoryGroupSource);
        $this->assertStringContainsString("'dateFactory' => fn(mixed ...\$arguments): mixed => \$this->container->get('date', ...\$arguments)", (string)$dateApplicationSupportFactoryGroupSource);
        $this->assertStringContainsString("'entityFactory' => fn(mixed ...\$arguments): mixed => \$this->container->get('entity', ...\$arguments)", (string)$entityDataCoreFactoryGroupSource);
        $this->assertStringContainsString("'jsonFactory' => fn(mixed ...\$arguments): mixed => \$this->container->get('json', ...\$arguments)", (string)$jsonDataCoreFactoryGroupSource);
        $this->assertStringContainsString("'dataLoaderFactory' => fn(): mixed => \$this->container->get('data_loader')", (string)$dataLoaderServiceFactoryGroupSource);
        $this->assertStringContainsString("'pagerFactory' => fn(mixed ...\$arguments): mixed => \$this->container->get('pager', ...\$arguments)", (string)$pagerDataLoaderFactoryGroupSource);
        $this->assertStringContainsString("'obfuscatorFactory' => fn(mixed ...\$arguments): mixed => \$this->container->get('obfuscator', ...\$arguments)", (string)$obfuscatorMediaCoreFactoryGroupSource);
        $this->assertStringContainsString("'imageModifyFactory' => fn(mixed ...\$arguments): mixed => \$this->container->get('image_modify', ...\$arguments)", (string)$imageModifyMediaCoreFactoryGroupSource);
        $this->assertStringContainsString("'transferFactory' => fn(mixed ...\$arguments): mixed => \$this->container->get('transfer', ...\$arguments)", (string)$mediaTransferFactoryGroupSource);
        $this->assertStringContainsString("'reflectorFactory' => fn(): mixed => \$this->container->get('reflector')", (string)$reflectorContextGroupSource);
        $this->assertStringContainsString("'localeFactory' => fn(): mixed => \$this->container->get('locale')", (string)$localeFactoryContextGroupSource);
        $this->assertStringContainsString("'matcherFactory' => fn(): mixed => \$this->container->get('matcher')", (string)$matcherFactoryContextGroupSource);
        $this->assertStringContainsString("'requestFactory' => fn(): mixed => \$this->container->get('request')", (string)$requestFactoryContextGroupSource);
        $this->assertStringContainsString("'roleFactory' => fn(): mixed => \$this->container->get('role')", (string)$roleFactoryContextGroupSource);
        $this->assertStringContainsString("'sessionFactory' => fn(string \$nameSpace, string \$group = 'block'): mixed => \$this->container->get('session', \$nameSpace, \$group)", (string)$sessionContextGroupSource);
        $this->assertStringContainsString("'tab' => \$this->container->get('tab')", (string)$tabServiceRuntimeContextGroupSource);
        $this->assertStringContainsString("'runtime' => \$this->container->get('bootstrap_runtime')", (string)$bootstrapRuntimeContextGroupSource);
        $this->assertStringContainsString("'requestInputFactory' => fn(): mixed => \$this->container->get('request_input')", (string)$requestInputContextGroupSource);
        $this->assertStringContainsString("'arrayAdducer' => \$this->container->get('array_adducer')", (string)$arrayAdducerHelperGroupSource);
        $this->assertStringContainsString("'recursiveMerger' => \$this->container->get('recursive_merger')", (string)$recursiveMergerHelperGroupSource);
        $this->assertStringContainsString("'arrayValueReader' => \$this->container->get('array_value_reader')", (string)$arrayValueReaderHelperGroupSource);
        $this->assertStringContainsString("'arrayLikeChecker' => \$this->container->get('array_like_checker')", (string)$arrayLikeCheckerHelperGroupSource);
        $this->assertStringContainsString("'blockFactory' => \$this->container->get('block_factory')", (string)$blockFactoryGroupSource);
        $this->assertStringContainsString("'blockExceptionFactory' => \$this->container->get('block_exception_factory')", (string)$blockExceptionFactoryGroupSource);
        $this->assertStringContainsString("'shortClassNameResolver' => \$this->container->get('short_class_name_resolver')", (string)$classHelperGroupSource);
        $this->assertStringContainsString("'imageMetadataReader' => \$this->container->get('image_metadata_reader')", (string)$imageMetadataMediaErrorHelperGroupSource);
        $this->assertStringContainsString("'errorLogWriter' => \$this->container->get('error_log_writer')", (string)$errorLogMediaErrorHelperGroupSource);
        $this->assertStringContainsString("'metaMakerState' => \$this->container->get('meta_maker_state')", (string)$metaMakerStateGroupSource);
        $this->assertStringContainsString("'metaMakerFactory' => \$this->container->get('meta_maker_factory')", (string)$metaMakerFactoryGroupSource);
        $this->assertStringContainsString("'phpArrayFileLoader' => \$this->container->get('php_array_file_loader')", (string)$phpArrayFileLoaderGroupSource);
        $this->assertStringContainsString("'metaRowFactory' => \$this->container->get('meta_row_factory')", (string)$metaRowFactoryGroupSource);
        $this->assertStringContainsString("'viewParserExceptionFactory' => \$this->container->get('error500_exception_factory')", (string)$viewParserExceptionFactoryLoaderGroupSource);
        $this->assertStringContainsString("'viewRouterFactory' => \$this->container->get('view_router_factory')", (string)$viewRouterFactoryLoaderGroupSource);
        $this->assertStringContainsString("'viewLoaderState' => \$this->container->get('view_loader_state')", (string)$viewStateLoaderGroupSource);
        $this->assertStringContainsString("'blockFileStorage' => \$this->container->has('block_file_storage') ? \$this->container->get('block_file_storage') : null", (string)$blockFileStorageBlockGroupSource);
        $this->assertStringContainsString("'metaFileStorage' => \$this->container->has('meta_file_storage') ? \$this->container->get('meta_file_storage') : null", (string)$blockFileStorageMetaGroupSource);
        $this->assertStringContainsString("'projectToolFileStorage' => \$this->container->has('project_tool_file_storage') ? \$this->container->get('project_tool_file_storage') : null", (string)$projectToolFileStorageGroupSource);
        $this->assertStringContainsString("'rootHtmlFileStorage' => \$this->container->has('root_html_file_storage') ? \$this->container->get('root_html_file_storage') : null", (string)$rootHtmlFileStorageGroupSource);
        $this->assertStringContainsString("'uploadSizeLimitProvider' => \$this->container->has('upload_size_limit_provider') ? \$this->container->get('upload_size_limit_provider') : null", (string)$uploadLimitStorageGroupSource);
        $this->assertStringContainsString('$this->fileStorage()->isFile($templatePath)', $source);
        $this->assertStringContainsString('$this->fileStorage()->exists($currentPath . \'meta.php\')', $source);
        $this->assertStringContainsString("\$this->setUploadSizeLimitProvider(\$this->uploadSizeLimitProviderDependency);", $source);
        $this->assertStringContainsString('private function createEmbeddedBlock(', $source);
        $this->assertStringContainsString('$factory = $this->metaMakerFactory();', $source);
        $this->assertStringContainsString('$factory = $this->viewRouterFactory();', $source);
        $this->assertStringContainsString('$router = $factory($viewClass, $this, $loaderState, $this->blockExceptionFactory);', $source);
        $this->assertStringContainsString('Meta maker factory must return a meta maker.', $source);
        $this->assertStringContainsString('View router factory must return an object.', $source);
        $this->assertStringContainsString('($this->blockFactory)(', $source);
        $this->assertStringContainsString('($this->blockExceptionFactory)($class, $this, $logErrMsg, $code, $previous);', $source);
        $this->assertStringContainsString('private function delayedMetaClassExists(string $className): bool', $source);
        $this->assertStringContainsString('if ($this->delayedMetaClassExists(\'\fan\core\base\meta\delayed\'))', $source);
        $this->assertStringContainsString('private function blockExceptionClassExists(string $class): bool', $source);
        $this->assertStringContainsString('if (!$this->blockExceptionClassExists($class))', $source);
        $this->assertStringContainsString('private function createBlockFatalException(', $source);
        $this->assertStringContainsString('return $this->createBlockException(\'\fan\project\exception\block\fatal\', $logErrMsg, $code, $previous);', $source);
        $this->assertStringContainsString('$this->_makeBlockException(\'Call to unknown Embedded Block "\' . $key . \'"\', \'local\', null, E_USER_WARNING);', $source);
        $this->assertStringNotContainsString('if (class_exists(\'\fan\core\base\meta\delayed\', false))', $source);
        $this->assertStringNotContainsString('=> class_exists($className, false)', $source);
        $this->assertStringNotContainsString('=> class_exists($class)', $source);
        $this->assertStringNotContainsString('if (!class_exists($class))', $source);
        $this->assertStringNotContainsString('new \fan\project\base\meta\maker(', $source);
        $this->assertStringNotContainsString('new \fan\project\exception\block\local(', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\block\fatal as fatalException;', $source);
        $this->assertStringNotContainsString('::getRouter($this', $source);
        $this->assertStringNotContainsString('call_user_func($this->blockFactory', $source);
        $this->assertStringNotContainsString('call_user_func($this->blockExceptionFactory', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:is_file|file_exists)\s*\(/',
            $source
        );
    }

    public function testDelegateHelpersCacheRoleCheckRunInitAndDebugInfo(): void
    {
        $target = new DelegateTarget();
        $block = new TestableBaseBlock('debug', new FakeTab(), null, [], false);
        $block->setEmbeddedBlocksForTest(['child' => new TestableBaseBlock('child', new FakeTab(), null, [], false)]);
        $block->setTemplate($this->fixturePath('ExplicitTemplate.tpl'));

        $this->assertSame(['a', 'b'], $block->exposeCallOrdinaryDelegate($target, 'callMe', ['a', 'b']));
        $identified = $block->exposeCallIdentifiedDelegate($target, 'callMe', ['event']);
        $this->assertSame($block, $identified[0]);
        $this->assertSame('event', $identified[1]);
        $this->assertSame($block, $block->exposeSetCacheRole(['admin', 'editor']));
        $this->assertTrue($block->checkRunInit());
        $this->assertNull($block->init());
        $this->assertNull($block->initRequired());
        $this->assertNull($block->runAfterInit());
        $this->assertSame('FanTest\core\block\\', $block->getNamespace());

        $debug = $block->getDebugInfo();
        $this->assertSame('debug', $debug['blockName']);
        $this->assertSame(get_class($block), $debug['className']);
        $this->assertSame($this->fixturePath('ExplicitTemplate.tpl'), $debug['templateFile']);
        $this->assertArrayHasKey('embeddedBlocks', $debug);
        $this->assertArrayHasKey('metaSourse', $debug);
        $this->assertArrayHasKey('folderMeta', $debug);
    }
}
