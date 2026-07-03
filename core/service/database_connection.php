<?php

declare(strict_types=1);

namespace fan\core\service;

final class database_connection
{
    private bool $initialized = false;
    private bool $isError = false;
    private ?string $errorMessage = null;
    private bool $autoTransaction;

    public function __construct(
        private object $connection,
        private string $connectionName,
        private database_connections $connections,
        private array $scenario = []
    ) {
        $this->autoTransaction = (int)($scenario['ISOLATION_LEVEL'] ?? 0) > 0;
    }

    public function execute(string $sql, ?array $param = null, int|string|null $resultType = null): mixed
    {
        unset($resultType); // Kept for compatibility with the legacy result-mode argument.
        $this->resetError();
        $bindings = $param ?? [];

        try {
            $this->initialize();
            if (preg_match('/^\s*(?:SELECT|SHOW|DESCRIBE|EXPLAIN|WITH)\b/i', $sql)) {
                return array_map(
                    static fn(mixed $row): array => is_object($row) ? get_object_vars($row) : (array)$row,
                    $this->connection->select($sql, $bindings)
                );
            }
            if (preg_match('/^\s*(?:INSERT|UPDATE|DELETE|REPLACE)\b/i', $sql)) {
                return $this->connection->affectingStatement($sql, $bindings);
            }

            return $this->connection->statement($sql, $bindings);
        } catch (\Throwable $exception) {
            $this->isError = true;
            $this->errorMessage = $exception->getMessage();

            return false;
        }
    }

    public function getAll(string $sql, ?array $param = null, int|string|null $resultType = null): array
    {
        $result = $this->execute($sql, $param, $resultType);

        return is_array($result) ? $result : [];
    }

    public function getAllLimit(
        string $sql,
        ?array $param = null,
        int|float $quantity = -1,
        int|float $offset = -1,
        int|string|null $resultType = null
    ): array {
        if ($quantity >= 0) {
            $sql .= ' LIMIT ' . ($offset >= 0 ? (int)$offset . ', ' : '') . (int)$quantity;
        }

        return $this->getAll($sql, $param, $resultType);
    }

    public function getOne(string $sql, string $fieldName, ?array $param = null): mixed
    {
        $row = $this->getRow($sql, $param);

        return $row[$fieldName] ?? null;
    }

    public function getRow(string $sql, ?array $param = null, int|string|null $resultType = null): array
    {
        return $this->getAll($sql, $param, $resultType)[0] ?? [];
    }

    public function getCol(string $sql, string|int $column, ?array $param = null): array
    {
        return array_map(static fn(array $row): mixed => $row[$column] ?? null, $this->getAll($sql, $param));
    }

    public function getAssoc(string $sql, ?array $param = null): array
    {
        $result = [];
        foreach ($this->getAll($sql, $param) as $row) {
            $key = array_shift($row);
            if (is_int($key) || is_string($key)) {
                $result[$key] = $row;
            }
        }

        return $result;
    }

    public function getInsertId(): mixed
    {
        try {
            $pdo = $this->connection->getPdo();

            return is_object($pdo) && method_exists($pdo, 'lastInsertId') ? $pdo->lastInsertId() : null;
        } catch (\Throwable $exception) {
            $this->isError = true;
            $this->errorMessage = $exception->getMessage();

            return null;
        }
    }

    public function getTableStatus(string $tableName): array
    {
        return $this->getRow('SHOW TABLE STATUS LIKE ?', [$tableName]);
    }

    public function startTransaction(): static
    {
        try {
            $this->initialize(false);
            if ($this->transactionLevel() === 0) {
                $this->connection->beginTransaction();
            }
            $this->initialized = true;
        } catch (\Throwable $exception) {
            $this->isError = true;
            $this->errorMessage = $exception->getMessage();
        }

        return $this;
    }

    public function commit(): static
    {
        return $this->finishTransaction('commit', false);
    }

    public function rollback(?string $savePoint = null, bool $setError = true): static
    {
        unset($savePoint); // Illuminate manages nested savepoints through its transaction level.

        return $this->finishTransaction('rollBack', $setError);
    }

    public function connectionClose(): static
    {
        $this->connections->close();

        return $this;
    }

    public function fixAll(string $operation, bool $setError = true): void
    {
        $this->connections->fixAll($operation, $setError);
    }

    public function close(): void
    {
        $this->connections->close();
    }

    public function getConnectionName(): string
    {
        return $this->connectionName;
    }

    public function isError(): bool
    {
        return $this->isError;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function resetError(): static
    {
        $this->isError = false;
        $this->errorMessage = null;

        return $this;
    }

    public function setResultTypes(int|string $resultType): static
    {
        unset($resultType);

        return $this;
    }

    public function parseSql(string $sql, array $param): string
    {
        foreach ($param as $value) {
            $replacement = $value === null
                ? 'NULL'
                : (is_numeric($value) ? (string)$value : $this->quote((string)$value));
            $position = strpos($sql, '?');
            if ($position === false) {
                break;
            }
            $sql = substr_replace($sql, $replacement, $position, 1);
        }

        return $sql;
    }

    private function initialize(bool $startAutomaticTransaction = true): void
    {
        if ($this->initialized) {
            return;
        }
        foreach ((array)($this->scenario['SQL'] ?? []) as $statement) {
            if (!is_string($statement) || $statement === '' || preg_match('/^\s*SET\s+AUTOCOMMIT\b/i', $statement)) {
                continue;
            }
            $this->connection->unprepared($statement);
        }
        $this->initialized = true;
        if ($startAutomaticTransaction && $this->autoTransaction && $this->transactionLevel() === 0) {
            $this->connection->beginTransaction();
        }
    }

    private function finishTransaction(string $method, bool $setError): static
    {
        try {
            if ($this->initialized && $this->transactionLevel() > 0) {
                $this->connection->{$method}();
            }
            $this->initialized = false;
            if ($setError && $method === 'rollBack') {
                $this->isError = true;
            }
        } catch (\Throwable $exception) {
            $this->isError = true;
            $this->errorMessage = $exception->getMessage();
        }

        return $this;
    }

    private function transactionLevel(): int
    {
        return method_exists($this->connection, 'transactionLevel') ? (int)$this->connection->transactionLevel() : 0;
    }

    private function quote(string $value): string
    {
        try {
            $pdo = $this->connection->getPdo();
            if (is_object($pdo) && method_exists($pdo, 'quote')) {
                return (string)$pdo->quote($value);
            }
        } catch (\Throwable) {
        }

        return "'" . str_replace("'", "''", $value) . "'";
    }
}
