<?php

declare(strict_types=1);

use fan\core\di\tab_delegate_factory;
use PHPUnit\Framework\TestCase;

final class TabDelegateFactoryTest extends TestCase
{
    public function testFactoryCreatesDelegateWithTabDependencies(): void
    {
        $matcher = new stdClass();
        $request = new stdClass();
        $locale = new stdClass();
        $sessionFactory = static fn(): object => new stdClass();
        $input = new stdClass();
        $arrayValueReader = static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
        $delegatedClass = null;
        $delegatedArguments = null;
        $factory = new tab_delegate_factory(
            static function (string $className, array $arguments) use (&$delegatedClass, &$delegatedArguments): object {
                $delegatedClass = $className;
                $delegatedArguments = $arguments;

                return new $className(...$arguments);
            }
        );
        $delegate = $factory(
            TabDelegateFactoryDelegateDouble::class,
            $matcher,
            $request,
            $locale,
            $sessionFactory,
            $input,
            $arrayValueReader
        );

        $this->assertSame(TabDelegateFactoryDelegateDouble::class, $delegatedClass);
        $this->assertSame([$matcher, $request, $locale, $sessionFactory, $input, $arrayValueReader], $delegatedArguments);
        $this->assertInstanceOf(TabDelegateFactoryDelegateDouble::class, $delegate);
        $this->assertSame([$matcher, $request, $locale, $sessionFactory, $input, $arrayValueReader], $delegate->dependencies);
    }}

final class TabDelegateFactoryDelegateDouble
{
    public array $dependencies;

    public function __construct(
        object $matcher,
        object $request,
        object $locale,
        ?callable $sessionFactory,
        object $input,
        callable $arrayValueReader
    ) {
        $this->dependencies = [$matcher, $request, $locale, $sessionFactory, $input, $arrayValueReader];
    }
}
