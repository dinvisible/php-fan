<?php
declare(strict_types=1);

namespace fan\core\service\template\type;
/**
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
 * @version of file: 05.02.004 (25.12.2014)
 */
abstract class base implements \ArrayAccess
{
    use \fan\core\di\container_aware_trait;

    protected ?array $tplVar = null;

    /**
     * @var \fan\core\block\base Object of block
     */
    protected ?object $block = null;

    protected ?string $blockName = null;

    protected string $fullHTML = '';

    private array $exceptVar = ['this', 'block', 'tab', 'assignTplKey', 'assignTplVal', 'returnHtmlVal'];

    private array $objectData = [];

    public function __construct(?\fan\core\block\base $block = null)
    {
        if (is_object($block) && $block instanceof \fan\core\block\base) {
            $this->block = $block;
            if (method_exists ($block, 'getBlockName')) {
                $this->blockName = $block->getBlockName();
            }

            $this->tplVar['block']  = $block;
            $this->tplVar['oBlock'] = $block;
            $this->tplVar['tab']    = $block->getTab();
        }
    }

    // ======== Static methods ======== \\
    // ======== The magic methods ======== \\
    /**
     * Handles dynamic property writes for this current component.
     *
     * @param mixed $value Value that should be applied or transformed.
     */
    public function __set(string $key, mixed $value): void
    {
        $this->offsetSet((string)$key, $value);
    }

    /**
     * Handles dynamic property reads for this current component.
     */
    public function __get(string $key): mixed
    {
        return $this->offsetGet((string)$key);
    }

    // ======== Required Interface methods ======== \\
    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        $method = 'set';
        $key = (string)$key;
        foreach (explode('_', $key) as $v) {
            $method .= ucfirst($v);
        }
        if (method_exists($this, $method)) {
            $this->$method($value);
        } else {
            $this->tplVar[$key] = $value;
        }
    }

    public function offsetExists(mixed $key): bool
    {
        $key = (string)$key;
        return !empty($this->tplVar[$key]);
    }

    public function offsetUnset(mixed $key): void
    {
        $key = (string)$key;
        unset($this->tplVar[$key]);
    }

    public function offsetGet(mixed $key): mixed
    {
        $method = 'get';
        $key = (string)$key;
        foreach (explode('_', $key) as $v) {
            $method .= ucfirst($v);
        }
        return method_exists($this, $method) ? $this->$method() : $this->tplVar[$key];
    }

    // ======== Main Interface methods ======== \\
    public static function getEngineList(): array
    {
        return ['main'];
    }

    public static function getAutoParseTag(): array
    {
        return [];
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function assign(string $key, mixed $value): void
    {
        $this->checkVars($key);
        $this->tplVar[$key] = $value;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function assignByRef(string $key, mixed &$value): void
    {
        $this->checkVars($key);
        $this->tplVar[$key] = &$value;
    }


    public function clearAssign(string $key): void
    {
        $this->checkVars($key);
        unset($this->tplVar[$key]);
    }

    public function getVars(mixed $key = null): mixed
    {
        return is_null($key) ? $this->tplVar : ($this->tplVar[$key] ?? null);
    }

    public function fetch(): string
    {
        $this->fullHTML = (string)$this->parseHtml();
        return $this->fullHTML;
    }

    // ======== Private/Protected methods ======== \\
    /**
     * Transforms html between supported representations.
     */
    abstract protected function parseHtml(): mixed;

    protected function &linkForAssign(string $key): mixed
    {
        $this->checkVars($key);
        return $this->tplVar[$key];
    }

    protected function getIteration(string $key): int
    {
        return $this->objectData[$key]['iteration'];
    }

    protected function getTotal(string $key): int
    {
        return count($this->objectData[$key]['data']);
    }

    protected function isFirst(string $key): bool
    {
        return $this->objectData[$key]['iteration'] < 2;
    }

    protected function isLast(string $key): bool
    {
        return isset($this->objectData[$key]['data']) ? (int)$this->objectData[$key]['iteration'] === count($this->objectData[$key]['data']) : false;
    }

    protected function isEven(string $key, bool $isBoolean = false): bool|int
    {
        $ret = is_null($this->objectData[$key]['data']) ? false : (int)$this->objectData[$key]['iteration'] % 2 === 0;
	return ($isBoolean ? $ret : ($ret ? 1 : 0));
    }

    protected function makeTagAttr(string $attr, mixed $data, mixed $key = null): string
    {
        if (is_object($data)) {
            $val = empty($data->$key) ?
                (method_exists($data, '__toString') ? $data->__toString() : '') :
                $data->$key;
        } else {
            $val = is_array($data) ? array_val($data, is_null($key) ? $attr : $key) : $data;
        }
        return empty($val) ? '' : ' ' . $attr . '="' . $val . '"';
    }

    final protected function setObjectData(string $type, string $key, mixed &$data = null): void
    {
        $key = (string)$key;
        $this->objectData[$key] = ['type' => $type, 'iteration' => 0];
        $this->objectData[$key]['data'] = &$data;
    }

    final protected function setIteration(string $key): void
    {
        $key = (string)$key;
        $this->objectData[$key]['iteration']++;
    }

    private function checkVars($key): void
    {
        $key = (string)$key;
        if (in_array($key, $this->exceptVar)) {
            throw new \fan\project\exception\template\fatal($this, 'Incorrecn key name "' . $key . '". It is reserved name.');
        }
    }
}
