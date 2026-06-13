<?php

declare(strict_types=1);

namespace fan\core\base\transfer;
/**
 * Compatibility autoload trigger for the internal transfer class.
 *
 * PHP 7 reserves "int", so the concrete class is defined as
 * \fan\core\base\transfer\transfer_int.
 */
function ensure_transfer_int_class_loaded(?callable $classExists = null): bool
{
    $classExists ??= static fn(string $className): bool => class_exists($className);

    return $classExists(transfer_int::class);
}

ensure_transfer_int_class_loaded();
