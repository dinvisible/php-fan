<?php

declare(strict_types=1);

use fan\core\service\database_connection;
use fan\core\service\database_connections;
use PHPUnit\Framework\TestCase;

final class DatabaseConnectionsTest extends TestCase
{
    public function testManagerCachesAdaptersAndDisconnectsKnownConnections(): void
    {
        $eloquent = new DatabaseConnectionsEloquentDouble();
        $connections = new database_connections($eloquent, [
            'DEFAULT_CONNECTION' => 'common',
            'DATABASE' => ['common' => []],
        ]);

        $default = $connections->connection();

        $this->assertInstanceOf(database_connection::class, $default);
        $this->assertSame($default, $connections->connection('common'));
        $this->assertNotSame($default, $connections->connection('common', 'secondary'));

        $connections->close();

        $this->assertSame(['common'], $eloquent->databaseManager->disconnected);
    }

    public function testAdapterRunsScenarioUsesLegacyQueryApiAndManagesTransactions(): void
    {
        $eloquent = new DatabaseConnectionsEloquentDouble();
        $connections = new database_connections($eloquent, [
            'DEFAULT_CONNECTION' => 'common',
            'DEFAULT_SCENARIO' => 'transactional',
            'DATABASE' => ['common' => []],
            'SCENARIO' => [
                'transactional' => [
                    'ISOLATION_LEVEL' => 1,
                    'SQL' => [
                        'SET AUTOCOMMIT=0',
                        'SET TRANSACTION ISOLATION LEVEL READ COMMITTED',
                    ],
                ],
            ],
        ]);
        $connection = $connections->connection();
        $eloquent->connection->selectResult = [(object)['id' => 7, 'name' => 'sample']];

        $this->assertSame([['id' => 7, 'name' => 'sample']], $connection->getAllLimit('SELECT * FROM items', [], 1, 2));
        $this->assertSame('SELECT * FROM items LIMIT 2, 1', $eloquent->connection->lastSql);
        $this->assertSame(['SET TRANSACTION ISOLATION LEVEL READ COMMITTED'], $eloquent->connection->unprepared);
        $this->assertSame(1, $eloquent->connection->transactionLevel());
        $this->assertSame(7, $connection->getOne('SELECT id FROM items', 'id'));

        $eloquent->connection->affectedRows = 3;
        $this->assertSame(3, $connection->execute('UPDATE items SET name = ?', ['updated']));
        $this->assertFalse($connection->isError());

        $connection->commit();
        $this->assertSame(0, $eloquent->connection->transactionLevel());

        $connection->execute('SELECT * FROM items');
        $connections->fixAll('rollback', false);
        $this->assertSame(0, $eloquent->connection->transactionLevel());
        $this->assertFalse($connection->isError());
    }

    public function testAdapterCapturesDriverErrorsForLegacyCallers(): void
    {
        $eloquent = new DatabaseConnectionsEloquentDouble();
        $connections = new database_connections($eloquent, [
            'DEFAULT_CONNECTION' => 'common',
            'DATABASE' => ['common' => []],
        ]);
        $connection = $connections->connection();
        $eloquent->connection->exception = new RuntimeException('query failed');

        $this->assertFalse($connection->execute('DELETE FROM items'));
        $this->assertTrue($connection->isError());
        $this->assertSame('query failed', $connection->getErrorMessage());
    }
}

final class DatabaseConnectionsEloquentDouble
{
    public DatabaseConnectionsDriverDouble $connection;
    public DatabaseConnectionsManagerDouble $databaseManager;

    public function __construct()
    {
        $this->connection = new DatabaseConnectionsDriverDouble();
        $this->databaseManager = new DatabaseConnectionsManagerDouble();
    }

    public function getConnection(string $name): object
    {
        return $this->connection;
    }

    public function getDatabaseManager(): object
    {
        return $this->databaseManager;
    }
}

final class DatabaseConnectionsManagerDouble
{
    public array $disconnected = [];

    public function disconnect(string $name): void
    {
        $this->disconnected[] = $name;
    }
}

final class DatabaseConnectionsDriverDouble
{
    public array $selectResult = [];
    public int $affectedRows = 0;
    public array $unprepared = [];
    public ?Throwable $exception = null;
    public string $lastSql = '';
    private int $level = 0;

    public function select(string $sql, array $bindings): array
    {
        $this->failIfConfigured();
        $this->lastSql = $sql;

        return $this->selectResult;
    }

    public function affectingStatement(string $sql, array $bindings): int
    {
        $this->failIfConfigured();
        $this->lastSql = $sql;

        return $this->affectedRows;
    }

    public function statement(string $sql, array $bindings): bool
    {
        $this->failIfConfigured();
        $this->lastSql = $sql;

        return true;
    }

    public function unprepared(string $sql): bool
    {
        $this->failIfConfigured();
        $this->unprepared[] = $sql;

        return true;
    }

    public function beginTransaction(): void
    {
        ++$this->level;
    }

    public function commit(): void
    {
        $this->level = max(0, $this->level - 1);
    }

    public function rollBack(): void
    {
        $this->level = max(0, $this->level - 1);
    }

    public function transactionLevel(): int
    {
        return $this->level;
    }

    public function getPdo(): object
    {
        return new class {
            public function lastInsertId(): string
            {
                return '42';
            }

            public function quote(string $value): string
            {
                return "'" . addslashes($value) . "'";
            }
        };
    }

    private function failIfConfigured(): void
    {
        if ($this->exception !== null) {
            throw $this->exception;
        }
    }
}
