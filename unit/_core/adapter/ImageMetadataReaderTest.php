<?php

declare(strict_types=1);

use fan\core\adapter\image_metadata_reader;
use PHPUnit\Framework\TestCase;

final class ImageMetadataReaderTest extends TestCase
{
    public function testReaderUsesWarningCaptureAroundGetImageSize(): void
    {
        $capture = new ImageMetadataReaderTestWarningCapture([100, 80, IMAGETYPE_PNG, 'mime' => 'image/png']);
        $reader = new image_metadata_reader($capture);

        $result = $reader->size('/tmp/picture.png');

        $this->assertSame([100, 80, IMAGETYPE_PNG, 'mime' => 'image/png'], $result);
        $this->assertSame('/tmp/picture.png', $capture->path);
    }

    public function testReaderPassesCapturedWarningToCaller(): void
    {
        $capture = new ImageMetadataReaderTestWarningCapture(false, 'metadata warning');
        $reader = new image_metadata_reader($capture);
        $warnings = [];

        $this->assertFalse($reader->size('/tmp/broken.png', static function (string $message, int $severity) use (&$warnings): void {
            $warnings[] = [$message, $severity];
        }));
        $this->assertSame([['metadata warning', E_USER_WARNING]], $warnings);
    }

    public function testSourceDelegatesWarningHandlingToAdapter(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/adapter/image_metadata_reader.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class image_metadata_reader', $source);
        $this->assertStringContainsString('public function __construct(private object $warningCapture)', $source);
        $this->assertStringContainsString('getimagesize($path)', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/warning_capture.php';", $source);
        $this->assertStringNotContainsString('new warning_capture()', $source);
        $this->assertStringNotContainsString('set_error_handler(', $source);
        $this->assertStringNotContainsString('restore_error_handler(', $source);
    }
}

final class ImageMetadataReaderTestWarningCapture
{
    public ?string $path = null;

    public function __construct(private array|false $result, private ?string $warning = null)
    {
    }

    public function run(callable $operation, ?callable $onWarning = null): array|false
    {
        if ($this->warning !== null && $onWarning !== null) {
            $onWarning($this->warning, E_USER_WARNING);
        }

        $reflector = new ReflectionFunction($operation);
        $staticVariables = $reflector->getStaticVariables();
        $this->path = $staticVariables['path'] ?? null;

        return $this->result;
    }
}
