<?php

declare(strict_types=1);

use fan\core\bootstrap\context;
use fan\core\bootstrap\context_bootstrap_operations;
use fan\core\bootstrap\state;
use PHPUnit\Framework\TestCase;
use fan\core\di\container;


final class ContextBootstrapOperationsTest extends TestCase
{
    public function testOperationsReadFromInjectedContextState(): void
    {
        $state = new state();
        $state->setConfig([
            'bootstrap' => [
                'global_path' => ['log' => '{PROJECT_DIR}/log'],
            ],
            'config_cache' => ['enabled' => true],
        ]);
        $state->setReplacement(['{PROJECT_DIR}' => '/project']);
        $state->setCli(true);
        $context = new context(
            state: $state,
            errorLogger: static fn(string $message, string $logDir): null => null,
            defaultFactoriesFactory: self::defaultFactories()
        );
        $operations = new context_bootstrap_operations($context);

        $this->assertSame('/project/log', $operations->getGlobalPath('log'));
        $this->assertSame('/fallback', $operations->getGlobalPath('missing', '/fallback'));
        $this->assertSame(['enabled' => true], $operations->getConfigCache());
        $this->assertNotSame('', $operations->getPid());
        $this->assertTrue($operations->isCli());
    }

    public function testOperationsLogAndHandleErrorsThroughInjectedContext(): void
    {
        $messages = [];
        $context = new context(
            errorLogger: static function (string $message, string $logDir) use (&$messages): void {
                $messages[] = [$message, $logDir];
            },
            defaultFactoriesFactory: self::defaultFactories()
        );
        $operations = new context_bootstrap_operations($context);

        $operations->logError('plain error');
        $this->assertNull($operations->handleError(123, 'boom', 'file.php', 45, ['ctx' => true]));
        $this->assertTrue($operations->handleError(E_DEPRECATED, 'old'));

        $this->assertSame('plain error', $messages[0][0]);
        $this->assertStringContainsString('Error No 123: boom in file.php on line 45.', $messages[1][0]);
    }

    public function testOperationsRejectBootstrapObjectsBeforeTheyAreInitialized(): void
    {
        $operations = new context_bootstrap_operations(new context(defaultFactoriesFactory: self::defaultFactories()));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Bootstrap context loader is not initialized.');

        $operations->getLoader();
    }

    public function testSourceDoesNotUseStaticBootstrapFacade(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/application/context_bootstrap_operations.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class context_bootstrap_operations', $source);
        $this->assertStringContainsString('public function __construct(private context $context)', $source);
        $this->assertStringContainsString('return $this->context->state();', $source);
        $this->assertStringContainsString('$this->context->logError($message);', $source);
        $this->assertStringNotContainsString('\bootstrap::', $source);
    }

    private static function defaultFactories(): callable
    {
        return static fn(): array => [
            'stateFactory' => static fn(): state => new state(),
            'containerFactory' => static fn(): container => new container(),
            'requestInputFactory' => static fn(): object => new stdClass(),
            'bootstrapRuntimeFactory' => static fn(): object => new stdClass(),
            'zendAutoloaderLoaderFactory' => static fn(): object => new class {
                public function load(string $zendPath): void
                {
                }
            },
            'bootstrapLoaderFileStorageFactory' => static fn(): object => new stdClass(),
            'bootstrapObjectFactory' => static fn(string $class, array $arguments): object => new stdClass(),
            'bootstrapConfigLoader' => static fn(context $context, ?string $configPath = null): null => null,
            'bootstrapErrorHandlerSetup' => static fn(context $context, callable $handler): null => null,
            'phpRuntimeSettingsFactory' => static fn(): object => new stdClass(),
            'errorHandlerRegistrar' => static fn(callable $handler): null => null,
            'errorHandlerSetup' => static fn(callable $handler, string $defaultTimezone): null => null,
            'errorLogger' => static fn(string $message, string $logDir): null => null,
        ];
    }
}
