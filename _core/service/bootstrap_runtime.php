<?php

declare(strict_types=1);

namespace fan\core\service;
use fan\core\base\meta\maker_state;
use fan\core\base\model\spec_file\image\row_state;
use fan\core\bootstrap\initializer;
use fan\core\bootstrap\loader;
use fan\core\bootstrap\runner;
use fan\core\view\router\loader_state;


class bootstrap_runtime
{
    private ?service_listener_state $serviceListenerState;

    private ?service_single_state $serviceSingleState;

    private ?loader_state $viewLoaderState;

    private ?maker_state $metaMakerState;

    private ?row_state $specFileImageRowState;

    /**
     * @var callable|null
     */
    private $serviceEngineFactory;

    /**
     * @var callable|null
     */
    private $serviceExceptionFactory;
    /**
     * @var callable|null
     */
    private $classNameResolver;
    /**
     * @var callable|null
     */
    private $arrayValueReader;

    /**
     * @var array<string, callable>
     */
    private array $bootstrapOperations;

    public function __construct(
        ?service_listener_state $serviceListenerState = null,
        ?service_single_state $serviceSingleState = null,
        ?loader_state $viewLoaderState = null,
        ?maker_state $metaMakerState = null,
        ?row_state $specFileImageRowState = null,
        ?callable $serviceEngineFactory = null,
        array $bootstrapOperations = [],
        ?callable $serviceExceptionFactory = null,
        ?callable $classNameResolver = null,
        ?callable $arrayValueReader = null
    )
    {
        $this->serviceListenerState = $serviceListenerState;
        $this->serviceSingleState = $serviceSingleState;
        $this->viewLoaderState = $viewLoaderState;
        $this->metaMakerState = $metaMakerState;
        $this->specFileImageRowState = $specFileImageRowState;
        $this->serviceEngineFactory = $serviceEngineFactory;
        $this->serviceExceptionFactory = $serviceExceptionFactory;
        $this->classNameResolver = $classNameResolver;
        $this->arrayValueReader = $arrayValueReader;
        $this->bootstrapOperations = $bootstrapOperations;
    }

    public function serviceListenerState(): service_listener_state
    {
        if ($this->serviceListenerState !== null) {
            return $this->serviceListenerState;
        }

        throw new \RuntimeException('Bootstrap runtime service listener state is not configured.');
    }

    public function serviceSingleState(): service_single_state
    {
        if ($this->serviceSingleState !== null) {
            return $this->serviceSingleState;
        }

        throw new \RuntimeException('Bootstrap runtime service single state is not configured.');
    }

    public function viewLoaderState(): loader_state
    {
        if ($this->viewLoaderState !== null) {
            return $this->viewLoaderState;
        }

        throw new \RuntimeException('Bootstrap runtime view loader state is not configured.');
    }

    public function metaMakerState(): maker_state
    {
        if ($this->metaMakerState !== null) {
            return $this->metaMakerState;
        }

        throw new \RuntimeException('Bootstrap runtime meta maker state is not configured.');
    }

    public function specFileImageRowState(): row_state
    {
        if ($this->specFileImageRowState !== null) {
            return $this->specFileImageRowState;
        }

        throw new \RuntimeException('Bootstrap runtime spec-file image row state is not configured.');
    }

    public function serviceEngineFactory(): callable
    {
        if ($this->serviceEngineFactory !== null) {
            return $this->serviceEngineFactory;
        }

        throw new \RuntimeException('Service engine factory is not configured.');
    }

    public function serviceExceptionFactory(): callable
    {
        if ($this->serviceExceptionFactory !== null) {
            return $this->serviceExceptionFactory;
        }

        throw new \RuntimeException('Service exception factory is not configured.');
    }

    public function classNameResolver(): callable
    {
        if ($this->classNameResolver !== null) {
            return $this->classNameResolver;
        }

        throw new \RuntimeException('Class name resolver is not configured.');
    }

    public function arrayValueReader(): callable
    {
        if ($this->arrayValueReader !== null) {
            return $this->arrayValueReader;
        }

        throw new \RuntimeException('Array value reader is not configured.');
    }

    public function getLoader(): loader
    {
        return ($this->bootstrapOperation('getLoader'))();
    }

    public function getRunner(): runner
    {
        return ($this->bootstrapOperation('getRunner'))();
    }

    public function getInitializer(): initializer
    {
        return ($this->bootstrapOperation('getInitializer'))();
    }

    public function parsePath(string $path): string
    {
        return ($this->bootstrapOperation('parsePath'))($path);
    }

    public function loadClass(string $class, bool $makeAlias = true): mixed
    {
        return ($this->bootstrapOperation('loadClass'))($class, $makeAlias);
    }

    public function logError(string $message): void
    {
        ($this->bootstrapOperation('logError'))($message);
    }

    public function handleError(
        int|float $errNo,
        string $errMsg,
        ?string $fileName = null,
        int|float|null $lineNum = null,
        mixed $errContext = null
    ): ?bool {
        return ($this->bootstrapOperation('handleError'))($errNo, $errMsg, $fileName, $lineNum, $errContext);
    }

    public function getGlobalPath(string $key, mixed $altPath = null): ?string
    {
        return ($this->bootstrapOperation('getGlobalPath'))($key, $altPath);
    }

    public function getConfigCache(): array
    {
        return ($this->bootstrapOperation('getConfigCache'))();
    }

    public function getPid(): string
    {
        return ($this->bootstrapOperation('getPid'))();
    }

    public function isCli(): bool
    {
        return ($this->bootstrapOperation('isCli'))();
    }

    private function bootstrapOperation(string $name): callable
    {
        if (isset($this->bootstrapOperations[$name])) {
            return $this->bootstrapOperations[$name];
        }

        throw new \InvalidArgumentException('Unknown bootstrap runtime operation "' . $name . '".');
    }
}
