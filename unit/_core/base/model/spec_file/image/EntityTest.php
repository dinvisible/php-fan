<?php

declare(strict_types=1);

use fan\core\base\model\row;
use fan\core\base\model\spec_file\image\entity;
use FanTest\_core\SourceFileContractTestCase;

class BaseModelSpecFileImageEntityTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/base/model/spec_file/image/entity.php';

    public function testRotateImageByIdDelegatesToImageRow(): void
    {
        $row = new BaseModelSpecFileImageEntityRowDouble();
        $entity = new BaseModelSpecFileImageEntityProbe($row);

        $entity->rotateImageById(15, 90);

        $this->assertSame([[90]], $row->rotateCalls);
        $this->assertSame([[15, false]], $entity->rowByIdCalls);
    }

    public function testGetImgTagByIdDelegatesCssClassAndParamsToRow(): void
    {
        $row = new BaseModelSpecFileImageEntityRowDouble();
        $entity = new BaseModelSpecFileImageEntityProbe($row);

        $this->assertSame('<img class="hero">', $entity->getImgTagById(12, 'hero', ['loading' => 'lazy']));
        $this->assertSame([['hero', ['loading' => 'lazy']]], $row->imgTagCalls);
    }
}

final class BaseModelSpecFileImageEntityProbe extends entity
{
    public array $rowByIdCalls = [];

    public function __construct(private BaseModelSpecFileImageEntityRowDouble $row)
    {
    }

    public function getRowById(mixed $rowId, bool $idIsEncrypt = false): row
    {
        $this->rowByIdCalls[] = [$rowId, $idIsEncrypt];

        return $this->row;
    }
}

final class BaseModelSpecFileImageEntityRowDouble extends row
{
    public array $rotateCalls = [];

    public array $imgTagCalls = [];

    public function __construct()
    {
    }

    public function rotateImage(int|float $angle, int $bgrColor = 0xFFFFFF, int|float $fix = 0): void
    {
        $this->rotateCalls[] = [$angle];
    }

    public function getImgTag(mixed $cssClass = null, ?array $param = null): ?string
    {
        $this->imgTagCalls[] = [$cssClass, $param];

        return '<img class="' . $cssClass . '">';
    }
}
