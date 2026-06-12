<?php

declare(strict_types=1);

use fan\core\base\model\entity;
use fan\core\base\model\request;
use FanTest\core\SourceFileContractTestCase;


class BaseModelRequestTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/base/model/request.php';

    public function testSetGetMagicAccessAndDynamicMethodsUseSqlMap(): void
    {
        $request = new BaseModelRequestProbe(new BaseModelRequestEntityDouble());

        $request->set('list', 'SELECT * FROM users');
        $request->details = 'SELECT * FROM users WHERE id = ?';
        $request->set_archive('SELECT * FROM archive');

        $this->assertSame('SELECT * FROM users', $request->get('list'));
        $this->assertSame('SELECT * FROM users WHERE id = ?', $request->details);
        $this->assertSame('SELECT * FROM archive', $request->get_archive());
        $this->assertSame([
            'list' => 'SELECT * FROM users',
            'details' => 'SELECT * FROM users WHERE id = ?',
            'archive' => 'SELECT * FROM archive',
        ], $request->toArray());
    }

    public function testGetLazyLoadsSqlWhenKeyIsMissing(): void
    {
        $request = new BaseModelRequestProbe(new BaseModelRequestEntityDouble(), [
            'from_file' => 'SELECT loaded',
        ]);

        $this->assertSame('SELECT loaded', $request->get('from_file'));
        $this->assertSame(['from_file'], $request->loadCalls);
    }

    public function testMissingSqlKeyThrowsOutOfBoundsException(): void
    {
        $request = new BaseModelRequestProbe(new BaseModelRequestEntityDouble());

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Call for unset SQL-key.');

        $request->get('missing');
    }

    public function testInvalidDynamicMethodCreatesExceptionThroughEntityBoundary(): void
    {
        $entity = new BaseModelRequestEntityDouble();
        $request = new request($entity);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Incorrect call of instance SQL-request loader!');

        try {
            $request->unknown();
        } finally {
            $this->assertSame([
                ['Incorrect call of instance SQL-request loader!', E_USER_ERROR, null],
            ], $entity->fatalExceptionCalls);
        }
    }

    public function testLoadsSqlFileUsingInjectedReflector(): void
    {
        $dir = sys_get_temp_dir() . '/fan-model-request-' . uniqid('', true);
        $entityPath = $dir . '/Entity.php';
        $sqlPath = $dir . '/sql/find.sql';
        $fileStorage = new BaseModelRequestFileStorageDouble([
            $sqlPath => 'SELECT * FROM users',
        ]);

        $request = new request(
            new BaseModelRequestEntityDouble(new BaseModelRequestEntityServiceDouble('sql')),
            new BaseModelRequestReflectorDouble([$entityPath]),
            $fileStorage
        );

        $this->assertSame('SELECT * FROM users', $request->get('find'));
        $this->assertSame([$sqlPath], $fileStorage->existsCalls);
        $this->assertSame([$sqlPath], $fileStorage->readCalls);
    }

    public function testLoadsSqlFileUsingInjectedSqlDirectoryResolver(): void
    {
        $dir = sys_get_temp_dir() . '/fan-model-request-' . uniqid('', true);
        $entityPath = $dir . '/Entity.php';
        $sqlPath = $dir . '/queries/find.sql';
        $fileStorage = new BaseModelRequestFileStorageDouble([
            $sqlPath => 'SELECT * FROM users',
        ]);

        $request = new request(
            new BaseModelRequestEntityDouble(new BaseModelRequestEntityServiceDouble('legacy')),
            new BaseModelRequestReflectorDouble([$entityPath]),
            $fileStorage,
            static fn(entity $entity): string => 'queries'
        );

        $this->assertSame('SELECT * FROM users', $request->get('find'));
        $this->assertSame([$sqlPath], $fileStorage->existsCalls);
    }

    public function testSqlDirectoryResolverMustReturnString(): void
    {
        $request = new request(
            new BaseModelRequestEntityDouble(),
            new BaseModelRequestReflectorDouble([__FILE__]),
            new BaseModelRequestFileStorageDouble([]),
            static fn(entity $entity): array => []
        );

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Model request SQL directory resolver must return a string.');

        $request->get('find');
    }

    public function testSourceUsesInjectedReflectorInsteadOfEntityServiceLocator(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('private ?object $reflector', $source);
        $this->assertStringContainsString('private ?object $fileStorage = null;', $source);
        $this->assertStringContainsString('private \Closure $sqlDirectoryResolver;', $source);
        $this->assertStringContainsString('$this->fileStorage()->read($fileName)', $source);
        $this->assertStringContainsString('$this->fileStorage()->exists($fileName)', $source);
        $this->assertStringContainsString('private function sqlDirectory(entity $entity): string', $source);
        $this->assertStringNotContainsString('$entity->getService()->getSqlDir()', $source);
        $this->assertStringContainsString('private function createRequestFatalException(', $source);
        $this->assertStringContainsString('$this->getEntity()->createRequestFatalException($message, $code, $previous)', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\model\entity\fatal as fatalException;', $source);
        $this->assertStringNotContainsString('getContainerService(', $source);
        $this->assertStringNotContainsString('containerService(', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:file_exists|file_get_contents)\s*\(/',
            $source
        );
    }
}

final class BaseModelRequestProbe extends request
{
    public array $loadCalls = [];

    public function __construct(entity $entity, private array $loadedSql = [])
    {
        parent::__construct($entity);
    }

    protected function _loadSQL(string $key): ?string
    {
        $this->loadCalls[] = $key;

        return $this->loadedSql[$key] ?? null;
    }
}

final class BaseModelRequestEntityDouble extends entity
{
    public array $fatalExceptionCalls = [];

    public function __construct(?BaseModelRequestEntityServiceDouble $service = null)
    {
        $this->service = $service ?? new BaseModelRequestEntityServiceDouble();
    }

    public function createRequestFatalException(string $message, int $code = E_USER_ERROR, ?Throwable $previous = null): Throwable
    {
        $this->fatalExceptionCalls[] = [$message, $code, $previous];

        return new RuntimeException($message, $code, $previous);
    }
}

final class BaseModelRequestEntityServiceDouble
{
    public function __construct(private string $sqlDir = 'sql')
    {
    }

    public function getSqlDir(): string
    {
        return $this->sqlDir;
    }
}

final class BaseModelRequestReflectorDouble
{
    public function __construct(private array $parentPaths)
    {
    }

    public function getParentPaths(object $entity): array
    {
        return $this->parentPaths;
    }
}

final class BaseModelRequestFileStorageDouble
{
    public array $existsCalls = [];
    public array $readCalls = [];

    /**
     * @param array<string, string> $files
     */
    public function __construct(private array $files)
    {
    }

    public function exists(string $path): bool
    {
        $this->existsCalls[] = $path;

        return array_key_exists($path, $this->files);
    }

    public function read(string $path): string|false
    {
        $this->readCalls[] = $path;

        return $this->files[$path] ?? false;
    }
}
