<?php

declare(strict_types=1);

namespace fan\core\adapter;

class php_mailer
{
    private object $mailer;

    public function __construct(bool $exceptions = false)
    {
        if (class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
            $class = \PHPMailer\PHPMailer\PHPMailer::class;
            $this->mailer = new $class($exceptions);
            return;
        }

        throw new \RuntimeException('PHPMailer is not available. Install phpmailer/phpmailer with Composer.');
    }

    public function __set(string $name, mixed $value): void
    {
        $this->mailer->{$name} = $value;
    }

    public function __get(string $name): mixed
    {
        return $this->mailer->{$name} ?? null;
    }

    public function __call(string $method, array $arguments): mixed
    {
        $methodMap = [
            'AddAddress' => 'addAddress',
            'ClearAddresses' => 'clearAddresses',
            'ClearAttachments' => 'clearAttachments',
            'IsHTML' => 'isHTML',
            'IsMail' => 'isMail',
            'IsSendmail' => 'isSendmail',
            'IsSMTP' => 'isSMTP',
            'SetLanguage' => 'setLanguage',
        ];

        $method = $methodMap[$method] ?? $method;
        if (!method_exists($this->mailer, $method)) {
            throw new \BadMethodCallException('Unknown PHPMailer method "' . $method . '".');
        }

        return $this->mailer->{$method}(...$arguments);
    }
}
