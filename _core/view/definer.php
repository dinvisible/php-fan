<?php
declare(strict_types=1);

namespace fan\core\view;
use fan\core\base\expression_evaluator;
/**
 * Definer type of View
 *
 * This file is part PHP-FAN (php-framework from Alexandr Nosov)
 * Copyright (C) 2005-2007 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *     http://www.opensource.org/licenses/lgpl-license.php
 *
 * Do not remove this comment if you want to use script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.02.001 (10.03.2014)
 */
class definer
{
    /**
     * Regexp for parse key string of rule
     */
    public const RULE_REG_EXP = '/([ACEFGHMPRS]+)\.(.+?)\.([binsr])\.(\d{1,2})/';
    /**
     * Regexp for check value of numeric type
     */
    public const NUM_REG_EXP = '/^(\=\=|\!\=|\>\=?|\<\=?)?(\-?[0-9]+(\.[0-9]+)?)$/';

    protected array $exprMaker = [
        's' => '_getStringExpr',
        'i' => '_getIntegerExpr',
        'n' => '_getNumericExpr',
        'b' => '_getBooleanExpr',
        'r' => '_getRegexpExpr',
    ];

    protected array $config = [];
    protected ?array $conditions = null;
    /**
     * @var \fan\core\service\request
     */
    protected ?object $request = null;

    /**
     * @var \fan\core\service\tab|null
     */
    protected ?object $tab = null;

    public function __construct(array $config, ?object $request = null, ?object $tab = null)
    {
        $this->config = $config;
        $this->request = $request;
        $this->tab = $tab;
        if (empty($this->config['default_format'])) {
            $this->config['default_format'] = 'html';
        }
    }
    // ======== Static methods ======== \\
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    public function getViewParserName(): string
    {
        $conditions = $this->_getConditions();
        foreach ($conditions as $k1 => $v1) {
            foreach ($v1 as $v2) {
                if ($v2()) {
                    return $k1;
                }
            }
        }
        return $this->getTab()->getTabMeta('default_view_format', $this->config['default_format']);
    }
    // ======== Private/Protected methods ======== \\
    public function _getConditions(): array
    {
        if (is_null($this->conditions)) {
            if (is_null($this->request)) {
                throw new \RuntimeException('Request service is not configured for view definer.');
            }
            $this->conditions = [];
            $matches    = null;
            foreach ($this->config['rule'] ?? [] as $k1 => $v1) { // $k1 - view format
                foreach ($v1 as $k2 => $v2) { // $k2 - index of rule // $v2 - string of rule
                    $v2 = (string)$v2;
                    if (preg_match_all(self::RULE_REG_EXP, $v2, $matches)) {
                        $search = $replace = $resolver = []; // Array Search/Replace for convert check rule to execute
                        foreach ($matches[3] as $k3 => $v3) { // $k3 - index of rule-string // $v3 - element of rule-string
                            // Data sources
                            $src = $matches[1][$k3];
                            // Data key
                            $key = $matches[2][$k3];

                            // Validate Value for rule
                            $val = $this->_getConditionValue($matches[4][$k3] ?? null, $v3, $v2);
                            if (is_null($val)) {
                                continue 2;
                            }

                            // Expression for Validate
                            $method = $this->exprMaker[$v3] ?? null;
                            if (empty($method) || !method_exists($this, $method)) {
                                throw new \UnexpectedValueException('Unknown metod key <b>' . $v3 . '</b><br /> for VIEW-rule:<br /><b>' . $v2 . '</b>.');
                            }
                            $ruleKey = '__rule_' . $k3;

                            $search[$k3]  = $matches[0][$k3];
                            $replace[$k3] = $ruleKey;
                            $resolver[$ruleKey] = $this->$method($key, $src, $val);
                        }
                        $finalExpr = str_replace($search, $replace, $v2);
                        try {
                            expression_evaluator::evaluate($finalExpr, function ($name) use ($resolver) {
                                if (!array_key_exists($name, $resolver)) {
                                    throw new \InvalidArgumentException('Unknown VIEW-rule token "' . $name . '".');
                                }
                                return false;
                            });
                            $this->conditions[$k1][$k2] = function () use ($finalExpr, $resolver) {
                                return (bool)expression_evaluator::evaluate($finalExpr, function ($name) use ($resolver) {
                                    if (!array_key_exists($name, $resolver)) {
                                        throw new \InvalidArgumentException('Unknown VIEW-rule token "' . $name . '".');
                                    }
                                    return $resolver[$name]();
                                });
                            };
                        } catch (\Throwable $e) {
                            throw new \UnexpectedValueException('Incorrect PHP-expression:<br /><b>' . $finalExpr . '</b><br /> in VIEW-rule:<br /><b>' . $v2 . '</b>.', 0, $e);
                        }
                    } else {
                        throw new \UnexpectedValueException('Can\'t parse VIEW-rule:<br /><b>' . $v2 . '</b>.');
                    }
                }
            }
        }
        return $this->conditions;
    }

    protected function getTab(): object
    {
        if (is_object($this->tab)) {
            return $this->tab;
        }

        throw new \RuntimeException('Tab service is not configured for view definer.');
    }

    protected function _getConditionValue(mixed $val, $v3, $v2): mixed
    {
        if (is_null($val)) {

        }
        if ((string)$v3 === 'b') {
            return (bool)$val;
        }
        if ((string)$v3 === 'i') {
            return (int)$val;
        }
        // Need data value
        if (!isset($this->config['value'][$val])) {
            throw new \UnexpectedValueException('Value doesn\'t set for VIEW-rule:<br /><b>' . $v2 . '</b>.');
        }
        $val = $this->config['value'][$val];
        if ((string)$v3 === 'n' && !preg_match(self::NUM_REG_EXP, (string)$val)) {
            throw new \UnexpectedValueException('Incorrect value:<br /><b>' . $val . '</b><br /> for VIEW-rule:<br /><b>' . $v2 . '</b>.');
        }
        return $val;
    }

    protected function _getStringExpr(string $key, string $src, mixed $val): \Closure
    {
        return fn() => (string)$this->request->get($key, $src) === (string)$val;
    }

    protected function _getIntegerExpr(string $key, string $src, mixed $val): \Closure
    {
        return fn() => (int)$this->request->get($key, $src) === (int)$val;
    }

    protected function _getNumericExpr(string $key, string $src, mixed $val): \Closure
    {
        $matches = [];
        preg_match(self::NUM_REG_EXP, (string)$val, $matches);
        $operator = empty($matches[1]) ? '==' : $matches[1];
        $expected = str_contains($matches[2], '.') ? (float)$matches[2] : (int)$matches[2];
        return function () use ($key, $src, $operator, $expected) {
            $actual = is_float($expected) ? (float)$this->request->get($key, $src) : (int)$this->request->get($key, $src);
            return match ($operator) {
                '!=' => $actual !== $expected,
                '>=' => $actual >= $expected,
                '<=' => $actual <= $expected,
                '>'  => $actual > $expected,
                '<'  => $actual < $expected,
                default => $actual === $expected,
            };
        };
    }

    protected function _getBooleanExpr(string $key, string $src, mixed $val): \Closure
    {
        return fn() => (bool)$this->request->get($key, $src) === (bool)$val;
    }

    protected function _getRegexpExpr(string $key, string $src, mixed $val): \Closure
    {
        return fn() => (bool)preg_match((string)$val, (string)$this->request->get($key, $src));
    }

}
