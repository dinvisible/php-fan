<?php

declare(strict_types=1);

use fan\core\view\keeper;
use fan\core\view\router\loader;
use fan\core\view\router\loader_state;
use FanTest\core\SourceFileContractTestCase;
use fan\core\block\base;
use fan\core\view\keeper\loader\json;
use fan\core\view\keeper\loader\text;
use fan\core\view\router;


class ViewRouterLoaderTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/view/router/loader.php';

    public function testLoaderRouterHasExpectedKeeperConfiguration(): void
    {
        $router = new ViewRouterLoaderProbe(new ViewRouterLoaderBlockDouble());

        $this->assertCount(3, $router);
        $this->assertTrue(isset($router['json']));
        $this->assertTrue(isset($router['html']));
        $this->assertTrue(isset($router['text']));
        $this->assertSame('html', $this->property($router, 'defaultKey'));
    }

    public function testLoaderRouterNoLongerStoresSharedKeepersInStaticProperties(): void
    {
        $source = $this->sourceCode();

        $this->assertStringNotContainsString('protected static ?\fan\core\view\keeper\loader\json $json', $source);
        $this->assertStringNotContainsString('protected static ?\fan\core\view\keeper\loader\text $text', $source);
        $this->assertStringNotContainsString('self::$json', $source);
        $this->assertStringNotContainsString('self::$text', $source);
    }

    public function testLoaderStateDependencyIsRequired(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('Loader state is not configured for loader view router.', $source);
        $this->assertStringNotContainsString('new loader_state()', $source);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Loader state is not configured for loader view router.');

        new loader(new ViewRouterLoaderBlockDouble());
    }

    public function testJsonAccessorsDelegateToJsonKeeper(): void
    {
        $router = new ViewRouterLoaderProbe(new ViewRouterLoaderBlockDouble());

        $this->assertSame($router, $router->setJson('answer', 42));
        $this->assertSame(42, $router->getJson('answer'));
        $this->assertSame(['answer' => 42], $router->getJson());
    }

    public function testTextAccessorsDelegateToTextKeeper(): void
    {
        $router = new ViewRouterLoaderProbe(new ViewRouterLoaderBlockDouble());

        $this->assertSame($router, $router->setText('Hello'));
        $this->assertSame($router, $router->set('text', ' world'));

        $this->assertSame('Hello world', $router->getText());
    }

    public function testIsFullRewriteIsTrueForJsonAndTextButFalseForHtml(): void
    {
        $router = new ViewRouterLoaderProbe(new ViewRouterLoaderBlockDouble());
        $json = $router->jsonKeeper();
        $text = $router->textKeeper();
        $html = new ViewRouterLoaderKeeperDouble();

        $router->setKeeper('json', $json);
        $router->setKeeper('text', $text);
        $router->setKeeper('html', $html);

        $this->assertTrue($router->isFullRewrite($json));
        $this->assertTrue($router->isFullRewrite($text));
        $this->assertFalse($router->isFullRewrite($html));
        $this->assertFalse($router->isFullRewrite(new ViewRouterLoaderKeeperDouble()));
    }

    public function testLoaderRoutersShareInjectedStateInsteadOfStaticKeepers(): void
    {
        $state = new loader_state(
            static fn(loader $router): ViewRouterLoaderJsonKeeperDouble => new ViewRouterLoaderJsonKeeperDouble(),
            static fn(loader $router): ViewRouterLoaderTextKeeperDouble => new ViewRouterLoaderTextKeeperDouble()
        );
        $first = new loader(new ViewRouterLoaderBlockDouble(), $state);
        $second = new loader(new ViewRouterLoaderBlockDouble(), $state);

        $this->assertSame($first, $first->setJson('answer', 42));
        $this->assertSame(42, $second->getJson('answer'));
        $this->assertSame($first, $first->setText('Hello'));
        $this->assertSame('Hello', $second->getText());
    }

    private function property(loader $router, string $name): mixed
    {
        $property = new ReflectionProperty(router::class, $name);
        return $property->getValue($router);
    }
}

final class ViewRouterLoaderProbe extends loader
{
    private ViewRouterLoaderJsonKeeperDouble $jsonDouble;
    private ViewRouterLoaderTextKeeperDouble $textDouble;

    public function __construct(base $block)
    {
        parent::__construct($block, new loader_state());
        $this->jsonDouble = new ViewRouterLoaderJsonKeeperDouble();
        $this->textDouble = new ViewRouterLoaderTextKeeperDouble();
    }

    public function jsonKeeper(): ViewRouterLoaderJsonKeeperDouble
    {
        return $this->_getJsonKeeper();
    }

    public function textKeeper(): ViewRouterLoaderTextKeeperDouble
    {
        return $this->_getTextKeeper();
    }

    public function setKeeper(string $key, keeper $keeper): void
    {
        $property = new ReflectionProperty(router::class, 'keepers');
        $keepers = $property->getValue($this);
        $keepers[$key] = $keeper;
        $property->setValue($this, $keepers);
    }

    protected function _getJsonKeeper(): json
    {
        return $this->jsonDouble;
    }

    protected function _getTextKeeper(): text
    {
        return $this->textDouble;
    }
}

final class ViewRouterLoaderJsonKeeperDouble extends json
{
    private array $values = [];

    public function __construct()
    {
    }

    public function set(mixed $key, mixed $value, bool $rewriteExisting = true, ?bool $convArray = null): static
    {
        $this->values[(string)$key] = $value;

        return $this;
    }

    public function get(mixed $key = null, mixed $default = null, bool $logError = true): mixed
    {
        return $key === null ? $this->values : ($this->values[(string)$key] ?? $default);
    }
}

final class ViewRouterLoaderTextKeeperDouble extends text
{
    private string $text = '';

    public function __construct()
    {
    }

    public function set(mixed $key, mixed $value, bool $rewriteExisting = true, ?bool $convArray = null): static
    {
        $this->text .= (string)$value;

        return $this;
    }

    public function __toString(): string
    {
        return $this->text;
    }
}

final class ViewRouterLoaderKeeperDouble extends keeper
{
    public function __construct()
    {
    }
}

final class ViewRouterLoaderBlockDouble extends base
{
    public function __construct()
    {
    }
}
