<?php

declare(strict_types=1);

use fan\core\adapter\safe_serializer;
use fan\core\adapter\safe_serializer_operations;
use PHPUnit\Framework\TestCase;

final class SafeSerializerOperationsTest extends TestCase
{
    public function testOperationsExposeSerializerCallables(): void
    {
        $operations = new safe_serializer_operations(new SafeSerializerOperationsPassThroughWarningCapture());
        $payload = ($operations->jsonPayloadEncoder())(['ok' => true]);

        $this->assertStringStartsWith(safe_serializer::JSON_PREFIX, $payload);
        $this->assertSame(['ok' => true], ($operations->externalPayloadDecoder())($payload));
        $this->assertTrue(($operations->jsonPayloadChecker())($payload));
        $this->assertSame(['a' => 1], ($operations->phpSnapshotDecoder())(($operations->phpSnapshotEncoder())(['a' => 1])));
        $this->assertSame(
            ($operations->stableKeyEncoder())(['b' => 2, 'a' => 1]),
            ($operations->stableKeyEncoder())(['a' => 1, 'b' => 2])
        );
    }

    public function testOperationsPassInjectedWarningCaptureToDecoders(): void
    {
        $warnings = [];
        $capture = new SafeSerializerOperationsTestWarningCapture('operation warning');
        $operations = new safe_serializer_operations($capture);

        $result = ($operations->externalPayloadDecoder())(
            'broken payload',
            'fallback',
            static function (string $message) use (&$warnings): void {
                $warnings[] = $message;
            }
        );

        $this->assertSame('fallback', $result);
        $this->assertSame(['operation warning'], $warnings);
        $this->assertSame(1, $capture->calls);
    }

    public function testSourceOwnsSafeSerializerStaticBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/adapter/safe_serializer_operations.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class safe_serializer_operations', $source);
        $this->assertStringContainsString('safe_serializer::encodeJson($value)', $source);
        $this->assertStringContainsString('safe_serializer::stableKey($identifyer)', $source);
        $this->assertStringContainsString('private object $warningCapture', $source);
        $this->assertStringContainsString('$warningCapture', $source);
    }
}

final class SafeSerializerOperationsPassThroughWarningCapture
{
    public function run(callable $operation, ?callable $onWarning = null): mixed
    {
        return $operation();
    }
}

final class SafeSerializerOperationsTestWarningCapture
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
