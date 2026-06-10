<?php

declare(strict_types=1);

namespace FanTest\core\base;
use fan\core\base\data;


class TestData extends data
{
    public function __construct(
        mixed $data = null,
        int|string|null $key = null,
        ?data $superior = null,
        ?object $errorLogger = null,
        ?callable $subDataFactory = null,
        ?callable $snapshotEncoder = null,
        ?callable $snapshotDecoder = null,
        ?callable $classNameResolver = null
    ) {
        if ($subDataFactory === null && $superior === null) {
            $subDataFactory = static fn(
                mixed $value,
                int|string|null $key,
                data $superior
            ): self => new self($value, $key, $superior);
        }

        parent::__construct(
            $data,
            $key,
            $superior,
            $errorLogger,
            $subDataFactory,
            $snapshotEncoder,
            $snapshotDecoder,
            $classNameResolver
        );
    }

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

    public function exposeErrorLogger(): ?object
    {
        return $this->errorLogger;
    }
}

class TestDataSetter
{
    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function setValue(TestData $data, $key, mixed $value): data
    {
        return $data->set($key, $value);
    }

    public function unsetValue(TestData $data, $key): void
    {
        unset($data->$key);
    }
}

class TestDataWithoutDefaultSubDataFactory extends data
{
}

class DataErrorLoggerDouble
{
    public array $messages = [];

    public function logErrorMessage($message, $title = '', $note = '', $fixPosition = false): void
    {
        $this->messages[] = [$message, $title, $note, $fixPosition];
    }
}
