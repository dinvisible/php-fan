<?php

declare(strict_types=1);

use fan\core\di\delayed_meta_factory;
use PHPUnit\Framework\TestCase;
use fan\core\base\meta\delayed;


require_once __DIR__ . '/../../../core/base/meta/delayed.php';
require_once __DIR__ . '/../../../core/factory/delayed_meta_factory.php';

final class DelayedMetaFactoryTest extends TestCase
{
    public function testFactoryCreatesProjectDelayedMeta(): void
    {
        if (!class_exists('\fan\project\base\meta\delayed', false)) {
            class_alias(delayed::class, '\fan\project\base\meta\delayed');
        }

        $target = new DelayedMetaFactoryTargetDouble();

        $delayed = (new delayed_meta_factory())($target, 'build', ['left', 'right']);

        $this->assertInstanceOf(delayed::class, $delayed);
        $this->assertSame('left:right', $delayed->getValue());
    }}

final class DelayedMetaFactoryTargetDouble
{
    public function build(string $left, string $right): string
    {
        return $left . ':' . $right;
    }
}
