<?php

declare(strict_types=1);

namespace FanTest\_core\exception;

class TestException extends \fan\core\exception\base
{
    public mixed $scalar = 'visible';
    public mixed $null = null;
    public array $array = ['x' => 1];
    public ?object $object = null;
    private ?string $nextDbOper = null;

    public function __construct($message, $nextDbOper = null, $code = E_USER_ERROR, ?\Exception $previous = null)
    {
        $this->nextDbOper = $nextDbOper;
        $this->object = new \stdClass();
        parent::__construct($message, $code, $previous);
    }

    protected function _defineDbOper($dbOper = null): ?string
    {
        return parent::_defineDbOper($this->nextDbOper);
    }

    public function exposeDefineDbOper($dbOper): ?string
    {
        return parent::_defineDbOper($dbOper);
    }
}
