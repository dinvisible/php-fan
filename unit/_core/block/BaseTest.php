<?php

declare(strict_types=1);

use FanTest\_core\block\DelegateTarget;
use FanTest\_core\block\FakeLocale;
use FanTest\_core\block\FakeReflector;
use FanTest\_core\block\FakeRequest;
use FanTest\_core\block\FakeRoleRegistry;
use FanTest\_core\block\FakeRootBlock;
use FanTest\_core\block\FakeServiceRegistry;
use FanTest\_core\block\FakeTab;
use FanTest\_core\block\FakeViewClass;
use FanTest\_core\block\FakeViewRouter;
use FanTest\_core\block\ObjectWithToArray;
use FanTest\_core\block\TestableBaseBlock;

require_once __DIR__ . '/../../mock/_core/block/GlobalFunctions.php';
require_once __DIR__ . '/../../mock/_core/block/FrameworkStubs.php';
require_once __DIR__ . '/../../mock/_core/block/FakeServices.php';
require_once __DIR__ . '/../../../_core/block/base.php';
require_once __DIR__ . '/../../mock/_core/block/TestableBaseBlock.php';

class BaseTest extends \PHPUnit\Framework\TestCase
{
    private array $errors = [];
    private bool $capturingErrors = false;

    protected function setUp(): void
    {
        FakeServiceRegistry::reset();
        TestableBaseBlock::useMeta([]);
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
        return class_exists('\fan\project\base\meta\row', false)
            ? new \fan\project\base\meta\row($block->getMetaMaker(), $data)
            : new \fan\core\base\meta\row($data);
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
            $class = \fan\core\base\meta\delayed::class;
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

        try {
            $block->setTemplate($this->fixturePath('missing.tpl'), true);
            $this->fail('Expected fatal block exception for an invalid template path.');
        } catch (\fan\project\exception\block\fatal $exception) {
            $this->assertStringContainsString('Incorrect template path', $exception->getMessage());
        }
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

    public function testRootParametersAndTemplateVariablesAreCopiedFromMeta(): void
    {
        $root = new FakeRootBlock('root', new FakeTab(), null, [], false);
        $view = new FakeViewRouter();
        $block = new TestableBaseBlock('content', new FakeTab(), null, [], false);
        $block->setViewForTest($view);
        $block->setMetaRowForTest($this->makeMetaRow($block, [
            'meta_tag'    => ['description'],
            'externalCss' => ['/main.css'],
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
            ['setExternalCss', ['/main.css']],
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
        } catch (\fan\project\exception\block\fatal $exception) {
            $this->assertStringContainsString('Main Block', $exception->getMessage());
        }
    }

    public function testEmbeddedBlockLookupThrowsForUnknownKey(): void
    {
        $child = new TestableBaseBlock('child', new FakeTab(), null, [], false);
        $block = new TestableBaseBlock('parent', new FakeTab(), null, [], false);
        $block->setEmbeddedBlocksForTest(['child' => $child]);

        $this->assertSame($child, $block->getEmbeddedBlock('child'));
        $this->expectException(\fan\project\exception\block\local::class);
        $this->expectExceptionMessage('unknown Embedded Block');

        $block->getEmbeddedBlock('missing');
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
        } catch (\fan\project\exception\block\fatal $exception) {
            $this->assertStringContainsString('Unknown block path', $exception->getMessage());
        }

        try {
            $block->exposeMakeBlockException('local failed', 'local');
            $this->fail('Expected local block exception.');
        } catch (\fan\project\exception\block\local $exception) {
            $this->assertSame('nothing', $block->getExceptionDbOper());
            $this->assertSame('local failed', $exception->getMessage());
        }

        try {
            $block->exposeMakeBlockException('unknown failed', 'unknown');
            $this->fail('Expected fatal fallback exception.');
        } catch (\fan\project\exception\block\fatal $exception) {
            $this->assertSame('rollback', $block->getExceptionDbOper());
        }

        try {
            $block->exposeMakeBlockException('commit failed', 'fatal', 'commit');
            $this->fail('Expected fatal exception with explicit DB operation.');
        } catch (\fan\project\exception\block\fatal $exception) {
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
        $session = service('session');
        $this->assertSame([get_class($block), 'block'], $session->args);

        $this->assertSame($tab->subscriber, $block->_subscribeForEvent('changed'));
        $this->assertSame('subscribeForEvent', $tab->subscriber->calls[0][0]);
        $this->assertSame($block, $tab->subscriber->calls[0][1]);
        $this->assertSame('changed', $tab->subscriber->calls[0][2]);
        $this->assertSame('eventHandler', $tab->subscriber->calls[0][3]);
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
