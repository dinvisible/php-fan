<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\core\base\service\multi;


/**
 * Cookie service
 *
 * This file is part PHP-FAN (php-framework of Alexandr Nosov)
 * Copyright (C) 2005-2007 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *     http://www.opensource.org/licenses/lgpl-license.php
 *
 * Do not remove this comment if you want to use script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.02.001 (10.03.2014)
 */
class cookie extends multi
{
    protected ?string $path = null;

    protected ?string $domain = null;

    protected bool $secure = false;

    protected bool $httpOnly = false;

    private ?object $input = null;

    private mixed $errorFactory = null;
    private ?object $state = null;
    private \Closure $cookieValueEncoder;
    private \Closure $cookieValueDecoder;
    private \Closure $cookieValueChecker;
    private ?object $cookieWriter = null;

    public function __construct(
        mixed $path,
        mixed $domain,
        bool $secure,
        ?object $input = null,
        ?callable $errorFactory = null,
        ?callable $cookieValueEncoder = null,
        ?callable $cookieValueDecoder = null,
        ?callable $cookieValueChecker = null,
        ?object $cookieWriter = null,
        ?object $state = null,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null
    )
    {
        $this->input = $input;
        $this->errorFactory = $errorFactory;
        $this->cookieValueEncoder = \Closure::fromCallable(
            $cookieValueEncoder ?? static function (mixed $value): string {
                throw new \RuntimeException('Cookie value encoder is not configured for cookie service.');
            }
        );
        $this->cookieValueDecoder = \Closure::fromCallable(
            $cookieValueDecoder ?? static function (
                string $payload,
                mixed $default = null,
                ?callable $onError = null,
                bool $returnOriginalOnLegacyFailure = false
            ): mixed {
                throw new \RuntimeException('Cookie value decoder is not configured for cookie service.');
            }
        );
        $this->cookieValueChecker = \Closure::fromCallable($cookieValueChecker ?? static fn(string $payload): bool => true);
        $this->cookieWriter = $cookieWriter;
        $this->state = $state ?? throw new \RuntimeException('Cookie state is not configured for cookie service.');
        parent::__construct(false, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory);

        $this->path   = is_null($path) ? null : (string)$path;
        $this->domain = is_null($domain) ? null : (string)$domain;
        $this->secure = (bool)$secure;
        $this->state()->initializeData($this->input()->globalArray('_COOKIE'));
    }

    public function get(string $name, ?string $defaultVal = null): mixed
    {
        if (!$this->state()->hasData($name)) {
            return $defaultVal;
        }

        return $this->decodeCookieValue((string)$this->state()->getData($name), $defaultVal);
    }

    /**
     * Transforms cookie value between supported representations.
     *
     * @param mixed $value Value that should be applied or transformed.
     */
    private function encodeCookieValue(mixed $value): string
    {
        try {
            return ($this->valueEncoder())($value);
        } catch (\InvalidArgumentException $e) {
            throw $this->createServiceFatalException('Cookie value contains data unsupported by JSON.');
        } catch (\JsonException $e) {
            throw $this->createServiceFatalException('Cookie value isn\'t JSON serializable: ' . $e->getMessage());
        }
    }

    /**
     * Transforms cookie value between supported representations.
     *
     * @param string $value Value that should be applied or transformed.
     * @param mixed $defaultVal Fallback value returned when no explicit value is available.
     */
    private function decodeCookieValue(string $value, mixed $defaultVal = null): mixed
    {
        if (!($this->cookieValueChecker)($value)) {
            return $value;
        }

        return ($this->valueDecoder())(
            $value,
            $defaultVal,
            function (string $message): void {
                $this->errorLogger()->logErrorMessage($message, 'Cookie JSON decode error', '', true, false);
            },
            true
        );
    }

    public function getAll(?string $defaultVal = null): array
    {
        $result = [];
        foreach ($this->state()->getAllData() as $k => $v) {
            $result[$k] = $this->get($k, $defaultVal);
        }
        return $result;
    }

    public function set(string $name, mixed $value): bool
    {
        return $this->setByTime($name, $value, 0);
    }

    public function setByTime(string $name, mixed $value, int $time): bool
    {
        $cookieValue = $time < 0 ? '' : $this->encodeCookieValue($value);

        if ($this->cookieWriter()->write($name, $cookieValue, ($time ? $time + time() : 0), (string)$this->path, (string)$this->domain, $this->secure, $this->httpOnly)) {
            if ($time < 0) {
                $this->state()->deleteData($name);
            } elseif (!$this->secure || !empty($this->input()->serverValue('HTTPS'))) {
                $this->state()->setData($name, $cookieValue);
            }
            return true;
        }

        return false;
    }

    public function setByDate(string $name, mixed $value, string $date): bool
    {
        $date = trim($date);
        if (empty($date)) {
            throw $this->createServiceFatalException('Date isn\'t set');
        }

        $matches = [];
        if (preg_match('/^((\d{4})\-(\d{2})\-(\d{2}))?\s?((\d{2})\:(\d{2})\:(\d{2}))?$/', $date, $matches)) {
            if (empty($matches[1])) {
                $matches[2] = date('Y');
                $matches[3] = date('m');
                $matches[4] = date('d');
            }
            if (empty($matches[5])) {
                $matches[6] = 23;
                $matches[7] = 59;
                $matches[8] = 59;
            }

            $time = mktime((int)$matches[6], (int)$matches[7], (int)$matches[8] - 1, (int)$matches[3], (int)$matches[4], (int)$matches[2]) - time();
            return $this->setByTime($name, $value, $time);
        }
        throw $this->createServiceFatalException('Date contains incorrect format: "' . $date . '"');
    }

    public function delete(string $name): bool
    {
        return $this->setByTime($name, null, -86400);
    }

    public function setHttpOnlyFlag(bool $httpOnly): void
    {
        $this->httpOnly = !empty($httpOnly);
    }

    private function input(): object
    {
        if ($this->input === null) {
            throw new \RuntimeException('Request input service is not configured for cookie service.');
        }

        return $this->input;
    }

    private function state(): object
    {
        if ($this->state === null) {
            throw new \RuntimeException('Cookie state is not configured for cookie service.');
        }

        return $this->state;
    }

    private function errorLogger(): object
    {
        if (!is_callable($this->errorFactory)) {
            throw new \RuntimeException('Error service factory is not configured for cookie service.');
        }

        return ($this->errorFactory)();
    }

    private function cookieWriter(): object
    {
        if ($this->cookieWriter === null || !method_exists($this->cookieWriter, 'write')) {
            throw new \RuntimeException('Cookie writer is not configured for cookie service.');
        }

        return $this->cookieWriter;
    }

    private function valueEncoder(): callable
    {
        if (!isset($this->cookieValueEncoder)) {
            $this->cookieValueEncoder = \Closure::fromCallable(static function (mixed $value): string {
                throw new \RuntimeException('Cookie value encoder is not configured for cookie service.');
            });
        }

        return $this->cookieValueEncoder;
    }

    private function valueDecoder(): callable
    {
        if (!isset($this->cookieValueDecoder)) {
            $this->cookieValueDecoder = \Closure::fromCallable(static function (
                string $payload,
                mixed $default = null,
                ?callable $onError = null,
                bool $returnOriginalOnLegacyFailure = false
            ): mixed {
                throw new \RuntimeException('Cookie value decoder is not configured for cookie service.');
            });
        }

        return $this->cookieValueDecoder;
    }

}
