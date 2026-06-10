<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class safe_serializer_operations
{
    public function __construct(private object $warningCapture)
    {
    }

    public function jsonPayloadEncoder(): callable
    {
        return static fn(mixed $value): string => safe_serializer::encodeJson($value);
    }

    public function phpSnapshotEncoder(): callable
    {
        return static fn(mixed $value): string => safe_serializer::encodePhpSnapshot($value);
    }

    public function phpSnapshotDecoder(): callable
    {
        $warningCapture = $this->warningCapture;

        return static fn(string $payload, mixed $default = null, ?callable $onError = null): mixed => safe_serializer::decodePhpSnapshot($payload, $default, $onError, $warningCapture);
    }

    public function externalPayloadDecoder(): callable
    {
        $warningCapture = $this->warningCapture;

        return static function (
            string $payload,
            mixed $default = null,
            ?callable $onError = null,
            bool $returnOriginalOnLegacyFailure = false
        ) use ($warningCapture): mixed {
            return safe_serializer::decodeExternalPayload(
                $payload,
                $default,
                $onError,
                $returnOriginalOnLegacyFailure,
                $warningCapture
            );
        };
    }

    public function externalPayloadChecker(): callable
    {
        return static fn(string $payload): bool => safe_serializer::isJsonPayload($payload) || safe_serializer::looksLikeLegacyPhpPayload($payload);
    }

    public function jsonPayloadChecker(): callable
    {
        return static fn(string $payload): bool => safe_serializer::isJsonPayload($payload);
    }

    public function stableKeyEncoder(): callable
    {
        return static fn(mixed $identifyer): string => safe_serializer::stableKey($identifyer);
    }
}
