<?php

declare(strict_types=1);

namespace fan\core\service;

class request_input
{
    public function __construct(private object $source)
    {
    }

    public function globalArray(string $name): array
    {
        return $this->source->globalArray($name);
    }

    public function globalValue(string $name, mixed $default = null): mixed
    {
        return $this->source->globalValue($name, $default);
    }

    public function unsetGlobalValue(string $name, mixed $key): void
    {
        $this->source->unsetGlobalValue($name, $key);
    }

    public function get(): array
    {
        return $this->globalArray('_GET');
    }

    public function request(): array
    {
        return $this->globalArray('_REQUEST');
    }

    public function requestValue(string $key, mixed $default = null): mixed
    {
        $request = $this->request();

        return $request[$key] ?? $default;
    }

    public function serverValue(string $key, mixed $default = null): mixed
    {
        return $this->source->serverValue($key, $default);
    }

    public function server(): array
    {
        return $this->globalArray('_SERVER');
    }

    public function &sessionRoot(): array
    {
        return $this->source->sessionRoot();
    }

    public function &sessionValue(string $group, string $name): mixed
    {
        $session =& $this->sessionRoot();
        if (!isset($session[$group]) || !is_array($session[$group])) {
            $session[$group] = [$name => null];
        } elseif (!array_key_exists($name, $session[$group])) {
            $session[$group][$name] = null;
        }

        return $session[$group][$name];
    }

    public function headers(): array
    {
        $headers = $this->source->apacheHeaders();
        if (is_array($headers)) {
            return $headers;
        }

        $headers = [];
        foreach ($this->server() as $key => $value) {
            if (substr((string)$key, 0, 5) !== 'HTTP_') {
                continue;
            }
            $nameParts = explode('_', substr((string)$key, 5));
            foreach ($nameParts as &$namePart) {
                $namePart = ucfirst(strtolower($namePart));
            }
            $headers[implode('-', $nameParts)] = $value;
        }

        return $headers;
    }

    public function argv(): array
    {
        return $this->globalArray('argv');
    }

    public function rawPost(): string
    {
        return $this->source->rawPost();
    }
}
