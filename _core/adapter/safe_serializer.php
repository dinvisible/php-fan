<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class safe_serializer
{
    public const JSON_PREFIX = 'php-fan-json:';

    /**
     * Encodes external payloads with a framework prefix so legacy PHP payloads
     * can still be detected and migrated during reads.
     */
    public static function encodeJson(mixed $value): string
    {
        if (self::hasUnsupportedJsonValue($value)) {
            throw new \InvalidArgumentException('Payload contains data unsupported by JSON.');
        }

        return self::JSON_PREFIX . json_encode(
            $value,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
    }

    public static function isJsonPayload(string $payload): bool
    {
        return str_starts_with($payload, self::JSON_PREFIX);
    }

    public static function decodeJsonPayload(string $payload): mixed
    {
        return json_decode(
            substr($payload, strlen(self::JSON_PREFIX)),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    /**
     * Decodes new JSON payloads and safely falls back to legacy PHP serialized
     * external data without hydrating classes.
     */
    public static function decodeExternalPayload(
        string $payload,
        mixed $default = null,
        ?callable $onError = null,
        bool $returnOriginalOnLegacyFailure = false
    ): mixed {
        if (self::isJsonPayload($payload)) {
            try {
                return self::decodeJsonPayload($payload);
            } catch (\JsonException $e) {
                if ($onError !== null) {
                    $onError($e->getMessage());
                }
                return $default;
            }
        }

        return self::decodeLegacyPhpPayload($payload, $default, false, $onError, $returnOriginalOnLegacyFailure);
    }

    /**
     * Encodes internal PHP snapshots. This is intentionally not used for
     * external cache/session/cookie/log data.
     */
    public static function encodePhpSnapshot(mixed $value): string
    {
        return serialize($value);
    }

    /**
     * Decodes internal PHP snapshots that may contain framework objects.
     */
    public static function decodePhpSnapshot(string $payload, mixed $default = null, ?callable $onError = null): mixed
    {
        return self::decodeLegacyPhpPayload($payload, $default, true, $onError);
    }

    /**
     * Builds stable non-storage keys without leaking raw serialized payloads
     * into callers.
     */
    public static function stableKey(mixed $value): string
    {
        if (is_int($value) || is_string($value)) {
            return (string)$value;
        }
        if (is_bool($value) || is_float($value) || is_null($value)) {
            return (string)$value;
        }

        if (self::hasUnsupportedJsonValue($value)) {
            return hash('sha256', self::encodePhpSnapshot($value));
        }

        $normalized = self::normalizeForStableJson($value);
        try {
            return hash(
                'sha256',
                json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
            );
        } catch (\JsonException) {
            return hash('sha256', self::encodePhpSnapshot($value));
        }
    }

    public static function hasUnsupportedJsonValue(mixed $value, int $depth = 0): bool
    {
        if ($depth > 128 || is_object($value) || is_resource($value)) {
            return true;
        }
        if (!is_array($value)) {
            return false;
        }
        foreach ($value as $item) {
            if (self::hasUnsupportedJsonValue($item, $depth + 1)) {
                return true;
            }
        }

        return false;
    }

    private static function decodeLegacyPhpPayload(
        string $payload,
        mixed $default,
        bool|array $allowedClasses,
        ?callable $onError,
        bool $returnOriginalOnFailure = false
    ): mixed {
        $message = null;
        set_error_handler(static function (int $severity, string $error) use (&$message): bool {
            $message = $error;
            return true;
        });
        try {
            $result = unserialize($payload, ['allowed_classes' => $allowedClasses]);
        } finally {
            restore_error_handler();
        }

        if ($result === false && $payload !== 'b:0;') {
            if ($message !== null && $onError !== null) {
                $onError($message);
            }
            return $returnOriginalOnFailure ? $payload : $default;
        }

        if ($allowedClasses === false && self::hasUnsupportedJsonValue($result)) {
            if ($onError !== null) {
                $onError('Legacy PHP payload contains object or resource data.');
            }
            return $returnOriginalOnFailure ? $payload : $default;
        }

        return $result;
    }

    private static function normalizeForStableJson(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        $normalized = [];
        foreach ($value as $key => $item) {
            $normalized[(string)$key] = self::normalizeForStableJson($item);
        }
        ksort($normalized);

        return $normalized;
    }
}
