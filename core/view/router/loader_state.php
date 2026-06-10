<?php

declare(strict_types=1);

namespace fan\core\view\router;
use fan\core\view\keeper\loader\json as loader_json;
use fan\core\view\keeper\loader\text;


class loader_state
{
    private ?loader_json $json = null;

    private ?text $text = null;

    /**
     * @var callable|null
     */
    private $jsonKeeperFactory;

    /**
     * @var callable|null
     */
    private $textKeeperFactory;

    public function __construct(?callable $jsonKeeperFactory = null, ?callable $textKeeperFactory = null)
    {
        $this->jsonKeeperFactory = $jsonKeeperFactory;
        $this->textKeeperFactory = $textKeeperFactory;
    }

    public function jsonKeeper(loader $router): loader_json
    {
        if ($this->json === null) {
            $this->json = $this->createJsonKeeper($router);
        } else {
            $this->json->addRouter($router);
        }

        return $this->json;
    }

    public function textKeeper(loader $router): text
    {
        if ($this->text === null) {
            $this->text = $this->createTextKeeper($router);
        } else {
            $this->text->addRouter($router);
        }

        return $this->text;
    }

    public function clear(): void
    {
        $this->json = null;
        $this->text = null;
    }

    private function createJsonKeeper(loader $router): loader_json
    {
        if ($this->jsonKeeperFactory === null) {
            throw new \RuntimeException('Loader JSON keeper factory is not configured.');
        }

        $keeper = ($this->jsonKeeperFactory)($router);
        if (!$keeper instanceof loader_json) {
            throw new \RuntimeException('Loader JSON keeper factory must return a loader JSON keeper.');
        }

        return $keeper;
    }

    private function createTextKeeper(loader $router): text
    {
        if ($this->textKeeperFactory === null) {
            throw new \RuntimeException('Loader text keeper factory is not configured.');
        }

        $keeper = ($this->textKeeperFactory)($router);
        if (!$keeper instanceof text) {
            throw new \RuntimeException('Loader text keeper factory must return a loader text keeper.');
        }

        return $keeper;
    }
}
