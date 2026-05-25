<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\base\transfer;
use fan\project\base\transfer\out;
use fan\project\base\transfer\sham;
use fan\project\base\transfer\transfer_int;


final class transfer_exception_factory
{
    public function __invoke(
        string $type,
        string $newUrl,
        ?string $newQueryString,
        ?string $dbOper,
        ?object $databaseConnections
    ): transfer {
        return match ($type) {
            'out' => new out($newUrl, $newQueryString, $dbOper, $databaseConnections),
            'int' => new transfer_int($newUrl, $newQueryString, $dbOper, $databaseConnections),
            'sham' => new sham($newUrl, $newQueryString, $dbOper, $databaseConnections),
            default => throw new \InvalidArgumentException('Unknown transfer type "' . $type . '".'),
        };
    }
}
