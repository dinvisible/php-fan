<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../_core/base/expression_evaluator.php';

use fan\core\base\expression_evaluator;

class ExpressionEvaluatorTest extends \PHPUnit\Framework\TestCase
{
    public function testEvaluatesBooleanExpressionsWithResolvedIdentifiers(): void
    {
        $roles = [
            'admin' => true,
            'editor' => true,
            'guest' => false,
        ];

        $result = expression_evaluator::evaluate(
            '(admin & editor) && !guest',
            fn($name) => $roles[$name] ?? false
        );

        $this->assertTrue($result);
    }

    public function testEvaluatesLiteralsComparisonsAndArithmetic(): void
    {
        $result = expression_evaluator::evaluate(
            "age >= 18 && name == 'Sergey' && (2 + 3 * 4) == 14",
            fn($name) => [
                'age' => 21,
                'name' => 'Sergey',
            ][$name]
        );

        $this->assertTrue($result);
    }

    public function testEvaluatesBooleanXorWithPhpIndependentPrecedence(): void
    {
        $this->assertTrue(expression_evaluator::evaluate('true ^ false', fn() => null));
        $this->assertFalse(expression_evaluator::evaluate('true ^ true', fn() => null));
    }

    public function testRejectsInvalidExpression(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        expression_evaluator::evaluate('admin && (', fn() => true);
    }
}
