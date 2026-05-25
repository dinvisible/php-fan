<?php

declare(strict_types=1);

use FanTest\_core\exception\TestService;
use fan\core\base\data;
use fan\core\service\config\row;
use fan\project\service\error;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../../mock/_core/base/DataFunctions.php';
require_once __DIR__ . '/../../../mock/_core/exception/ExceptionDoubles.php';
require_once __DIR__ . '/../../../../_core/base/data.php';
require_once __DIR__ . '/../../../../_core/service/config/row.php';

class ServiceConfigRowTest extends TestCase
{
    private object $errorLogger;

    protected function setUp(): void
    {
        error::reset();
        $this->errorLogger = error::instance();
    }

    public function testRootKeysOwnersSourcesAndNestedRowsArePreserved(): void
    {
        $createdKeys = [];
        $shortClassNameResolver = $this->shortClassNameResolver();
        $subDataFactory = null;
        $subDataFactory = function (
            mixed $value,
            int|string|null $key,
            data $superior
        ) use (&$createdKeys, &$subDataFactory, $shortClassNameResolver): row {
            $createdKeys[] = $key;

            return new row(
                $value,
                $key,
                $superior,
                null,
                $subDataFactory,
                shortClassNameResolver: $shortClassNameResolver
            );
        };

        $row = new row([
            'TestService' => [
                'ENABLED' => true,
                'nested' => ['value' => 'source'],
            ],
        ], null, null, null, $subDataFactory, shortClassNameResolver: $this->shortClassNameResolver());
        $service = new TestService();

        $this->assertSame(['TestService', 'nested'], $createdKeys);
        $this->assertSame('', $row->getRootKey());
        $this->assertSame('TestService', $row->get('TestService')->getRootKey());
        $this->assertSame('TestService', $row->get(['TestService', 'nested'])->getRootKey());

        $this->assertSame($row->get('TestService'), $row->get('TestService')->setServiceOwner($service));
        $this->assertSame([$service], $row->get('TestService')->getOwners());
        $this->assertSame([$service], $row->get(['TestService', 'nested'])->getOwners());
        $this->assertSame([
            'TestService' => [
                'ENABLED' => true,
                'nested' => ['value' => 'source'],
            ],
        ], $row->getSources());
    }

    public function testFacadeControlsMutationResetAndMergePriority(): void
    {
        $service = new TestService();
        $row = $this->row([
            'TestService' => [
                'ENABLED' => true,
                'mode' => 'source',
            ],
        ]);
        $branch = $row->get('TestService');
        $branch->setErrorLogger($this->errorLogger);
        $branch->setFacade($service);

        $branch->set('mode', 'external');
        $this->assertSame('source', $branch->get('mode'));
        $this->assertCount(1, $this->errorLogger->messages);

        $service->setConfigValue($branch, 'mode', 'runtime');
        $this->assertSame('runtime', $branch->get('mode'));

        $service->resetConfigValue($branch, 'mode');
        $this->assertSame('source', $branch->get('mode'));

        $service->setConfigValue($branch, 'newKey', 'runtime');
        $service->resetConfigValue($branch, 'newKey');
        $this->assertNull($branch->get('newKey'));

        $service->mergeConfigData($branch, ['mode' => 'blocked', 'added' => 'no-priority'], false);
        $this->assertSame('source', $branch->get('mode'));
        $this->assertSame('no-priority', $branch->get('added'));

        $service->mergeConfigData($branch, ['mode' => 'priority'], true);
        $this->assertSame('priority', $branch->get('mode'));
    }

    public function testSerializationKeepsSourceDataAndRootKey(): void
    {
        $row = $this->row(['alpha' => ['x' => 1]]);
        $row->get('alpha')->getRootKey();

        $restored = unserialize(serialize($row));

        $this->assertInstanceOf(row::class, $restored);
        $this->assertSame(['alpha' => ['x' => 1]], $restored->toArray());
        $this->assertSame('alpha', $restored->get('alpha')->getRootKey());
        $this->assertSame(['alpha' => ['x' => 1]], $restored->getSources());
    }

    public function testManualSnapshotSerializationUsesInjectedCodec(): void
    {
        $encodedState = null;
        $calls = [];
        $encoder = static function (mixed $state) use (&$encodedState, &$calls): string {
            $encodedState = $state;
            $calls[] = ['encode', array_keys($state)];

            return 'row-snapshot';
        };
        $decoder = static function (string $payload, mixed $default = null) use (&$encodedState, &$calls): mixed {
            $calls[] = ['decode', $payload, $default];

            return $encodedState;
        };

        $row = $this->row(['alpha' => ['x' => 1]], $encoder, $decoder);
        $row->get('alpha')->getRootKey();

        $this->assertSame('row-snapshot', $row->serialize());

        $restored = $this->row([], $encoder, $decoder);
        $restored->unserialize('row-snapshot');

        $this->assertSame(['alpha' => ['x' => 1]], $restored->toArray());
        $this->assertSame('alpha', $restored->get('alpha')->getRootKey());
        $this->assertSame([
            ['encode', ['parent', 'srcData', 'rootKey']],
            ['decode', 'row-snapshot', []],
        ], $calls);
    }

    public function testUnsetUsesInjectedServiceExceptionFactory(): void
    {
        $calls = [];
        $service = new TestService();
        $row = $this->row(
            ['TestService' => ['mode' => 'source']],
            null,
            null,
            static function (string $exceptionClass, object $service, string $message, int $code, ?Throwable $previous = null) use (&$calls): Throwable {
                $calls[] = [$exceptionClass, $service, $message, $code, $previous];

                return new RuntimeException($message, $code, $previous);
            }
        );
        $branch = $row->get('TestService');
        $branch->setFacade($service);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('You can\'t unset data for key "mode".');

        try {
            unset($branch->mode);
        } finally {
            $this->assertCount(1, $calls);
            $this->assertSame('\fan\project\exception\service\fatal', $calls[0][0]);
            $this->assertSame($service, $calls[0][1]);
            $this->assertSame('You can\'t unset data for key "mode".', $calls[0][2]);
            $this->assertSame(E_USER_ERROR, $calls[0][3]);
            $this->assertNull($calls[0][4]);
        }
    }

    public function testSourceNoLongerUsesStaticSnapshotSerializer(): void
    {
        $source = file_get_contents(__DIR__ . '/../../../../_core/service/config/row.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('safe_serializer::', $source);
        $this->assertStringContainsString('private function shortClassName(object|string $object): string', $source);
        $this->assertStringContainsString('$name = $this->shortClassName($service);', $source);
        $this->assertStringContainsString('createServiceFatalException(', $source);
        $this->assertStringNotContainsString('get_class_name(', $source);
        $this->assertStringNotContainsString('new \fan\project\exception\service\fatal', $source);
    }

    private function row(
        mixed $data,
        ?callable $snapshotEncoder = null,
        ?callable $snapshotDecoder = null,
        ?callable $serviceExceptionFactory = null,
        ?callable $shortClassNameResolver = null
    ): row {
        $shortClassNameResolver ??= $this->shortClassNameResolver();
        $subDataFactory = null;
        $subDataFactory = function (
            mixed $value,
            int|string|null $key,
            data $superior
        ) use (
            &$subDataFactory,
            $snapshotEncoder,
            $snapshotDecoder,
            $serviceExceptionFactory,
            $shortClassNameResolver
        ): row {
            return new row(
                $value,
                $key,
                $superior,
                null,
                $subDataFactory,
                $snapshotEncoder,
                $snapshotDecoder,
                $serviceExceptionFactory,
                $shortClassNameResolver
            );
        };

        return new row(
            $data,
            null,
            null,
            null,
            $subDataFactory,
            $snapshotEncoder,
            $snapshotDecoder,
            $serviceExceptionFactory,
            $shortClassNameResolver
        );
    }

    private function shortClassNameResolver(): callable
    {
        return static function (object|string $object): string {
            if ($object instanceof TestService) {
                return 'TestService';
            }
            $className = is_object($object) ? get_class($object) : $object;
            $parts = explode('\\', $className);

            return (string)end($parts);
        };
    }
}
