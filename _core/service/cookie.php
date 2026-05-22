<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
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
class cookie extends \fan\core\base\service\multi
{
    private static array $instances = [];

    /**
     * Cookie data
     * @var array
     */
    protected static ?array $data = null;

    protected ?string $path = null;

    protected ?string $domain = null;

    protected bool $secure = false;

    protected bool $httpOnly = false;

    protected function __construct(mixed $path, mixed $domain, bool $secure)
    {
        parent::__construct(false);

        $this->path   = is_null($path) ? null : (string)$path;
        $this->domain = is_null($domain) ? null : (string)$domain;
        $this->secure = (bool)$secure;
        if (is_null(self::$data)) {
            self::$data = &$_COOKIE;
        }
    }

    public static function instance(mixed $path = null, mixed $domain = null, bool $secure = false): self
    {
        $config = self::staticContainerService('config')->get('cookie');

        if (is_null($path)) {
            $path = $config->get('DEFAULT_PATH', '/');
        }
        if (is_null($domain)) {
            $domain = $config->get('DEFAULT_DOMAIN');
        }

        $k0 = empty($domain) ? '' : (string)$domain;
        $k1 = empty($path)   ? '' : (string)$path;
        $k2 = empty($path)   ? '' : (string)$path;
        if (empty(self::$instances[$k0][$k1][$k2])) {
            self::$instances[$k0][$k1][$k2] = new self($path, $domain, !empty($secure));
        }

        return self::$instances[$k0][$k1][$k2];
    }


    public function get(string $name, ?string $defaultVal = null): mixed
    {
        if (!isset(self::$data[$name])) {
            return $defaultVal;
        }

        return $this->decodeCookieValue((string)self::$data[$name], $defaultVal);
    }

    /**
     * Transforms cookie value between supported representations.
     *
     * @param mixed $value Value that should be applied or transformed.
     */
    private function encodeCookieValue(mixed $value): string
    {
        try {
            return \fan\core\adapter\safe_serializer::encodeJson($value);
        } catch (\InvalidArgumentException $e) {
            throw new fatalException($this, 'Cookie value contains data unsupported by JSON.');
        } catch (\JsonException $e) {
            throw new fatalException($this, 'Cookie value isn\'t JSON serializable: ' . $e->getMessage());
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
        return \fan\core\adapter\safe_serializer::decodeExternalPayload(
            $value,
            $defaultVal,
            function (string $message): void {
                $this->containerService('error')->logErrorMessage($message, 'Cookie JSON decode error', '', true, false);
            },
            true
        );
    }

    public function getAll(?string $defaultVal = null): array
    {
        $result = [];
        foreach (self::$data as $k => $v) {
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

        if (setcookie($name, $cookieValue, ($time ? $time + time() : 0), (string)$this->path, (string)$this->domain, $this->secure, $this->httpOnly)) {
            if ($time < 0) {
                unset(self::$data[$name]);
            } elseif (!$this->secure || !empty($_SERVER['HTTPS'])) {
                self::$data[$name] = $cookieValue;
            }
            return true;
        }

        return false;
    }

    public function setByDate(string $name, mixed $value, string $date): bool
    {
        $date = trim($date);
        if (empty($date)) {
            throw new fatalException($this, 'Date isn\'t set');
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
        throw new fatalException($this, 'Date contains incorrect format: "' . $date . '"');
    }

    public function delete(string $name): bool
    {
        return $this->setByTime($name, null, -86400);
    }

    public function setHttpOnlyFlag(bool $httpOnly): void
    {
        $this->httpOnly = !empty($httpOnly);
    }

}
