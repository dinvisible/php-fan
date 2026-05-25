<?php

declare(strict_types=1);

use fan\core\block\root\html;
use FanTest\_core\SourceFileContractTestCase;

if (!defined('BASE_DIR')) {
    define('BASE_DIR', dirname(__DIR__, 4));
}

class BlockRootHtmlTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/block/root/html.php';

    public function testTitleAndMetaTagsAreStoredWithoutDuplicates(): void
    {
        $block = new BlockRootHtmlProbe();

        $this->assertSame($block, $block->setTitle('First'));
        $this->assertSame('First', $block->getTitle());
        $block->setTitle('Second', true);
        $this->assertSame('First', $block->getTitle());

        $block->setMetaTag(['name' => 'description', 'content' => 'Demo']);
        $block->setMetaTag(['name' => 'description', 'content' => 'Demo']);

        $this->assertSame([
            ['name' => 'description', 'content' => 'Demo'],
        ], $block->getMetaTag());
    }

    public function testInvalidMetaTagIsReportedThroughInjectedErrorLogWriter(): void
    {
        $logger = new BlockRootHtmlErrorLogWriterDouble();
        $block = new BlockRootHtmlProbe(errorLogWriter: $logger);

        $block->setMetaTag('broken');

        $this->assertSame([], $block->getMetaTag());
        $this->assertSame([['Incorrect value for meta-tag.', 0, null]], $logger->writes);
    }

    public function testExternalCssAndJsAreNormalizedThroughTabUriAndDeduplicated(): void
    {
        $block = new BlockRootHtmlProbe();

        $this->assertSame($block, $block->setExternalCss('/css/app.css'));
        $block->setExternalCss('/css/app.css');
        $this->assertSame(['/uri//css/app.css/css'], $block->externalCss()['style']['all']);

        $this->assertSame($block, $block->setExternalJs('/js/app.js', 'body'));
        $block->setExternalJs('/js/app.js', 'body');
        $this->assertSame(['/uri//js/app.js/js'], $block->externalJs()['body']);
    }

    public function testEmbedCssAndJsAccumulateByMediaAndPosition(): void
    {
        $json = new BlockRootHtmlJsonDouble();
        $block = new BlockRootHtmlProbe(json: $json);

        $this->assertSame($block, $block->setEmbedCss('body{}'));
        $block->setEmbedCss('body{}');
        $this->assertSame(['all' => 'body{}'], $block->embedCss());

        $this->assertSame($block, $block->setEmbedJs('console.log(1);', 'body', 1, false));
        $this->assertSame('try{console.log(1);}catch(e){}', $block->embedJs()['body'][2]);

        $this->assertSame($block, $block->setEmbedJs(['boot', ['id' => 7]], 'head', 0, false));
        $this->assertSame([['id' => 7]], $json->encoded);
        $this->assertStringContainsString('boot({"id":7});', $block->embedJs()['head'][1]);
    }

    public function testInvalidEmbedCssIsReportedThroughInjectedErrorLogWriter(): void
    {
        $logger = new BlockRootHtmlErrorLogWriterDouble();
        $block = new BlockRootHtmlProbe(errorLogWriter: $logger);

        $this->assertNull($block->setEmbedCss(new stdClass()));
        $this->assertNull($block->setEmbedCss('body{}', 'invalid'));

        $this->assertSame([
            ['Incorrect value for Embed Css.', 0, null],
            ['Incorrect Media type of CSS: "invalid".', 0, null],
        ], $logger->writes);
    }

    public function testSetLinkTagAppendsOptionalTitle(): void
    {
        $block = new BlockRootHtmlProbe();

        $this->assertSame($block, $block->setLinkTag('alternate', 'application/rss+xml', '/feed', 'Feed'));

        $this->assertSame([
            ['rel' => 'alternate', 'type' => 'application/rss+xml', 'href' => '/feed', 'title' => 'Feed'],
        ], $block->view->get('tagLink'));
    }

    public function testRunAfterInitUsesInjectedApplicationForFallbackTitle(): void
    {
        $app = new BlockRootHtmlApplicationDouble();
        $block = new BlockRootHtmlProbe(application: $app);

        $block->runAfterInit();

        $this->assertSame('Project | Admin', $block->getTitle());
    }

    public function testRunAfterInitUsesInjectedShortClassNameResolverForFallbackTitle(): void
    {
        $app = new BlockRootHtmlApplicationDouble();
        $main = new BlockRootHtmlMainBlockDouble();
        $classNameCalls = [];
        $block = new BlockRootHtmlProbe(
            application: $app,
            mainBlock: $main,
            shortClassNameResolver: static function (object|string $object) use (&$classNameCalls): string {
                $classNameCalls[] = $object;

                return 'main-short';
            }
        );

        $block->runAfterInit();

        $this->assertSame('Project | Admin | main-short', $block->getTitle());
        $this->assertSame([$main], $classNameCalls);
    }

    public function testEmbedCssByMetaUsesInjectedArrayLikeChecker(): void
    {
        $checks = [];
        $meta = new ArrayObject(['all' => 'body{}']);
        $block = new BlockRootHtmlProbe(
            arrayLikeChecker: static function (mixed $value) use (&$checks, $meta): bool {
                $checks[] = $value;

                return $value === $meta;
            }
        );

        $this->assertSame($block, $block->setEmbedCssByMeta($meta));

        $this->assertSame([$meta], $checks);
        $this->assertSame(['all' => 'body{}'], $block->embedCss());
    }

    public function testModalWindowUsesInjectedTemplateService(): void
    {
        $templateService = new BlockRootHtmlTemplateServiceDouble();
        $fileStorage = new BlockRootHtmlFileStorageDouble();
        $templatePath = tempnam(sys_get_temp_dir(), 'php-fan-modal-');
        $this->assertIsString($templatePath);
        file_put_contents($templatePath, 'template');
        $fileStorage->files[$templatePath] = 'template';

        try {
            $block = new BlockRootHtmlProbe(template: $templateService, fileStorage: $fileStorage);
            $block->setModalWindow($templatePath, ['title' => 'Modal'], null, null);

            $this->assertSame([[$templatePath, null, $block]], $templateService->getCalls);
            $this->assertSame([['title', 'Modal']], $templateService->template->assignCalls);
            $block->preOutput();
            $this->assertSame('modal-html', $block->view->get('modal_win'));
        } finally {
            unlink($templatePath);
        }
    }

    public function testExternalJsIncludesAreReadThroughInjectedFileStorage(): void
    {
        $fileStorage = new BlockRootHtmlFileStorageDouble();
        $fileStorage->files[BASE_DIR . '/uri//js/app.js/js'] = "/**include\n/js/vendor.js;\n*/";
        $block = new BlockRootHtmlProbe(fileStorage: $fileStorage);

        $block->setExternalJs('/js/app.js');

        $this->assertSame([
            BASE_DIR . '/uri//js/app.js/js',
            BASE_DIR . '/js/vendor.js',
        ], $fileStorage->readableChecks);
        $this->assertSame(['/js/vendor.js', '/uri//js/app.js/js'], $block->externalJs()['head']);
    }

    public function testPreOutputUsesInjectedObfuscators(): void
    {
        $block = new BlockRootHtmlProbe(
            obfuscators: [
                'css' => new BlockRootHtmlObfuscatorDouble('css'),
                'js' => new BlockRootHtmlObfuscatorDouble('js'),
            ],
        );

        $block->setExternalCss('/css/app.css');
        $block->setExternalJs('/js/app.js');
        $block->preOutput();

        $this->assertSame(['css' => $block->externalCss()], $block->view->get('externalCSS'));
        $this->assertSame(['js' => $block->externalJs()], $block->view->get('externalJS'));
    }

    public function testSourceNoLongerCallsContainerServiceDirectly(): void
    {
        $this->assertStringNotContainsString('containerService(', $this->sourceCode());
    }

    public function testSourceUsesInjectedErrorLogWriter(): void
    {
        $source = $this->sourceCode();

        $this->assertStringNotContainsString('error_log(', $source);
        $this->assertStringContainsString('$this->errorLogWriter()->write(', $source);
    }

    public function testSourceUsesInjectedFileStorage(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('private ?object $rootHtmlFileStorage = null;', $source);
        $this->assertStringContainsString('$this->rootHtmlFileStorage()->isFile($filePath)', $source);
        $this->assertStringContainsString('$this->rootHtmlFileStorage()->read($jsFilePath)', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:is_file|is_readable|file_get_contents)\s*\(/',
            $source
        );
    }

    public function testSourceUsesInjectedHelperBoundaries(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('private \Closure $shortClassNameResolver;', $source);
        $this->assertStringContainsString('private \Closure $arrayLikeChecker;', $source);
        $this->assertStringContainsString('$title .= \' | \' . $this->shortClassName($main);', $source);
        $this->assertStringContainsString('if ($this->isArrayLike($meta)) {', $source);
        $this->assertStringNotContainsString('get_class_name(', $source);
        $this->assertStringNotContainsString('is_array_alt(', $source);
    }
}

final class BlockRootHtmlProbe extends html
{
    public BlockRootHtmlViewDouble $view;

    public function __construct(
        private ?BlockRootHtmlApplicationDouble $application = null,
        private ?BlockRootHtmlJsonDouble $json = null,
        private ?BlockRootHtmlTemplateServiceDouble $template = null,
        private array $obfuscators = [],
        ?BlockRootHtmlErrorLogWriterDouble $errorLogWriter = null,
        ?BlockRootHtmlFileStorageDouble $fileStorage = null,
        private ?object $mainBlock = null,
        ?callable $shortClassNameResolver = null,
        ?callable $arrayLikeChecker = null,
    ) {
        $this->view = new BlockRootHtmlViewDouble();
        $this->tab = new BlockRootHtmlTabDouble();
        $dependencies = ['rootHtmlFileStorage' => $fileStorage ?? new BlockRootHtmlFileStorageDouble()];
        if ($errorLogWriter !== null) {
            $dependencies['errorLogWriter'] = $errorLogWriter;
        }
        if ($shortClassNameResolver !== null) {
            $dependencies['shortClassNameResolver'] = $shortClassNameResolver;
        }
        if ($arrayLikeChecker !== null) {
            $dependencies['arrayLikeChecker'] = $arrayLikeChecker;
        }
        $this->setBlockDependencies($dependencies);
    }

    public function externalCss(): array
    {
        return $this->externalCSS;
    }

    public function externalJs(): array
    {
        return $this->externalJS;
    }

    public function embedCss(): array
    {
        return $this->embedCSS;
    }

    public function embedJs(): array
    {
        return $this->embedJS;
    }

    public function preOutput(): void
    {
        $this->_preOutput();
    }

    protected function _getBlock(string $blockName, bool $allowException = true): ?object
    {
        return $blockName === 'main' ? $this->mainBlock : null;
    }

    protected function applicationService(): object
    {
        return $this->application ??= new BlockRootHtmlApplicationDouble();
    }

    protected function jsonService(mixed ...$arguments): object
    {
        return $this->json ??= new BlockRootHtmlJsonDouble();
    }

    protected function templateService(mixed ...$arguments): object
    {
        return $this->template ??= new BlockRootHtmlTemplateServiceDouble();
    }

    protected function obfuscatorService(string $type): object
    {
        return $this->obfuscators[$type] ??= new BlockRootHtmlObfuscatorDouble($type);
    }
}

final class BlockRootHtmlViewDouble implements ArrayAccess
{
    private array $data = ['title' => null];

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }
}

final class BlockRootHtmlMainBlockDouble
{
    public function getMeta(string $key, mixed $default = null): mixed
    {
        return $default;
    }
}

final class BlockRootHtmlTabDouble
{
    public function getURI(string $urn = '', string $type = 'link', mixed $addSid = null, mixed $protocol = null): string
    {
        return '/uri/' . $urn . '/' . $type;
    }

    public function isDebugAllowed(): bool
    {
        return false;
    }
}

final class BlockRootHtmlApplicationDouble
{
    public function getCoreVersion(): string
    {
        return 'core-version';
    }

    public function getConfig(string $key): string
    {
        return $key === 'PROJECT_NAME' ? 'Project' : '';
    }

    public function getAppName(): string
    {
        return 'Admin';
    }
}

final class BlockRootHtmlJsonDouble
{
    public array $encoded = [];

    public function encode(mixed $value): string
    {
        $this->encoded[] = $value;

        return json_encode($value, JSON_THROW_ON_ERROR);
    }
}

final class BlockRootHtmlErrorLogWriterDouble
{
    public array $writes = [];

    public function write(string $message, int $messageType = 0, ?string $destination = null): bool
    {
        $this->writes[] = [$message, $messageType, $destination];

        return true;
    }
}

final class BlockRootHtmlTemplateServiceDouble
{
    public BlockRootHtmlTemplateDouble $template;

    public array $getCalls = [];

    public function __construct()
    {
        $this->template = new BlockRootHtmlTemplateDouble();
    }

    public function get(string $template, mixed $parentClass, object $block): BlockRootHtmlTemplateDouble
    {
        $this->getCalls[] = [$template, $parentClass, $block];

        return $this->template;
    }
}

final class BlockRootHtmlTemplateDouble
{
    public array $assignCalls = [];

    public function assign(string $key, mixed $value): void
    {
        $this->assignCalls[] = [$key, $value];
    }

    public function fetch(): string
    {
        return 'modal-html';
    }
}

final class BlockRootHtmlFileStorageDouble
{
    public array $files = [];

    public array $readableChecks = [];

    public function isFile(string $path): bool
    {
        return array_key_exists($path, $this->files) || is_file($path);
    }

    public function isReadable(string $path): bool
    {
        $this->readableChecks[] = $path;

        return array_key_exists($path, $this->files) || is_readable($path);
    }

    public function read(string $path): string|false
    {
        return $this->files[$path] ?? (is_readable($path) ? file_get_contents($path) : false);
    }
}

final class BlockRootHtmlObfuscatorDouble
{
    public function __construct(private string $type)
    {
    }

    public function getNewList(array $items): array
    {
        return [$this->type => $items];
    }
}
