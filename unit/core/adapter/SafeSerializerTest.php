<?php

declare(strict_types=1);
use fan\core\adapter\safe_serializer;
use PHPUnit\Framework\TestCase;


class SafeSerializerTest extends TestCase
{
    public function testExternalPayloadUsesPrefixedJson(): void
    {
        $payload = safe_serializer::encodeJson([
            'name' => 'fan',
            'items' => [1, true, null],
        ]);

        $this->assertStringStartsWith(safe_serializer::JSON_PREFIX, $payload);
        $this->assertSame(
            ['name' => 'fan', 'items' => [1, true, null]],
            safe_serializer::decodeExternalPayload(
                $payload,
                null,
                null,
                false,
                new SafeSerializerPassThroughWarningCapture()
            )
        );
    }

    public function testExternalPayloadReadsLegacyPhpArraysWithoutClasses(): void
    {
        $legacyPayload = safe_serializer::encodePhpSnapshot([
            'alpha' => ['x' => 1],
            'enabled' => false,
        ]);

        $this->assertSame(
            ['alpha' => ['x' => 1], 'enabled' => false],
            safe_serializer::decodeExternalPayload(
                $legacyPayload,
                null,
                null,
                false,
                new SafeSerializerPassThroughWarningCapture()
            )
        );
    }

    public function testExternalPayloadRejectsLegacyObjects(): void
    {
        $errors = [];
        $legacyObject = safe_serializer::encodePhpSnapshot(new \stdClass());

        $result = safe_serializer::decodeExternalPayload(
            $legacyObject,
            'fallback',
            static function (string $message) use (&$errors): void {
                $errors[] = $message;
            },
            false,
            new SafeSerializerPassThroughWarningCapture()
        );

        $this->assertSame('fallback', $result);
        $this->assertSame(['Legacy PHP payload contains object or resource data.'], $errors);
    }

    public function testLegacyDecodeUsesInjectedWarningCapture(): void
    {
        $warnings = [];
        $capture = new SafeSerializerTestWarningCapture('decode warning');

        $result = safe_serializer::decodeExternalPayload(
            'not serialized',
            'fallback',
            static function (string $message) use (&$warnings): void {
                $warnings[] = $message;
            },
            false,
            $capture
        );

        $this->assertSame('fallback', $result);
        $this->assertSame(['decode warning'], $warnings);
        $this->assertSame(1, $capture->calls);
    }

    public function testSourceDelegatesWarningsToAdapterBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/adapter/safe_serializer.php');

        $this->assertIsString($source);
        $this->assertStringContainsString("throw new \RuntimeException('Warning capture dependency is not configured for safe serializer.');", $source);
        $this->assertStringNotContainsString('defaultWarningCapture', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/warning_capture.php';", $source);
        $this->assertStringNotContainsString('new warning_capture()', $source);
        $this->assertStringNotContainsString('set_error_handler(', $source);
        $this->assertStringNotContainsString('restore_error_handler(', $source);
    }

    public function testInternalSnapshotAllowsObjects(): void
    {
        $source = new \stdClass();
        $source->name = 'fan';

        $payload = safe_serializer::encodePhpSnapshot($source);
        $restored = safe_serializer::decodePhpSnapshot(
            $payload,
            null,
            null,
            new SafeSerializerPassThroughWarningCapture()
        );

        $this->assertEquals($source, $restored);
    }

    public function testStableKeyNormalizesArrayOrder(): void
    {
        $this->assertSame(
            safe_serializer::stableKey(['b' => 2, 'a' => 1]),
            safe_serializer::stableKey(['a' => 1, 'b' => 2])
        );
    }
}

final class SafeSerializerPassThroughWarningCapture
{
    public function run(callable $operation, ?callable $onWarning = null): mixed
    {
        return $operation();
    }
}

final class SafeSerializerTestWarningCapture
{
    public int $calls = 0;

    public function __construct(private string $warning)
    {
    }

    public function run(callable $operation, ?callable $onWarning = null): mixed
    {
        $this->calls++;
        if ($onWarning !== null) {
            $onWarning($this->warning, E_USER_WARNING);
        }

        return false;
    }
}
