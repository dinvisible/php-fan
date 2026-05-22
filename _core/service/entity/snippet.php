<?php

declare(strict_types=1);

namespace fan\core\service\entity;
use fan\core\base\expression_evaluator;
use fan\project\exception\model\entity\fatal as fatalException;
/**
 * Description of SQL-snippet
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
class snippet
{
    /**
     * Condition Regular Expressions
     * @var string
     */
    protected string $conditionRe = '/([\+\-\*\/%\(\)=.!&|\^~<>\s]*)(?:(exist|filled|val)\{\$(\w+)\}|const\[\s*([\-\.\d]+|\'.*?(?<!\\\\)\')\s*\])([\+\-\*\/%\(\)=.!&|\^~<>\s]*)/';
    /**
     * Place-Holders Regular Expressions
     * @var string
     */
    protected string $placeHoldersRe = '/\{\$(\w+)\}/';
    /**
     * Instance of Snippety-designer
     * @var \fan\core\service\entity\designer\snippety
     */
    protected ?object $snippety = null;
    /**
     * Entity - table data
     * @var \fan\core\base\model\entity
     */
    protected ?object $entity = null;

    /**
     * Snippet of SQL-request (sourse SQL-snippet)
     * @var string
     */
    protected ?string $query = null;

    /**
     * Source Condition-string
     * @var string
     */
    protected ?string $srcCondition = null;

    /**
     * Parsed condition expression.
     * @var string
     */
    protected ?string $condition = null;

    /**
     * List of Data-Keys used for this SQL-snippet
     * @var array
     */
    protected array $usedKeys = [];

    /**
     * Callback function/method
     * @var string|array
     */
    protected mixed $callback = null;


    /**
     * @param mixed $callback Callable invoked to complete the delegated operation.
     */
    public function __construct(\fan\core\service\entity\designer\snippety $snippety, mixed $query, mixed $srcCondition, mixed $callback)
    {
        $this->snippety     = $snippety;
        $this->entity       = $snippety->getEntity();
        $this->srcCondition = (string)$srcCondition;

        $this->condition = $this->_parseCondition((string)$srcCondition);
        $this->usedKeys  = $this->_parsePlaceHolders((string)$query);
        $this->callback  = $this->_parseCallback($callback);
    }

    // ======== Static methods ======== \\

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    public function getSnippetQuery(array $data): array
    {
        $isValid  = $this->_checkCondition($data);
        if ($this->callback) {
            list($query, $usedData) = call_user_func($this->callback, $this, $data, $isValid);
        } else {
            $query = $isValid ? $this->query : '';
            $usedData = $isValid ? $this->prepareData($query, $data) : [];
        }
        return [$query, empty($usedData) ? [] : $usedData];
    }

    /**
     * @throws fatalException
     */
    public function prepareData(string &$query, array $data, ?array $usedKeys = null): array
    {
        $query = (string)$query;
        if (is_null($usedKeys)) {
            $usedKeys = $this->usedKeys;
        }

        $qttQuery = substr_count($query, '?');
        if ($qttQuery > count($usedKeys)) {
            throw new fatalException($this->getEntity(), 'Quantity of "question mark" more than UsedKeys.');
        }

        $adjustedParam = [];
        for ($i = 0; $i < $qttQuery; $i++) {
            $key = $usedKeys[$i];
            if (!isset($data[$key])) {
                array_push($adjustedParam, null);
            } elseif (!is_array($data[$key])) {
                array_push($adjustedParam, $data[$key]);
            } elseif (preg_match('/^((?:[^?]*\?){' . ($i + 1) . '})(.*)$/', $query, $matches)) {
                $query = substr($matches[1], 0, -1) . implode(',', array_fill(0, count($data[$key]), ' ?')) . $matches[2];
                $adjustedParam = array_merge($adjustedParam, array_values($data[$key]));
            } else {
                throw new fatalException($this->getEntity(), 'Can\'t parse SQL-snippet');
            }
        }
        return $adjustedParam;
    }
    public function getSql(): ?string
    {
        return $this->query;
    }
    public function getSrcCondition(): ?string
    {
        return $this->srcCondition;
    }
    public function getCondition(): ?string
    {
        return $this->condition;
    }
    public function getUsedKeys(): array
    {
        return $this->usedKeys;
    }
    public function getCallback(): mixed
    {
        return $this->callback;
    }

    public function getSnippety(): ?\fan\core\service\entity\designer\snippety
    {
        return $this->snippety;
    }
    public function getEntity(): ?\fan\core\base\model\entity
    {
        return $this->entity;
    }

    // ======== Private/Protected methods ======== \\
    /**
     * @throws fatalException
     */
    protected function _parseCondition(string $condition): string
    {
        if (trim($condition) === '') {
            return 'true';
        }

        $result = '';
        $offset = 0;
        $matches = [];
        if (preg_match_all($this->conditionRe, $condition, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $k => $v) {
                [$match, $matchOffset] = $v;
                if ($matchOffset !== $offset) {
                    throw new fatalException($this->getEntity(), 'Incorrect SQL-Condition: "' . $condition . '".');
                }

                $result .= $matches[1][$k][0];
                if (empty($matches[2][$k][0])) {
                    $result .= $matches[4][$k][0];
                } else {
                    $result .= '__' . $matches[2][$k][0] . '_' . $matches[3][$k][0];
                }
                $result .= $matches[5][$k][0];
                $offset = $matchOffset + strlen($match);
            }

            if ($offset !== strlen($condition)) {
                throw new fatalException($this->getEntity(), 'Incorrect SQL-Condition: "' . $condition . '".');
            }
        } else {
            throw new fatalException($this->getEntity(), 'Incorrect SQL-Condition: "' . $condition . '".');
        }

        try {
            expression_evaluator::evaluate($result, fn() => null);
        } catch (\Throwable $e) {
            throw new fatalException($this->getEntity(), 'Incorrect SQL-Condition: "' . $condition . '".');
        }
        return $result;
    }

    protected function _parsePlaceHolders(string $query): array
    {
        $result  = [];
        $matches = [];
        if (preg_match_all($this->placeHoldersRe, $query, $matches)) {
            foreach ($matches[0] as $k => $v) {
                $result[] = $matches[1][$k];
                $query = str_replace($v, '?', $query);
            }
        }

        $this->query = $query;
        return $result;
    }

    /**
     * @param string $callback Callable invoked to complete the delegated operation.
     *
     * @throws fatalException
     */
    protected function _parseCallback(mixed $callback): mixed
    {
        if (empty($callback)) {
            return null;
        }
        if (is_callable($callback)) {
            return $callback;
        }
        if (!is_scalar($callback)) {
            throw new fatalException($this->getEntity(), 'Incorrect Callback function.');
        }

        $callback = (string)$callback;

        $entity = $this->getEntity();
        if (strpos($callback, ':')) {
            $callback = explode(':', $callback);
        } else {
            $callback = [$entity->getRequestLoader(), $callback];
            if (is_callable($callback)) {
                return $callback;
            }
            $callback = [$entity, $callback];
            if (is_callable($callback)) {
                return $callback;
            }
            $callback = $callback;
        }

        if (!is_callable($callback)) {
            throw new fatalException($entity, 'Incorrect Callback function: "' . $callback . '".');
        }
        return $callback;
    }

    protected function _checkCondition(array $data): bool
    {
        try {
            return (bool)expression_evaluator::evaluate((string)$this->condition, function ($name) use ($data) {
                if (!preg_match('/^__(exist|filled|val)_(\w+)$/', $name, $matches)) {
                    throw new \InvalidArgumentException('Unknown SQL-condition token "' . $name . '".');
                }

                $key = $matches[2];
                return match ($matches[1]) {
                    'exist' => array_key_exists($key, $data),
                    'filled' => !empty($data[$key]),
                    'val' => $data[$key] ?? null,
                };
            });
        } catch (\Throwable $e) {
            throw new fatalException($this->getEntity(), 'Incorrect SQL-Condition: "' . $this->srcCondition . '".');
        }
    }
}
