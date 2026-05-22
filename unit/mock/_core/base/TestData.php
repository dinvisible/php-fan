<?php

declare(strict_types=1);

namespace FanTest\_core\base;

class TestData extends \fan\core\base\data
{
    public function allowSetter(mixed $setter): mixed
    {
        return $this->_setSetter($setter);
    }

    public function setMultiLevel($multiLevel): void
    {
        $this->multiLevel = $multiLevel;
    }

    public function exposeSubData(): mixed
    {
        return $this->_getSubData();
    }
}

class TestDataSetter
{
    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function setValue(TestData $data, $key, mixed $value): \fan\core\base\data
    {
        return $data->set($key, $value);
    }

    public function unsetValue(TestData $data, $key): void
    {
        unset($data->$key);
    }
}
