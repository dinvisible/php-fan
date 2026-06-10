<?php

declare(strict_types=1);

use fan\core\di\matcher_item_factory;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use fan\project\service\matcher\item;


#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class MatcherItemFactoryTest extends TestCase
{
    public function testFactoryCreatesProjectMatcherItemWithDependencies(): void
    {
        if (!class_exists('fan\project\service\matcher\item', false)) {
            eval('
                namespace fan\project\service\matcher;

                class item
                {
                    public array $dependencies;

                    public function __construct(
                        public int $index,
                        ?object $input,
                        ?object $runtime,
                        ?object $locale,
                        ?object $application,
                        ?object $routeFileStorage,
                        ?callable $componentFactory,
                        ?callable $serviceExceptionFactory = null,
                        ?callable $fatalExceptionFactory = null
                    ) {
                        $this->dependencies = [$input, $runtime, $locale, $application, $routeFileStorage, $componentFactory, $serviceExceptionFactory, $fatalExceptionFactory];
                    }
                }
            ');
        }

        $input = new stdClass();
        $runtime = new stdClass();
        $locale = new stdClass();
        $application = new stdClass();
        $routeFileStorage = new stdClass();
        $componentFactory = static fn(): object => new stdClass();
        $serviceExceptionFactory = static fn(): Throwable => new RuntimeException('service fatal');
        $fatalExceptionFactory = static fn(): Throwable => new RuntimeException('fatal');

        $item = (new matcher_item_factory($fatalExceptionFactory))(
            7,
            $input,
            $runtime,
            $locale,
            $application,
            $routeFileStorage,
            $componentFactory,
            $serviceExceptionFactory
        );

        $this->assertInstanceOf(item::class, $item);
        $this->assertSame(7, $item->index);
        $this->assertSame([$input, $runtime, $locale, $application, $routeFileStorage, $componentFactory, $serviceExceptionFactory, $fatalExceptionFactory], $item->dependencies);
    }}
