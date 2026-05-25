<?php

declare(strict_types=1);

use fan\core\base\model\entity as BaseEntity;
use fan\core\base\model\spec_file\entity;
use FanTest\_core\SourceFileContractTestCase;

class BaseModelSpecFileEntityTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/base/model/spec_file/entity.php';

    public function testSpecFileEntityKeepsBaseEntityContract(): void
    {
        $entity = new BaseModelSpecFileEntityProbe();

        $this->assertInstanceOf(entity::class, $entity);
        $this->assertInstanceOf(BaseEntity::class, $entity);
    }
}

final class BaseModelSpecFileEntityProbe extends entity
{
    public function __construct()
    {
    }
}
