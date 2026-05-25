<?php

declare(strict_types=1);

use FanTest\_core\block\DelegateTarget;
use FanTest\_core\block\FakeLocale;
use FanTest\_core\block\FakeReflector;
use FanTest\_core\block\FakeRequest;
use FanTest\_core\block\FakeRoleRegistry;
use FanTest\_core\block\FakeRootBlock;
use FanTest\_core\block\FakeServiceContainer;
use FanTest\_core\block\FakeServiceRegistry;
use FanTest\_core\block\FakeTab;
use FanTest\_core\block\FakeViewClass;
use FanTest\_core\block\FakeViewRouter;
use FanTest\_core\block\ObjectWithToArray;
use FanTest\_core\block\TestableBaseBlock;
use FanTest\_core\block\FakeMetaMaker;
use fan\core\base\meta\delayed;
use fan\core\base\meta\maker;
use fan\core\base\meta\maker_state;
use fan\core\base\meta\row as meta_row;
use fan\core\block\base;
use fan\project\base\meta\row;
use fan\project\exception\block\fatal;
use fan\project\exception\block\local;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../mock/_core/block/GlobalFunctions.php';
require_once __DIR__ . '/../../mock/_core/block/FrameworkStubs.php';
require_once __DIR__ . '/../../mock/_core/block/FakeServices.php';
require_once __DIR__ . '/../../../_core/base/meta/maker_state.php';
require_once __DIR__ . '/../../../_core/block/base.php';
require_once __DIR__ . '/../../mock/_core/block/TestableBaseBlock.php';

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
        return __DIR__ . '/../../mock/_core/block/' . $file;
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
        $this->assertInstanceOf('FanTest\_core\block\FakeMetaMaker', $block->getMetaMaker());
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
        $this->assertInstanceOf('FanTest\_core\block\FakeViewRouter', $block->getView());
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
            'FanTest\_core\block\TestableBaseBlock' => $this->fixturePath('TestableBaseBlock.php'),
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
            'FanTest\_core\block\TestableBaseBlock' => $this->fixturePath('TestableBaseBlock.php'),
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
        $this->assertSame('FanTest\_core\block\TestableBaseBlock', $seenClass);
        $this->assertSame($this->fixturePath('TestableBaseBlock.tpl'), $block->getTemplate());
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
        $tab->loadBlockMap['project/child'] = 'FanTest\_core\block\FakeEmbeddedBlock';
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
        $this->assertInstanceOf('FanTest\_core\block\FakeEmbeddedBlock', $embedded['child']);
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
        $tab->loadBlockMap['known/path'] = 'FanTest\_core\block\FakeEmbeddedBlock';
        $block = new TestableBaseBlock('content', $tab, null, [], false);

        $this->assertNull($block->exposeGetBlock('missing', false));
        $this->assertSame('FanTest\_core\block\FakeEmbeddedBlock', $block->exposeParseClassName('known/path'));
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
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/block/base.php');

        $this->assertStringNotContainsString('container_aware_trait', $source);
        $this->assertStringNotContainsString('function containerService(', $source);
        $this->assertStringNotContainsString('function blockService(', $source);
        $this->assertStringNotContainsString('serviceFactory', $source);
        $this->assertStringNotContainsString('container_registry::get()', $source);
        $this->assertStringNotContainsString('new $class($k, $this->tab, $this, $containerMeta', $source);
        $this->assertStringContainsString('private mixed $blockFactory = null;', $source);
        $this->assertStringContainsString('private mixed $metaMakerFactory = null;', $source);
        $this->assertStringContainsString('private mixed $viewRouterFactory = null;', $source);
        $this->assertStringContainsString('private ?object $imageMetadataReader = null;', $source);
        $this->assertStringContainsString('private ?object $fileStorage = null;', $source);
        $this->assertStringContainsString('private ?object $projectToolFileStorage = null;', $source);
        $this->assertStringContainsString('private mixed $uploadSizeLimitProviderDependency = null;', $source);
        $this->assertStringContainsString("'imageMetadataReader' => \$container->get('image_metadata_reader')", $source);
        $this->assertStringContainsString("'metaMakerFactory' => \$container->get('meta_maker_factory')", $source);
        $this->assertStringContainsString("'viewRouterFactory' => \$container->get('view_router_factory')", $source);
        $this->assertStringContainsString("'blockFileStorage' => \$container->has('block_file_storage') ? \$container->get('block_file_storage') : null", $source);
        $this->assertStringContainsString("'projectToolFileStorage' => \$container->has('project_tool_file_storage') ? \$container->get('project_tool_file_storage') : null", $source);
        $this->assertStringContainsString("'rootHtmlFileStorage' => \$container->has('root_html_file_storage') ? \$container->get('root_html_file_storage') : null", $source);
        $this->assertStringContainsString("'uploadSizeLimitProvider' => \$container->has('upload_size_limit_provider') ? \$container->get('upload_size_limit_provider') : null", $source);
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
        $this->assertStringContainsString('private function createBlockFatalException(', $source);
        $this->assertStringContainsString('return $this->createBlockException(\'\fan\project\exception\block\fatal\', $logErrMsg, $code, $previous);', $source);
        $this->assertStringContainsString('$this->_makeBlockException(\'Call to unknown Embedded Block "\' . $key . \'"\', \'local\', null, E_USER_WARNING);', $source);
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
        $this->assertSame('FanTest\_core\block\\', $block->getNamespace());

        $debug = $block->getDebugInfo();
        $this->assertSame('debug', $debug['blockName']);
        $this->assertSame(get_class($block), $debug['className']);
        $this->assertSame($this->fixturePath('ExplicitTemplate.tpl'), $debug['templateFile']);
        $this->assertArrayHasKey('embeddedBlocks', $debug);
        $this->assertArrayHasKey('metaSourse', $debug);
        $this->assertArrayHasKey('folderMeta', $debug);
    }
}
