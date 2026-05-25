<?php

declare(strict_types=1);

use fan\core\di\block_exception_factory;
use PHPUnit\Framework\TestCase;
use fan\core\block\base;

final class BlockExceptionFactoryTest extends TestCase
{
    public function testFactoryCreatesBlockExceptionWithRuntimeArguments(): void
    {
        $block = (new ReflectionClass(BlockExceptionFactoryBlockProbe::class))->newInstanceWithoutConstructor();
        $previous = new Exception('previous');
        $delegatedClass = null;
        $delegatedArguments = null;
        $factory = new block_exception_factory(
            configuredServiceFactory: static function (string $className, array $arguments) use (&$delegatedClass, &$delegatedArguments): object {
                $delegatedClass = $className;
                $delegatedArguments = $arguments;

                return new $className(...$arguments);
            }
        );

        $exception = $factory(BlockExceptionFactoryProbe::class, $block, 'broken block', 503, $previous);

        $this->assertSame(BlockExceptionFactoryProbe::class, $delegatedClass);
        $this->assertSame([$block, 'broken block', 503, $previous, null, null, null, null, null], $delegatedArguments);
        $this->assertInstanceOf(BlockExceptionFactoryProbe::class, $exception);
        $this->assertSame($block, $exception->block);
        $this->assertSame('broken block', $exception->getMessage());
        $this->assertSame(503, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testFactoryPassesInjectedExceptionDependencies(): void
    {
        $block = (new ReflectionClass(BlockExceptionFactoryBlockProbe::class))->newInstanceWithoutConstructor();
        $databaseConnections = new stdClass();
        $runtimeLogger = new stdClass();
        $requestService = new stdClass();
        $errorService = new stdClass();
        $headerWriter = new stdClass();
        $factory = new block_exception_factory(
            static fn(string $className, array $arguments): object => new $className(...$arguments),
            $databaseConnections,
            $runtimeLogger,
            $requestService,
            $errorService,
            $headerWriter
        );

        $exception = $factory(BlockExceptionFactoryProbe::class, $block, 'broken block', 503);

        $this->assertSame(
            [$databaseConnections, $runtimeLogger, $requestService, $errorService, $headerWriter],
            $exception->exceptionDependencies
        );
    }}

final class BlockExceptionFactoryBlockProbe extends base
{
}

final class BlockExceptionFactoryProbe extends RuntimeException
{
    public array $exceptionDependencies = [];

    public function __construct(
        public base $block,
        string $message,
        int $code,
        ?Exception $previous = null,
        ?object $exceptionDatabaseConnections = null,
        ?object $exceptionRuntimeLogger = null,
        ?object $exceptionRequestService = null,
        ?object $exceptionErrorService = null,
        ?object $exceptionHeaderWriter = null
    ) {
        $this->exceptionDependencies = [
            $exceptionDatabaseConnections,
            $exceptionRuntimeLogger,
            $exceptionRequestService,
            $exceptionErrorService,
            $exceptionHeaderWriter,
        ];
        parent::__construct($message, $code, $previous);
    }
}
