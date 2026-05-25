<?php

declare(strict_types=1);

namespace FanTest\_core\exception;
use fan\core\exception\base;


class TestException extends base
{
    public mixed $scalar = 'visible';
    public mixed $null = null;
    public array $array = ['x' => 1];
    public ?object $object = null;
    private ?string $nextDbOper = null;

    public function __construct(
        $message,
        $nextDbOper = null,
        $code = E_USER_ERROR,
        ?\Exception $previous = null,
        ?object $databaseConnections = null,
        ?object $runtimeLogger = null,
        ?object $requestService = null,
        ?object $errorService = null,
        ?object $headerWriter = null
    )
    {
        $this->nextDbOper = $nextDbOper;
        $this->object = new \stdClass();
        parent::__construct($message, $code, $previous, $databaseConnections, $runtimeLogger, $requestService, $errorService, $headerWriter);
    }

    protected function _defineDbOper($dbOper = null): ?string
    {
        return parent::_defineDbOper($this->nextDbOper);
    }

    public function exposeDefineDbOper($dbOper): ?string
    {
        return parent::_defineDbOper($dbOper);
    }

    public function exposeLogByPhp(string $message, bool $exceptPos = true): static
    {
        return parent::_logByPhp($message, $exceptPos);
    }

    public function exposeLogByService(string $message, string $title = '', string $note = ''): static
    {
        return parent::_logByService($message, $title, $note);
    }

    public function exposeSendInternalServerErrorHeader(): void
    {
        parent::sendInternalServerErrorHeader();
    }
}
