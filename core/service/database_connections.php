<?php

declare(strict_types=1);

namespace fan\core\service;

final class database_connections
{
    /** @var array<string, database_connection> */
    private array $connections = [];

    private array $config;

    public function __construct(private object $eloquent, array|object $config)
    {
        $this->config = $this->normalizeConfig($config);
    }

    public function connection(?string $connectionName = null, mixed $extraKey = 0): database_connection
    {
        $connectionName = $connectionName ?: (string)($this->config['DEFAULT_CONNECTION'] ?? 'default');
        $key = $connectionName . ':' . $this->normalizeExtraKey($extraKey);
        if (!isset($this->connections[$key])) {
            if (!method_exists($this->eloquent, 'getConnection')) {
                throw new \RuntimeException('Eloquent manager does not expose database connections.');
            }
            $connection = $this->eloquent->getConnection($connectionName);
            if (!is_object($connection)) {
                throw new \UnexpectedValueException('Eloquent connection must be an object.');
            }
            $this->connections[$key] = new database_connection(
                $connection,
                $connectionName,
                $this,
                $this->scenarioFor($connectionName)
            );
        }

        return $this->connections[$key];
    }

    public function fixAll(string $operation, bool $setError = true): void
    {
        if (!in_array($operation, ['commit', 'rollback', 'nothing'], true)) {
            throw new \InvalidArgumentException('Unsupported database operation "' . $operation . '".');
        }
        if ($operation === 'nothing') {
            return;
        }

        foreach ($this->connections as $connection) {
            $operation === 'commit' ? $connection->commit() : $connection->rollback(null, $setError);
        }
    }

    public function close(): void
    {
        foreach ($this->connections as $connection) {
            $connection->commit();
        }

        $manager = method_exists($this->eloquent, 'getDatabaseManager') ? $this->eloquent->getDatabaseManager() : null;
        if (is_object($manager) && method_exists($manager, 'disconnect')) {
            foreach (array_unique(array_map(
                static fn(database_connection $connection): string => $connection->getConnectionName(),
                $this->connections
            )) as $connectionName) {
                $manager->disconnect($connectionName);
            }
        }
        $this->connections = [];
    }

    private function scenarioFor(string $connectionName): array
    {
        $connection = $this->config['DATABASE'][$connectionName] ?? [];
        $scenarioName = is_array($connection) && !empty($connection['SCENARIO'])
            ? (string)$connection['SCENARIO']
            : (string)($this->config['DEFAULT_SCENARIO'] ?? '');
        $scenario = $scenarioName === '' ? [] : ($this->config['SCENARIO'][$scenarioName] ?? []);

        return is_array($scenario) ? $scenario : [];
    }

    private function normalizeConfig(array|object $config): array
    {
        if (is_object($config) && method_exists($config, 'toArray')) {
            $config = $config->toArray();
        }
        if (!is_array($config)) {
            throw new \InvalidArgumentException('Database configuration must be an array or expose toArray().');
        }

        return $config;
    }

    private function normalizeExtraKey(mixed $extraKey): string
    {
        if (is_scalar($extraKey) || $extraKey === null) {
            return (string)$extraKey;
        }

        return hash('sha256', serialize($extraKey));
    }
}
