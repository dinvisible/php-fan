<?php

declare(strict_types=1);

use fan\core\di\tab_view_parser_factory;
use PHPUnit\Framework\TestCase;
use fan\core\block\base;
use fan\core\view\parser;
use fan\core\view\parser\loader;

final class TabViewParserFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        require_once dirname(__DIR__, 3) . '/core/block/base.php';
        require_once dirname(__DIR__, 3) . '/core/view/parser.php';
        require_once dirname(__DIR__, 3) . '/core/view/parser/loader.php';
    }

    public function testFactoryCreatesDefaultViewParser(): void
    {
        $mainBlock = new TabViewParserFactoryBlockDouble();
        $jsonFactory = static fn(): object => new stdClass();
        $templateFactory = static fn(): object => new stdClass();
        $header = new stdClass();
        $locale = new stdClass();
        $dataLoaderFactory = static fn(): object => new stdClass();
        $delegatedClass = null;
        $delegatedArguments = null;
        $factory = new tab_view_parser_factory(
            static function (string $className, array $arguments) use (&$delegatedClass, &$delegatedArguments): object {
                $delegatedClass = $className;
                $delegatedArguments = $arguments;

                return new $className(...$arguments);
            }
        );

        $parser = $factory(
            TabViewParserFactoryDefaultDouble::class,
            0,
            $mainBlock,
            $jsonFactory,
            null,
            $templateFactory,
            $header,
            $locale,
            $dataLoaderFactory
        );

        $this->assertSame(TabViewParserFactoryDefaultDouble::class, $delegatedClass);
        $this->assertSame([$mainBlock, $jsonFactory, $templateFactory, $header, $locale], $delegatedArguments);
        $this->assertInstanceOf(TabViewParserFactoryDefaultDouble::class, $parser);
        $this->assertSame([$mainBlock, $jsonFactory, $templateFactory, $header, $locale], $parser->dependencies);
    }

    public function testFactoryCreatesDebugViewParserWithDebugService(): void
    {
        $mainBlock = new TabViewParserFactoryBlockDouble();
        $jsonFactory = static fn(): object => new stdClass();
        $debug = new stdClass();
        $templateFactory = static fn(): object => new stdClass();
        $header = new stdClass();
        $locale = new stdClass();
        $dataLoaderFactory = static fn(): object => new stdClass();
        $delegatedClass = null;
        $delegatedArguments = null;
        $factory = new tab_view_parser_factory(
            static function (string $className, array $arguments) use (&$delegatedClass, &$delegatedArguments): object {
                $delegatedClass = $className;
                $delegatedArguments = $arguments;

                return new $className(...$arguments);
            }
        );

        $parser = $factory(
            TabViewParserFactoryDebugDouble::class,
            1,
            $mainBlock,
            $jsonFactory,
            $debug,
            $templateFactory,
            $header,
            $locale,
            $dataLoaderFactory
        );

        $this->assertSame(TabViewParserFactoryDebugDouble::class, $delegatedClass);
        $this->assertSame([$mainBlock, $jsonFactory, $debug, $templateFactory, $header, $locale], $delegatedArguments);
        $this->assertInstanceOf(TabViewParserFactoryDebugDouble::class, $parser);
        $this->assertSame([$mainBlock, $jsonFactory, $debug, $templateFactory, $header, $locale], $parser->dependencies);
    }

    public function testFactoryCreatesLoaderViewParserWithDataLoaderFactory(): void
    {
        $mainBlock = new TabViewParserFactoryBlockDouble();
        $jsonFactory = static fn(): object => new stdClass();
        $templateFactory = static fn(): object => new stdClass();
        $header = new stdClass();
        $locale = new stdClass();
        $dataLoaderFactory = static fn(): object => new stdClass();
        $delegatedClass = null;
        $delegatedArguments = null;
        $factory = new tab_view_parser_factory(
            static function (string $className, array $arguments) use (&$delegatedClass, &$delegatedArguments): object {
                $delegatedClass = $className;
                $delegatedArguments = $arguments;

                return new $className(...$arguments);
            }
        );

        $parser = $factory(
            TabViewParserFactoryLoaderDouble::class,
            0,
            $mainBlock,
            $jsonFactory,
            null,
            $templateFactory,
            $header,
            $locale,
            $dataLoaderFactory
        );

        $this->assertSame(TabViewParserFactoryLoaderDouble::class, $delegatedClass);
        $this->assertSame([$mainBlock, $jsonFactory, $templateFactory, $header, $locale, $dataLoaderFactory], $delegatedArguments);
        $this->assertInstanceOf(TabViewParserFactoryLoaderDouble::class, $parser);
        $this->assertSame([$mainBlock, $jsonFactory, $templateFactory, $header, $locale, $dataLoaderFactory], $parser->dependencies);
    }}

final class TabViewParserFactoryBlockDouble extends base
{
    public function __construct()
    {
    }
}

final class TabViewParserFactoryDefaultDouble extends parser
{
    public array $dependencies;

    public function __construct(
        base $mainBlock,
        ?callable $jsonFactory = null,
        ?callable $templateFactory = null,
        ?object $header = null,
        ?object $locale = null
    ) {
        parent::__construct($mainBlock, $jsonFactory, $templateFactory, $header, $locale);
        $this->dependencies = [$mainBlock, $jsonFactory, $templateFactory, $header, $locale];
    }
}

final class TabViewParserFactoryDebugDouble extends parser
{
    public array $dependencies;

    public function __construct(
        base $mainBlock,
        ?callable $jsonFactory,
        object $debug,
        ?callable $templateFactory = null,
        ?object $header = null,
        ?object $locale = null
    ) {
        parent::__construct($mainBlock, $jsonFactory, $templateFactory, $header, $locale);
        $this->dependencies = [$mainBlock, $jsonFactory, $debug, $templateFactory, $header, $locale];
    }
}

final class TabViewParserFactoryLoaderDouble extends loader
{
    public array $dependencies;

    public function __construct(
        base $mainBlock,
        ?callable $jsonFactory = null,
        ?callable $templateFactory = null,
        ?object $header = null,
        ?object $locale = null,
        ?callable $dataLoaderFactory = null
    ) {
        parent::__construct($mainBlock, $jsonFactory, $templateFactory, $header, $locale, $dataLoaderFactory);
        $this->dependencies = [$mainBlock, $jsonFactory, $templateFactory, $header, $locale, $dataLoaderFactory];
    }
}
