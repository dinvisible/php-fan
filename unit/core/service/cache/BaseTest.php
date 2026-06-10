<?php

declare(strict_types=1);

use fan\core\service\cache;
use fan\core\service\cache\base;
use fan\core\adapter\safe_serializer;
use FanTest\core\SourceFileContractTestCase;
use fan\core\adapter\warning_capture;


class ServiceCacheBaseTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/cache/base.php';

    public function testSetCreatesMetaAndDefersSaveWhenAutoSaveIsDisabled(): void
    {
        $engine = new ServiceCacheBaseEngineDouble(
            new ServiceCacheBaseFacadeDouble(),
            'page',
            'home',
            ['LIFETIME' => 45]
        );

        $this->assertSame($engine, $engine->set(['title' => 'Home'], false));

        $this->assertTrue($engine->isLoaded());
        $this->assertFalse($engine->isSaved());
        $this->assertSame(['title' => 'Home'], $engine->get());
        $this->assertSame('array', $engine->getMeta(false)['data_type']);
        $this->assertSame(45, $engine->getMeta(false)['lifetime']);
        $this->assertSame(0, $engine->saveCalls);
    }

    public function testSavePersistsOnlyDirtyLoadedData(): void
    {
        $engine = new ServiceCacheBaseEngineDouble(new ServiceCacheBaseFacadeDouble(), 'page', 'home', []);

        $engine->save();
        $this->assertSame(0, $engine->saveCalls);

        $engine->set('cached html', false);
        $engine->save();
        $engine->save();

        $this->assertTrue($engine->isSaved());
        $this->assertSame(1, $engine->saveCalls);
    }

    public function testGetLoadsDataLazilyAndReturnsDefaultWhenLoadFails(): void
    {
        $loadedEngine = new ServiceCacheBaseEngineDouble(new ServiceCacheBaseFacadeDouble(), 'page', 'home', []);
        $loadedEngine->loadResult = true;
        $loadedEngine->loadedData = 'from storage';
        $loadedEngine->loadedMeta = [
            'data_type' => 'string',
            'create_date' => date('Y-m-d H:i:s'),
            'lifetime' => 0,
        ];

        $this->assertSame('from storage', $loadedEngine->get('fallback'));
        $this->assertSame('from storage', $loadedEngine->get('fallback'));
        $this->assertSame([false], $loadedEngine->loadCalls);

        $missingEngine = new ServiceCacheBaseEngineDouble(new ServiceCacheBaseFacadeDouble(), 'page', 'missing', []);
        $missingEngine->loadResult = false;

        $this->assertSame('fallback', $missingEngine->get('fallback'));
        $this->assertSame([false], $missingEngine->loadCalls);
    }

    public function testExtraMetaIsSavedImmediately(): void
    {
        $engine = new ServiceCacheBaseEngineDouble(new ServiceCacheBaseFacadeDouble(), 'page', 'home', []);

        $this->assertSame($engine, $engine->setExtraMeta('etag', 'abc123'));

        $this->assertSame('abc123', $engine->getExtraMeta('etag'));
        $this->assertSame(1, $engine->saveCalls);
    }

    public function testDeleteClearsPayloadAndMeta(): void
    {
        $engine = new ServiceCacheBaseEngineDouble(new ServiceCacheBaseFacadeDouble(), 'page', 'home', []);
        $engine->set(['value' => 1], false);

        $this->assertSame($engine, $engine->delete());

        $this->assertTrue($engine->isLoaded());
        $this->assertTrue($engine->isSaved());
        $this->assertSame('fallback', $engine->get('fallback'));
        $this->assertSame([], $engine->getMeta(false));
        $this->assertSame(1, $engine->deleteCalls);
    }

    public function testDecodePayloadLogsThroughInjectedErrorLogger(): void
    {
        $logger = new ServiceCacheBaseErrorLoggerDouble();
        $engine = new ServiceCacheBaseEngineDouble(
            new ServiceCacheBaseFacadeDouble(),
            'page',
            'home',
            [],
            $logger,
            null,
            null,
            static fn(
                string $payload,
                mixed $default = null,
                ?callable $onError = null,
                bool $returnOriginalOnLegacyFailure = false
            ): mixed => safe_serializer::decodeExternalPayload(
                $payload,
                $default,
                $onError,
                $returnOriginalOnLegacyFailure,
                new warning_capture()
            )
        );

        $this->assertNull($engine->exposeDecodePayload('not-json', 'Broken payload'));
        $this->assertSame(
            [['unserialize(): Error at offset 0 of 8 bytes', 'Broken payload', '', true, false]],
            $logger->messages
        );
    }

    public function testPayloadCodecDependenciesAreInjected(): void
    {
        $calls = [];
        $engine = new ServiceCacheBaseEngineDouble(
            new ServiceCacheBaseFacadeDouble(),
            'page',
            'home',
            [],
            new ServiceCacheBaseErrorLoggerDouble(),
            null,
            static function (mixed $value) use (&$calls): string {
                $calls[] = ['encode', $value];

                return 'encoded:' . (string)$value;
            },
            static function (
                string $payload,
                mixed $default = null,
                ?callable $onError = null,
                bool $returnOriginalOnLegacyFailure = false
            ) use (&$calls): mixed {
                $calls[] = ['decode', $payload, $default, $returnOriginalOnLegacyFailure];

                return ['decoded' => $payload];
            },
            static function (string $payload) use (&$calls): bool {
                $calls[] = ['is-json', $payload];

                return $payload === 'json';
            }
        );

        $this->assertSame('encoded:payload', $engine->exposeEncodePayload('payload'));
        $this->assertSame(['decoded' => 'payload'], $engine->exposeDecodePayload('payload', 'Payload decode'));
        $this->assertTrue($engine->exposeIsJsonPayload('json'));
        $this->assertSame([
            ['encode', 'payload'],
            ['decode', 'payload', null, false],
            ['is-json', 'json'],
        ], $calls);
    }

    public function testMissingPayloadEncoderFailsAtUseTime(): void
    {
        $engine = new ServiceCacheBaseEngineDouble(new ServiceCacheBaseFacadeDouble(), 'page', 'home', []);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cache payload encoder is not configured for cache engine.');

        $engine->exposeEncodePayload('payload');
    }

    public function testMissingPayloadDecoderFailsAtUseTime(): void
    {
        $engine = new ServiceCacheBaseEngineDouble(new ServiceCacheBaseFacadeDouble(), 'page', 'home', []);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cache payload decoder is not configured for cache engine.');

        $engine->exposeDecodePayload('payload', 'Payload decode');
    }

    public function testMissingJsonPayloadCheckerFailsAtUseTime(): void
    {
        $engine = new ServiceCacheBaseEngineDouble(new ServiceCacheBaseFacadeDouble(), 'page', 'home', []);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cache JSON payload checker is not configured for cache engine.');

        $engine->exposeIsJsonPayload('payload');
    }

    public function testAddMetaUsesInjectedCacheFatalExceptionFactory(): void
    {
        $facade = new ServiceCacheBaseFacadeDouble();
        $engine = new ServiceCacheBaseEngineDouble($facade, 'page', 'home', []);

        try {
            $engine->addMeta('bad-meta');
            $this->fail('Expected cache facade to create the fatal exception.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Incorrect cache Meta-date.', $exception->getMessage());
        }

        $this->assertSame([
            ['Incorrect cache Meta-date.', E_USER_ERROR, null],
        ], $facade->fatalExceptionCalls);
    }

    public function testSourceNoLongerUsesFacadeAsServiceLocator(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('protected function createCacheFatalException(', $source);
        $this->assertStringContainsString('$this->facade->createCacheFatalException($message, $code, $previous)', $source);
        $this->assertStringNotContainsString('getContainerService(', $source);
        $this->assertStringNotContainsString('safe_serializer::', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
    }
}

final class ServiceCacheBaseEngineDouble extends base
{
    public int $saveCalls = 0;

    public int $deleteCalls = 0;

    public bool $loadResult = false;

    public mixed $loadedData = null;

    public array $loadedMeta = [];

    public array $loadCalls = [];

    protected function _loadData(bool $loadMetaOnly): bool
    {
        $this->loadCalls[] = $loadMetaOnly;
        if (!$this->loadResult) {
            return false;
        }

        $this->metaData = $this->loadedMeta;
        if (!$loadMetaOnly) {
            $this->data = $this->loadedData;
        }

        return true;
    }

    protected function _saveData(): static
    {
        $this->saveCalls++;

        return $this;
    }

    protected function _deleteData(): static
    {
        $this->deleteCalls++;

        return parent::_deleteData();
    }

    public function exposeDecodePayload(string $data, string $errorTitle): mixed
    {
        return $this->_decodePayload($data, $errorTitle);
    }

    public function exposeEncodePayload(mixed $value): string
    {
        return $this->_encodePayload($value);
    }

    public function exposeIsJsonPayload(string $data): bool
    {
        return $this->_isJsonPayload($data);
    }
}

final class ServiceCacheBaseFacadeDouble extends cache
{
    public array $fatalExceptionCalls = [];

    public function __construct()
    {
    }

    public function getExceptionLogType(): string
    {
        return 'nothing';
    }

    public function createCacheFatalException(string $message, int $code = E_USER_ERROR, ?Throwable $previous = null): Throwable
    {
        $this->fatalExceptionCalls[] = [$message, $code, $previous];

        return new RuntimeException($message, $code, $previous);
    }
}

final class ServiceCacheBaseErrorLoggerDouble
{
    public array $messages = [];

    public function logErrorMessage(
        string $message,
        string $title = '',
        string $note = '',
        bool $fixPosition = false,
        bool $allowDebug = true,
    ): void {
        $this->messages[] = [$message, $title, $note, $fixPosition, $allowDebug];
    }
}
