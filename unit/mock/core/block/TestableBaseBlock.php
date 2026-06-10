<?php

declare(strict_types=1);

namespace FanTest\core\block;
use fan\core\base\meta\maker;
use fan\core\base\meta\row;
use fan\core\block\base;


class TestableBaseBlock extends base
{
    public static array $nextMeta = [];

    public ?object $createdMetaMaker = null;
    public array $dynamicMeta = [];
    public int|float $dynamicMetaCalls = 0;
    public mixed $dynamicMetaInput = null;
    public int|float $transferorCalls = 0;
    public int|float $postCreateCalls = 0;
    public int|float $preOutputCalls = 0;
    public int|float $roleOperationCalls = 0;

    public static function useMeta(array $meta): void
    {
        self::$nextMeta = $meta;
    }

    protected function _createMetaMaker(): object
    {
        $this->createdMetaMaker = new FakeMetaMaker($this, self::$nextMeta);
        return $this->createdMetaMaker;
    }

    public function getDynamicMeta($meta): array
    {
        $this->dynamicMetaCalls++;
        $this->dynamicMetaInput = $meta;
        return $this->dynamicMeta;
    }

    protected function _transferor(): void
    {
        $this->transferorCalls++;
    }

    protected function _postCreate(): void
    {
        $this->postCreateCalls++;
    }

    protected function _preOutput(): void
    {
        $this->preOutputCalls++;
    }

    protected function _doRoleOperations(): void
    {
        $this->roleOperationCalls++;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function exposeSetMeta(mixed $key, mixed $value): static
    {
        return $this->setMeta($key, $value);
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function exposeAddMeta(mixed $value): static
    {
        return $this->addMeta($value);
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function exposeSetMetaVar(mixed $key, mixed $value): static
    {
        return $this->setMetaVar($key, $value);
    }

    public function exposeMakeDynamicMeta($force): void
    {
        $this->_makeDynamicMeta($force);
    }

    public function exposeSetRootBlockParameters(?base $root = null, $rootKeys = []): static
    {
        return $this->_setRootBlockParameters($root, $rootKeys);
    }

    public function exposeSetTplVarsByMeta($tplVars): static
    {
        return $this->_setTplVarsByMeta($tplVars);
    }

    public function exposePreparseMeta(): static
    {
        return $this->_preparseMeta();
    }

    public function exposeSetTemplateInternal($templateName = ''): static
    {
        return $this->_setTemplate($templateName);
    }

    public function exposeGetTplSuffixes($separator = '_'): array
    {
        return $this->_getTplSuffixes($separator);
    }

    public function exposeCheckTemplate($blockPath, $templateName, $suffixes, $extension = 'tpl'): bool
    {
        return $this->_checkTemplate($blockPath, $templateName, $suffixes, $extension);
    }

    public function exposeSetEmbeddedBlocks(): static
    {
        return $this->_setEmbeddedBlocks();
    }

    public function exposeGetBlock($blockName, $allowException = true): ?object
    {
        return $this->_getBlock($blockName, $allowException);
    }

    public function exposeMakeBlockException($logErrMsg, $type = 'local', $exceptionDbOper = null, $code = E_USER_NOTICE, $previous = null): never
    {
        $this->_makeBlockException($logErrMsg, $type, $exceptionDbOper, $code, $previous);
    }

    public function exposeParseClassName($blockPath, $allowException = true): ?string
    {
        return $this->_parseClassName($blockPath, $allowException);
    }

    public function exposeSetCacheRole(mixed $role): static
    {
        return $this->_setCacheRole($role);
    }

    public function exposeCallOrdinaryDelegate($object, $method, $args): mixed
    {
        return $this->_callOrdinaryDelegate($object, $method, $args);
    }

    public function exposeCallIdentifiedDelegate($object, $method, $args): mixed
    {
        return $this->_callIdentifiedDelegate($object, $method, $args);
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function exposeSetViewVar($key, mixed $value): static
    {
        return $this->_setViewVar($key, $value);
    }

    public function setEmbeddedBlocksForTest(array $blocks): void
    {
        $property = new \ReflectionProperty('\fan\core\block\base', 'embeddedBlocks');
        $property->setValue($this, $blocks);
    }

    public function setViewForTest($view): void
    {
        $property = new \ReflectionProperty('\fan\core\block\base', 'view');
        $property->setValue($this, $view);
    }

    public function setMetaRowForTest(row $meta): void
    {
        $property = new \ReflectionProperty('\fan\core\block\base', 'meta');
        $property->setValue($this, $meta);
        $this->createdMetaMaker->meta = $meta;
        $makerRootRow = new \ReflectionProperty(maker::class, 'rootRow');
        $makerRootRow->setValue($this->createdMetaMaker, $meta);
    }
}

class FakeRootBlock extends TestableBaseBlock
{
    public array $rootCalls = [];

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function setMetaTag(mixed $value): void
    {
        $this->rootCalls[] = [__FUNCTION__, $value];
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function setExternalCss(mixed $value): void
    {
        $this->rootCalls[] = [__FUNCTION__, $value];
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function setEmbedCssByMeta(mixed $value): void
    {
        $this->rootCalls[] = [__FUNCTION__, $value];
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function setExternalJs(mixed $value): void
    {
        $this->rootCalls[] = [__FUNCTION__, $value];
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function setEmbedJs(mixed $value, $position): void
    {
        $this->rootCalls[] = [__FUNCTION__, $value, $position];
    }
}

class FakeEmbeddedBlock extends TestableBaseBlock
{
}

class ObjectWithToArray
{
    private ?array $data = null;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}

class DelegateTarget
{
    public array $calls = [];

    public function callMe(): array
    {
        $this->calls[] = func_get_args();
        return func_get_args();
    }
}
