<?php

declare(strict_types=1);

use fan\core\base\model\entity as BaseEntity;
use fan\core\base\model\file_data\entity;
use FanTest\_core\SourceFileContractTestCase;

class BaseModelFileDataEntityTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/base/model/file_data/entity.php';

    public function testFileDataEntityKeepsBaseEntityContract(): void
    {
        $entity = new BaseModelFileDataEntityProbe();

        $this->assertInstanceOf(entity::class, $entity);
        $this->assertInstanceOf(BaseEntity::class, $entity);
    }
}

final class BaseModelFileDataEntityProbe extends entity
{
    public function __construct()
    {
    }
}
