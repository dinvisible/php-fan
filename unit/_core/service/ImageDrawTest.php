<?php

declare(strict_types=1);

use fan\core\service\image_draw;
use FanTest\_core\SourceFileContractTestCase;

class ServiceImageDrawTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/image_draw.php';

    public function testSetBackgroundFillsCanvas(): void
    {
        $image = new ServiceImageDrawProbe(8, 6);

        $this->assertSame($image, $image->setBackground(0x112233));
        $this->assertSame(['red' => 17, 'green' => 34, 'blue' => 51, 'alpha' => 0], $image->pixel(3, 3));
    }

    public function testLineHelpersReturnSelfAndPreserveCanvasSize(): void
    {
        $image = new ServiceImageDrawProbe(8, 6);

        $this->assertSame($image, $image->lineVertical(['left' => 2], 0xFFFFFF));
        $this->assertSame($image, $image->lineHorizontal(['top' => 3], 0xFFFFFF));
        $this->assertSame(8, $image->getWidth());
        $this->assertSame(6, $image->getHeigth());
    }

    public function testDrawingMethodsUseInjectedCanvasOperations(): void
    {
        $canvasOperations = new ServiceImageDrawCanvasOperationsDouble();
        $image = new ServiceImageDrawProbe(8, 6, $canvasOperations);

        $this->assertSame($image, $image->setBackground(0x112233));
        $this->assertSame($image, $image->line(['left' => 1, 'top' => 2, 'right' => 3, 'bottom' => 4], 0xFFFFFF));
        $this->assertSame(7, $image->getFontWidth(2));
        $this->assertSame(9, $image->getFontHeigth(2));

        $this->assertSame([[0, 0, 8, 6, 3]], $canvasOperations->fillRectangleCalls);
        $this->assertSame([[1, 2, 5, 2, 3]], $canvasOperations->lineCalls);
        $this->assertSame([2], $canvasOperations->fontWidthCalls);
        $this->assertSame([2], $canvasOperations->fontHeightCalls);
    }

    public function testSourceUsesInjectedCanvasOperationsForDrawing(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('$this->imageCanvasOperations()->fillRectangle(', $source);
        $this->assertStringContainsString('$this->imageCanvasOperations()->line(', $source);
        $this->assertStringContainsString('$this->imageCanvasOperations()->fontWidth(', $source);
        $this->assertStringContainsString('$this->arrayValueReader()', $source);
        $this->assertStringNotContainsString('imagefilledrectangle(', $source);
        $this->assertStringNotContainsString('imageline(', $source);
        $this->assertStringNotContainsString('imagefontwidth(', $source);
        $this->assertStringNotContainsString('imagefontheight(', $source);
        $this->assertStringNotContainsString('array_val(', $source);
    }

    public function testFontHelpersDelegateToGdFontMetrics(): void
    {
        $image = new ServiceImageDrawProbe(8, 6);

        $this->assertSame(imagefontwidth(2), $image->getFontWidth(2));
        $this->assertSame(imagefontheight(2), $image->getFontHeigth(2));
    }
}

final class ServiceImageDrawProbe extends image_draw
{
    public function __construct(int $width, int $height, ?object $canvasOperations = null)
    {
        $this->sourceWidth = $width;
        $this->sourceHeight = $height;
        $this->width = $width;
        $this->height = $height;
        $this->imageCanvasOperations = $canvasOperations ?? new ServiceImageDrawNativeCanvasOperationsDouble();
        $this->arrayValueReader = static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
        $this->image = imagecreatetruecolor($width, $height);
        $this->setParam(['type' => 'png']);
    }

    public function pixel(int $x, int $y): array
    {
        return imagecolorsforindex($this->image, imagecolorat($this->image, $x, $y));
    }
}

final class ServiceImageDrawNativeCanvasOperationsDouble
{
    public function colorAllocate(mixed $image, int $red, int $green, int $blue): int|false
    {
        return imagecolorallocate($image, $red, $green, $blue);
    }

    public function fillRectangle(mixed $image, int $x1, int $y1, int $x2, int $y2, int $color): bool
    {
        return imagefilledrectangle($image, $x1, $y1, $x2, $y2, $color);
    }

    public function line(mixed $image, int $x1, int $y1, int $x2, int $y2, int $color): bool
    {
        return imageline($image, $x1, $y1, $x2, $y2, $color);
    }

    public function fontWidth(int $font): int
    {
        return imagefontwidth($font);
    }

    public function fontHeight(int $font): int
    {
        return imagefontheight($font);
    }
}

final class ServiceImageDrawCanvasOperationsDouble
{
    public array $fillRectangleCalls = [];
    public array $lineCalls = [];
    public array $fontWidthCalls = [];
    public array $fontHeightCalls = [];

    public function colorAllocate(mixed $image, int $red, int $green, int $blue): int
    {
        return count([$red, $green, $blue]);
    }

    public function fillRectangle(mixed $image, int $x1, int $y1, int $x2, int $y2, int $color): bool
    {
        $this->fillRectangleCalls[] = [$x1, $y1, $x2, $y2, $color];

        return true;
    }

    public function line(mixed $image, int $x1, int $y1, int $x2, int $y2, int $color): bool
    {
        $this->lineCalls[] = [$x1, $y1, $x2, $y2, $color];

        return true;
    }

    public function fontWidth(int $font): int
    {
        $this->fontWidthCalls[] = $font;

        return 7;
    }

    public function fontHeight(int $font): int
    {
        $this->fontHeightCalls[] = $font;

        return 9;
    }
}
