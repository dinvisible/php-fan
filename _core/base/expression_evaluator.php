<?php

declare(strict_types=1);

namespace fan\core\base;

class expression_evaluator
{
    private array $tokens = [];
    private int $position = 0;
    private $resolver;

    public static function evaluate(string $expression, callable $resolver): mixed
    {
        $evaluator = new self($expression, $resolver);
        $result = $evaluator->parseOr();
        if (!$evaluator->isEnd()) {
            throw new \InvalidArgumentException('Unexpected token "' . $evaluator->peek()['value'] . '".');
        }
        return $result;
    }

    private function __construct(string $expression, callable $resolver)
    {
        $this->tokens = $this->tokenize($expression);
        $this->resolver = $resolver;
    }

    private function tokenize(string $expression): array
    {
        $tokens = [];
        $length = strlen($expression);
        for ($i = 0; $i < $length;) {
            $char = $expression[$i];
            if (ctype_space($char)) {
                $i++;
                continue;
            }
            $two = substr($expression, $i, 2);
            $three = substr($expression, $i, 3);
            if (in_array($three, ['===', '!=='], true)) {
                $tokens[] = ['type' => 'op', 'value' => $three];
                $i += 3;
                continue;
            }
            if (in_array($two, ['&&', '||', '==', '!=', '>=', '<=', '<>'], true)) {
                $tokens[] = ['type' => 'op', 'value' => $two];
                $i += 2;
                continue;
            }
            if (str_contains('()!~&|^=<>+-*/%', $char)) {
                $tokens[] = ['type' => $char === '(' || $char === ')' ? $char : 'op', 'value' => $char];
                $i++;
                continue;
            }
            if ($char === '\'' || $char === '"') {
                [$value, $i] = $this->readString($expression, $i);
                $tokens[] = ['type' => 'literal', 'value' => $value];
                continue;
            }
            if (ctype_digit($char) || $char === '.' && isset($expression[$i + 1]) && ctype_digit($expression[$i + 1])) {
                [$value, $i] = $this->readNumber($expression, $i);
                $tokens[] = ['type' => 'literal', 'value' => $value];
                continue;
            }
            if (ctype_alpha($char) || $char === '_') {
                [$value, $i] = $this->readIdentifier($expression, $i);
                $tokens[] = ['type' => 'identifier', 'value' => $value];
                continue;
            }
            throw new \InvalidArgumentException('Unexpected character "' . $char . '".');
        }
        return $tokens;
    }

    private function readString(string $expression, int $offset): array
    {
        $quote = $expression[$offset];
        $value = '';
        $length = strlen($expression);
        for ($i = $offset + 1; $i < $length; $i++) {
            $char = $expression[$i];
            if ($char === '\\' && $i + 1 < $length) {
                $value .= stripcslashes(substr($expression, $i, 2));
                $i++;
                continue;
            }
            if ($char === $quote) {
                return [$value, $i + 1];
            }
            $value .= $char;
        }
        throw new \InvalidArgumentException('Unclosed string literal.');
    }

    private function readNumber(string $expression, int $offset): array
    {
        if (!preg_match('/\G(?:\d+(?:\.\d*)?|\.\d+)/', $expression, $matches, 0, $offset)) {
            throw new \InvalidArgumentException('Invalid number.');
        }
        $raw = $matches[0];
        return [str_contains($raw, '.') ? (float)$raw : (int)$raw, $offset + strlen($raw)];
    }

    private function readIdentifier(string $expression, int $offset): array
    {
        preg_match('/\G[A-Za-z_][A-Za-z0-9_]*/', $expression, $matches, 0, $offset);
        $value = $matches[0];
        return [$value, $offset + strlen($value)];
    }

    private function parseOr(): mixed
    {
        $left = $this->parseXor();
        while ($this->matchOperator(['||', '|'])) {
            $right = (bool)$this->parseXor();
            $left = (bool)$left || $right;
        }
        return $left;
    }

    private function parseXor(): mixed
    {
        $left = $this->parseAnd();
        while ($this->matchOperator(['^'])) {
            $left = ((bool)$left) !== ((bool)$this->parseAnd());
        }
        return $left;
    }

    private function parseAnd(): mixed
    {
        $left = $this->parseEquality();
        while ($this->matchOperator(['&&', '&'])) {
            $right = (bool)$this->parseEquality();
            $left = (bool)$left && $right;
        }
        return $left;
    }

    private function parseEquality(): mixed
    {
        $left = $this->parseComparison();
        while ($operator = $this->matchOperator(['===', '!==', '==', '=', '!=', '<>'])) {
            $right = $this->parseComparison();
            $left = match ($operator) {
                '===' => $left === $right,
                '!==' => $left !== $right,
                '!=', '<>' => !$this->isEqualAfterNormalization($left, $right),
                default => $this->isEqualAfterNormalization($left, $right),
            };
        }
        return $left;
    }

    /**
     * Compares values after scalar normalization for non-strict expression operators.
     *
     * @param mixed $left Left operand produced by the expression parser.
     * @param mixed $right Right operand produced by the expression parser.
     *
     * @return bool True when both normalized operands are identical.
     */
    private function isEqualAfterNormalization(mixed $left, mixed $right): bool
    {
        [$left, $right] = $this->normalizeEqualityOperands($left, $right);
        return $left === $right;
    }

    /**
     * Normalizes scalar operands before evaluating expression equality.
     *
     * @param mixed $left Left operand produced by the expression parser.
     * @param mixed $right Right operand produced by the expression parser.
     *
     * @return array Normalized operand pair.
     */
    private function normalizeEqualityOperands(mixed $left, mixed $right): array
    {
        if (is_bool($left) || is_bool($right)) {
            return [(bool)$left, (bool)$right];
        }
        if (is_numeric($left) && is_numeric($right)) {
            $hasDecimal = str_contains((string)$left, '.') || str_contains((string)$right, '.');
            return $hasDecimal ? [(float)$left, (float)$right] : [(int)$left, (int)$right];
        }
        if ((is_scalar($left) || $left === null) && (is_scalar($right) || $right === null)) {
            return [(string)$left, (string)$right];
        }
        return [$left, $right];
    }

    private function parseComparison(): mixed
    {
        $left = $this->parseAdditive();
        while ($operator = $this->matchOperator(['>=', '<=', '>', '<'])) {
            $right = $this->parseAdditive();
            $left = match ($operator) {
                '>=' => $left >= $right,
                '<=' => $left <= $right,
                '>'  => $left > $right,
                '<'  => $left < $right,
            };
        }
        return $left;
    }

    private function parseAdditive(): mixed
    {
        $left = $this->parseMultiplicative();
        while ($operator = $this->matchOperator(['+', '-'])) {
            $right = $this->parseMultiplicative();
            $left = $operator === '+' ? $left + $right : $left - $right;
        }
        return $left;
    }

    private function parseMultiplicative(): mixed
    {
        $left = $this->parseUnary();
        while ($operator = $this->matchOperator(['*', '/', '%'])) {
            $right = $this->parseUnary();
            $left = match ($operator) {
                '*' => $left * $right,
                '/' => $left / $right,
                '%' => $left % $right,
            };
        }
        return $left;
    }

    private function parseUnary(): mixed
    {
        if ($this->matchOperator(['!'])) {
            return !$this->parseUnary();
        }
        if ($this->matchOperator(['-'])) {
            return -$this->parseUnary();
        }
        if ($this->matchOperator(['+'])) {
            return +$this->parseUnary();
        }
        if ($this->matchOperator(['~'])) {
            return ~$this->parseUnary();
        }
        return $this->parsePrimary();
    }

    private function parsePrimary(): mixed
    {
        $token = $this->next();
        if ($token === null) {
            throw new \InvalidArgumentException('Unexpected end of expression.');
        }
        if ($token['type'] === 'literal') {
            return $token['value'];
        }
        if ($token['type'] === 'identifier') {
            return match (strtolower($token['value'])) {
                'true'  => true,
                'false' => false,
                'null'  => null,
                default => ($this->resolver)($token['value']),
            };
        }
        if ($token['type'] === '(') {
            $value = $this->parseOr();
            if (!$this->consume(')')) {
                throw new \InvalidArgumentException('Missing closing parenthesis.');
            }
            return $value;
        }
        throw new \InvalidArgumentException('Unexpected token "' . $token['value'] . '".');
    }

    private function matchOperator(array $operators): ?string
    {
        $token = $this->peek();
        if ($token !== null && $token['type'] === 'op' && in_array($token['value'], $operators, true)) {
            $this->position++;
            return $token['value'];
        }
        return null;
    }

    private function consume(string $type): bool
    {
        if ($this->peek()['type'] ?? null === $type) {
            $this->position++;
            return true;
        }
        return false;
    }

    private function next(): ?array
    {
        return $this->tokens[$this->position++] ?? null;
    }

    private function peek(): ?array
    {
        return $this->tokens[$this->position] ?? null;
    }

    private function isEnd(): bool
    {
        return $this->position >= count($this->tokens);
    }
}
