<?php

declare(strict_types=1);

use fan\core\plain\image;
use FanTest\_core\SourceFileContractTestCase;

if (!class_exists('bootstrap', false)) {
    class bootstrap
    {
        public static function parsePath(string $path): string
        {
            return $path;
        }
    }
}

class PlainImageTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/plain/image.php';

    public function testImageEntrypointSetsImageTypeBeforeDelegatingToFile(): void
    {
        $image = new PlainImageProbe('image');

        $this->assertSame('content', $image->getImage());
        $this->assertSame('image', $image->imageType());
    }

    public function testNailSizeUsesFixedAdminSizeOrNumericRequestValues(): void
    {
        $image = new PlainImageProbe('image');

        $image->setImageType('adm_nail');
        $this->assertSame([60, 60], $image->nailSize());

        $image->setImageType('nail');
        $image->contextDouble->request = new PlainImageRequestDouble(['w' => '120', 'h' => '45.5']);
        $this->assertSame([120, 45.5], $image->nailSize());
    }

    public function testNailDirectoryIsCreatedFromConfiguredMask(): void
    {
        $base = sys_get_temp_dir() . '/php-fan-nail-' . uniqid('', true);
        $image = new PlainImageProbe('image');

        try {
            $this->assertSame($base, $image->nailDir($base));
            $this->assertDirectoryExists($base);
        } finally {
            if (is_dir($base)) {
                rmdir($base);
            }
        }
    }

    public function testNailDirectoryErrorsUseInjectedPlainExceptionFactory(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'fan-nail-file-');
        $this->assertIsString($file);
        $image = new PlainImageProbe('image');

        try {
            $image->nailDir($file, true);
            $this->fail('Expected injected plain exception factory to create the nail directory failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Incorrect path for nail. Is file there "' . $file . '"', $exception->getMessage());
        } finally {
            @unlink($file);
        }

        $this->assertSame([
            [
                '\fan\project\exception\plain\fatal',
                $image,
                'Incorrect path for nail. Is file there "' . $file . '"',
                E_USER_ERROR,
                null,
            ],
        ], $image->contextDouble->plainExceptionCalls);
    }

    public function testAdminNailStubUsesInjectedImageMetadataReader(): void
    {
        $stub = tempnam(sys_get_temp_dir(), 'fan-stub-');
        $this->assertIsString($stub);
        file_put_contents($stub, 'stub');
        $image = new PlainImageProbe('image');
        $image->setPlainConfig(new PlainImageConfigDouble(['nail_stub' => $stub]));
        $image->contextDouble->imageMetadataReader = new PlainImageMetadataReaderDouble([
            'mime' => 'image/gif',
            0 => 60,
            1 => 60,
            2 => IMAGETYPE_GIF,
        ]);

        try {
            $data = $image->stubFileData();
        } finally {
            @unlink($stub);
        }

        $this->assertSame($stub, $data['filePath']);
        $this->assertSame('image/gif', $data['headers']['contentType']);
        $this->assertSame(4, $data['headers']['length']);
        $this->assertIsInt($data['headers']['modified']);
        $this->assertSame([$stub], $image->contextDouble->imageMetadataReader->paths);
    }

    public function testSourceUsesInjectedImageMetadataReaderAndFileStorage(): void
    {
        $source = $this->sourceCode();

        $this->assertStringNotContainsString('getimagesize(', $source);
        $this->assertStringContainsString('$this->context()->imageMetadataReader()->size($nailStub)', $source);
        $this->assertStringContainsString('$this->context()->fileStorage()->isFile($data[\'filePath\'])', $source);
        $this->assertStringContainsString('$this->context()->fileStorage()->isReadable($nailStub)', $source);
        $this->assertStringContainsString('$this->context()->fileStorage()->makeDirectory($nailDir, 0744, true)', $source);
        $this->assertStringContainsString('$this->createPlainFatalException(', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\plain\fatal as fatalException;', $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:is_file|is_readable|filesize|filemtime|is_dir|mkdir|is_writable)\s*\(/',
            $source
        );
    }
}

final class PlainImageProbe extends image
{
    public PlainImageContextDouble $contextDouble;

    public function __construct(string $key)
    {
        $this->key = $key;
        $this->contextDouble = new PlainImageContextDouble();
        $this->context = $this->contextDouble;
    }

    public function getFile(): array|string
    {
        return 'content';
    }

    public function imageType(): ?string
    {
        return $this->imageType;
    }

    public function setImageType(string $imageType): void
    {
        $this->imageType = $imageType;
    }

    public function nailSize(): array
    {
        return $this->_getNailSize();
    }

    public function nailDir(string $mask, bool $isException = false): ?string
    {
        return $this->_getNailDir($mask, $isException);
    }

    public function stubFileData(): array
    {
        return $this->_getStubFileData();
    }

    public function setPlainConfig(object $config): void
    {
        $this->config = $config;
    }
}

final class PlainImageContextDouble
{
    public ?PlainImageRequestDouble $request = null;
    public ?PlainImageMetadataReaderDouble $imageMetadataReader = null;
    public ?PlainImageFileStorageDouble $fileStorage = null;
    public array $plainExceptionCalls = [];

    public function request(): object
    {
        return $this->request ?? new PlainImageRequestDouble([]);
    }

    public function parsePath(string $path): string
    {
        return $path;
    }

    public function imageMetadataReader(): object
    {
        return $this->imageMetadataReader ??= new PlainImageMetadataReaderDouble(false);
    }

    public function fileStorage(): object
    {
        return $this->fileStorage ??= new PlainImageFileStorageDouble();
    }

    public function createPlainFatalException(object $controller, string $message, int $code = E_USER_ERROR, ?Throwable $previous = null): Throwable
    {
        $this->plainExceptionCalls[] = ['\fan\project\exception\plain\fatal', $controller, $message, $code, $previous];

        return new RuntimeException($message, $code, $previous);
    }
}

final class PlainImageRequestDouble
{
    public function __construct(private array $values)
    {
    }

    public function get(string $key, string $source): mixed
    {
        return $this->values[$key] ?? null;
    }
}

final class PlainImageConfigDouble
{
    public function __construct(private array $values)
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->values[$key] ?? $default;
    }
}

final class PlainImageMetadataReaderDouble
{
    public array $paths = [];

    public function __construct(private array|false $result)
    {
    }

    public function size(string $path): array|false
    {
        $this->paths[] = $path;

        return $this->result;
    }
}

final class PlainImageFileStorageDouble
{
    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    public function isReadable(string $path): bool
    {
        return is_readable($path);
    }

    public function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    public function makeDirectory(string $path, int $mode = 0777, bool $recursive = false): bool
    {
        return mkdir($path, $mode, $recursive);
    }

    public function size(string $path): int|false
    {
        return filesize($path);
    }

    public function modifiedTime(string $path): int|false
    {
        return filemtime($path);
    }
}
