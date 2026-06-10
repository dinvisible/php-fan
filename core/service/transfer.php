<?php

declare(strict_types=1);

namespace fan\core\service;
use fan\core\base\transfer as base_transfer;


class transfer
{
    private $transferExceptionFactory;

    public function __construct(private ?object $databaseConnections, callable $transferExceptionFactory)
    {
        $this->transferExceptionFactory = $transferExceptionFactory;
    }

    public function out(string $newUrl, ?string $newQueryString = null, ?string $dbOper = null): never
    {
        throw $this->createTransfer('out', $newUrl, $newQueryString, $dbOper);
    }

    public function int(string $newUrl, ?string $newQueryString = null, ?string $dbOper = null): never
    {
        throw $this->createTransfer('int', $newUrl, $newQueryString, $dbOper);
    }

    public function sham(string $newUrl, ?string $newQueryString = null, ?string $dbOper = null): never
    {
        throw $this->createTransfer('sham', $newUrl, $newQueryString, $dbOper);
    }

    private function createTransfer(
        string $type,
        string $newUrl,
        ?string $newQueryString,
        ?string $dbOper
    ): base_transfer {
        $transfer = ($this->transferExceptionFactory)(
            $type,
            $newUrl,
            $newQueryString,
            $dbOper,
            $this->databaseConnections
        );
        if (!$transfer instanceof base_transfer) {
            throw new \RuntimeException('Transfer exception factory must return a transfer exception.');
        }

        return $transfer;
    }
}
