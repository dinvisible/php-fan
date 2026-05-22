<?php

declare(strict_types=1);

class SafeSerializerTest extends \PHPUnit\Framework\TestCase
{
    public function testExternalPayloadUsesPrefixedJson(): void
    {
        $payload = \fan\core\adapter\safe_serializer::encodeJson([
            'name' => 'fan',
            'items' => [1, true, null],
        ]);

        $this->assertStringStartsWith(\fan\core\adapter\safe_serializer::JSON_PREFIX, $payload);
        $this->assertSame(
            ['name' => 'fan', 'items' => [1, true, null]],
            \fan\core\adapter\safe_serializer::decodeExternalPayload($payload)
        );
    }

    public function testExternalPayloadReadsLegacyPhpArraysWithoutClasses(): void
    {
        $legacyPayload = \fan\core\adapter\safe_serializer::encodePhpSnapshot([
            'alpha' => ['x' => 1],
            'enabled' => false,
        ]);

        $this->assertSame(
            ['alpha' => ['x' => 1], 'enabled' => false],
            \fan\core\adapter\safe_serializer::decodeExternalPayload($legacyPayload, null)
        );
    }

    public function testExternalPayloadRejectsLegacyObjects(): void
    {
        $errors = [];
        $legacyObject = \fan\core\adapter\safe_serializer::encodePhpSnapshot(new \stdClass());

        $result = \fan\core\adapter\safe_serializer::decodeExternalPayload(
            $legacyObject,
            'fallback',
            static function (string $message) use (&$errors): void {
                $errors[] = $message;
            }
        );

        $this->assertSame('fallback', $result);
        $this->assertSame(['Legacy PHP payload contains object or resource data.'], $errors);
    }

    public function testInternalSnapshotAllowsObjects(): void
    {
        $source = new \stdClass();
        $source->name = 'fan';

        $payload = \fan\core\adapter\safe_serializer::encodePhpSnapshot($source);
        $restored = \fan\core\adapter\safe_serializer::decodePhpSnapshot($payload);

        $this->assertEquals($source, $restored);
    }

    public function testStableKeyNormalizesArrayOrder(): void
    {
        $this->assertSame(
            \fan\core\adapter\safe_serializer::stableKey(['b' => 2, 'a' => 1]),
            \fan\core\adapter\safe_serializer::stableKey(['a' => 1, 'b' => 2])
        );
    }
}
