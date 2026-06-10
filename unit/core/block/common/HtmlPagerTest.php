<?php

declare(strict_types=1);

use fan\core\block\common\html_pager;
use FanTest\core\SourceFileContractTestCase;

class BlockCommonHtmlPagerTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/block/common/html_pager.php';

    public function testPageGroupUsesSingleGroupWhenTotalFitsLimits(): void
    {
        $pager = new BlockCommonHtmlPagerProbe();

        $this->assertSame([
            'pagesNL' => [1, 2, 3, 4],
            'pages' => [[1, 2, 3, 4]],
        ], $pager->_getPageGroup(4, 2));
    }

    public function testPageGroupSplitsStartMiddleAndEndForFarMiddlePage(): void
    {
        $pager = new BlockCommonHtmlPagerProbe([
            'qttLimit' => ['startEnd' => 2, 'middle' => 3],
        ]);

        $this->assertSame([
            'pagesNL' => [49.0, 50.0, 51.0],
            'pages' => [
                [1, 2],
                [49.0, 50.0, 51.0],
                [99, 100],
            ],
        ], $pager->_getPageGroup(100, 50));
    }

    public function testPreOutputPublishesPagerStateToView(): void
    {
        $pagerEngine = new BlockCommonHtmlPagerEngineDouble(12, 5);
        $pager = new BlockCommonHtmlPagerProbe([
            'quantifier' => ['allow' => true],
            'tplType' => 'select',
        ], $pagerEngine);

        $pager->preOutput();

        $this->assertSame(12, $pager->view->pageQtt);
        $this->assertSame(5, $pager->view->currentPage);
        $this->assertTrue($pager->view->showIfOnePage);
        $this->assertSame('select', $pager->view->tplType);
    }

    public function testPageUriDelegatesToPagerService(): void
    {
        $pager = new BlockCommonHtmlPagerProbe([], new BlockCommonHtmlPagerEngineDouble(3, 1));

        $this->assertSame('/page/2', $pager->getPageUri(2));
        $this->assertSame('', $pager->getEmbeddedForm());
    }

    public function testPostCreateUsesInjectedPagerService(): void
    {
        $pagerEngine = new BlockCommonHtmlPagerEngineDouble(4, 2);
        $pager = new BlockCommonHtmlPagerProbe([], null, $pagerEngine);

        $pager->postCreate();

        $this->assertSame('/page/3', $pager->getPageUri(3));
    }

    public function testSourceNoLongerCallsContainerServiceDirectly(): void
    {
        $this->assertStringNotContainsString('containerService(', $this->sourceCode());
    }
}

final class BlockCommonHtmlPagerProbe extends html_pager
{
    public object $view;

    public function __construct(
        private array $metaData = [],
        ?BlockCommonHtmlPagerEngineDouble $pagerEngine = null,
        private ?BlockCommonHtmlPagerEngineDouble $injectedPager = null,
    ) {
        $this->view = new stdClass();
        $this->pager = $pagerEngine ?? new BlockCommonHtmlPagerEngineDouble(1, 1);
    }

    public function getMeta(string|array|null $key = null, mixed $default = null, bool $convToArray = false): mixed
    {
        if ($key === null) {
            return $this->metaData;
        }
        if (is_array($key)) {
            $value = $this->metaData;
            foreach ($key as $part) {
                if (!is_array($value) || !array_key_exists($part, $value)) {
                    return $default;
                }
                $value = $value[$part];
            }
            return $value;
        }
        return $this->metaData[$key] ?? $default;
    }

    public function preOutput(): void
    {
        $this->_preOutput();
    }

    public function postCreate(): void
    {
        $this->_postCreate();
    }

    protected function pagerService(mixed ...$arguments): object
    {
        return $this->injectedPager ?? new BlockCommonHtmlPagerEngineDouble(1, 1);
    }
}

final class BlockCommonHtmlPagerEngineDouble
{
    public function __construct(private int $pageQtt, private int $pageNum)
    {
    }

    public function getPageUri(int|string $page): string
    {
        return '/page/' . $page;
    }

    public function getPageQtt(): int
    {
        return $this->pageQtt;
    }

    public function getPageNum(): int
    {
        return $this->pageNum;
    }
}
