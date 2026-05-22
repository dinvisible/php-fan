<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../_core/base/meta/delayed.php';

class DelayedTest extends \PHPUnit\Framework\TestCase
{
    public function testCallsObjectMethodWithArrayArguments(): void
    {
        $target = new class {
            public function build($left, $right): string
            {
                return $left . ':' . $right;
            }
        };

        $delayed = new \fan\core\base\meta\delayed($target, 'build', ['alpha', 'beta']);

        $this->assertSame('alpha:beta', $delayed->getValue());
    }

    public function testWrapsScalarArgumentIntoSingleArgumentList(): void
    {
        $target = new class {
            /**
             * @param mixed $value Value that should be applied or transformed.
             */
            public function repeat($value): string
            {
                return $value . $value;
            }
        };

        $delayed = new \fan\core\base\meta\delayed($target, 'repeat', 'x');

        $this->assertSame('xx', $delayed->getValue());
    }

    public function testNullArgumentsProduceEmptyArgumentList(): void
    {
        $target = new class {
            public function value(): int
            {
                return 42;
            }
        };

        $delayed = new \fan\core\base\meta\delayed($target, 'value', null);

        $this->assertSame(42, $delayed->getValue());
    }
}
