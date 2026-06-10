<?php

declare(strict_types=1);

use fan\core\block\common\html_nav;
use FanTest\core\SourceFileContractTestCase;

if (!function_exists('role')) {
    function role(mixed $condition): bool
    {
        return $condition !== 'deny';
    }
}

class BlockCommonHtmlNavTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/block/common/html_nav.php';

    public function testInitWritesParsedNavigationToView(): void
    {
        $block = new BlockCommonHtmlNavProbe([
            'nav' => [
                'home' => [
                    'nav_name' => 'Home',
                    'url_value' => '~/home',
                    'url_type' => 'local',
                    'nav_key' => 'home',
                ],
                'foreign' => [
                    'nav_name' => 'Docs',
                    'url_value' => 'https://example.test/docs',
                    'url_type' => 'foreign',
                    'children' => [
                        [
                            'nav_name' => 'Child',
                            'url_value' => '/child',
                        ],
                    ],
                ],
                'dummy' => [
                    'url_value' => '',
                    'url_type' => 'dummy',
                ],
                'hidden' => [
                    'nav_name' => 'Hidden',
                    'url_value' => '/hidden',
                    'role' => 'deny',
                ],
            ],
        ], '/home');

        $block->init();

        $this->assertSame([
            'home' => [
                'nav_name' => 'Home',
                'url_value' => '/uri/~/home/link//',
                'current' => true,
                'children' => [],
            ],
            'foreign' => [
                'nav_name' => 'Docs',
                'url_value' => 'https://example.test/docs',
                'current' => false,
                'children' => [
                    [
                        'nav_name' => 'Child',
                        'url_value' => '/uri//child/link//',
                        'current' => false,
                        'children' => [],
                    ],
                ],
            ],
            'dummy' => [
                'nav_name' => '&nbsp;',
                'url_value' => '#',
                'current' => false,
                'children' => [],
            ],
        ], $block->view->navList);
    }

    public function testCurrentElementCanMatchIndexedPathSegments(): void
    {
        $block = new BlockCommonHtmlNavProbe([], '/catalog/books');

        $this->assertTrue($block->checkCurrent('0:catalog,1:books'));
        $this->assertFalse($block->checkCurrent('0:catalog,1:music'));
    }

    public function testCurrentRequestCanUseInjectedMatcherPrefix(): void
    {
        $block = new BlockCommonHtmlNavProbe(['allowUrlPrefix' => true], '/books/list', 'admin/tools');

        $this->assertTrue($block->checkCurrent('0:tools,1:admin,2:books'));
        $this->assertFalse($block->checkCurrent('0:books'));
    }

    public function testSourceNoLongerCallsContainerServiceDirectly(): void
    {
        $source = $this->sourceCode();

        $this->assertStringNotContainsString('containerService(', $source);
        $this->assertStringNotContainsString('array_val(', $source);
        $this->assertStringContainsString('$this->arrayValueReader()', $source);
    }
}

final class BlockCommonHtmlNavProbe extends html_nav
{
    public object $view;

    public function __construct(
        private array $metaData,
        string $currentUri,
        private string $appPrefix = '',
    )
    {
        $this->view = new stdClass();
        $this->tab = new BlockCommonHtmlNavTabDouble($currentUri);
        $this->setBlockDependencies([
            'arrayValueReader' => static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default,
        ]);
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

    public function checkCurrent(string $key): bool
    {
        return $this->_checkCurrentElement($key);
    }

    protected function roleService(): object
    {
        return new BlockCommonHtmlNavRoleDouble();
    }

    protected function matcherService(): object
    {
        return new BlockCommonHtmlNavMatcherDouble($this->appPrefix);
    }
}

final class BlockCommonHtmlNavTabDouble
{
    public function __construct(private string $currentUri)
    {
    }

    public function getURI(string $urn, string $type = 'link', mixed $addSid = null, mixed $protocol = null): string
    {
        return '/uri/' . $urn . '/' . $type . '/' . (string)$addSid . '/' . (string)$protocol;
    }

    public function getCurrentURI(
        mixed $corLanguage = true,
        bool $addExt = true,
        bool $addQueryStr = true,
        mixed $addSid = null,
        mixed $sprtr = null,
    ): string {
        return $this->currentUri;
    }
}

final class BlockCommonHtmlNavRoleDouble
{
    public function check(mixed $condition): bool
    {
        return $condition !== 'deny';
    }
}

final class BlockCommonHtmlNavMatcherDouble
{
    public function __construct(private string $appPrefix)
    {
    }

    public function getCurrentItem(): object
    {
        return (object)[
            'parsed' => (object)[
                'app_prefix' => $this->appPrefix,
            ],
        ];
    }
}
