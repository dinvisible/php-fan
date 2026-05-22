<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
use fan\project\exception\service\date as dateException;
/**
 * Timer manager service
 *
 * This file is part PHP-FAN (php-framework from Alexandr Nosov)
 * Copyright (C) 2005-2007 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *     http://www.opensource.org/licenses/lgpl-license.php
 *
 * Do not remove this comment if you want to use script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.02.009 (23.09.2015)
 */
// ToDo: redesign this class
class date extends \fan\core\base\service\multi
{
    /**
     * @var boolean Is Global init
     * @var \fan\core\service\config\row
     */
    private static ?object $globalConfig = null;

    private static array $instances = [];

    protected ?object $date = null;

    protected ?string $format = null;

    protected bool $isTime = true;

    protected ?string $timezone = null;

    protected bool $save = true;

    protected function __construct(\DateTime $date, mixed $format, bool $isTime, mixed $timezone, bool $save)
    {
        $this->date     = $date;
        $this->format   = $format === null ? null : (string)$format;
        $this->isTime   = (bool)$isTime;
        $this->timezone = $timezone === null ? null : (string)$timezone;
        $this->save     = (bool)$save;
        parent::__construct();

    }

    // ======== Static methods ======== \\

    /**
     * @throws \fan\core\exception\service\date
     */
    public static function instance(?string $date = null, mixed $format = null, mixed $timezone = null, bool $save = true): static
    {
        $config = self::_getGlobalConfig();
        $timezoneDefault = (string)$config->get('TIMEZONE', 'Europe/Kiev');
        if (empty(self::$instances)) {
            date_default_timezone_set($timezoneDefault);
        }
        if (is_null($timezone)) {
            $timezone = $timezoneDefault;
        }

        if (is_null($format)) {
            $date = null;
            foreach ($config->get('DEFAULT_FORMAT', []) as $v) {
                list($isTime, $date) = self::_getDate($config, $date, (string)$v, (string)$timezone);
                if (!is_null($date)) {
                    $format = (string)$v;
                    break;
                }
            }
        } else {
            list($isTime, $date) = self::_getDate($config, $date, (string)$format, (string)$timezone);
        }

        if (is_null($date)) {
            throw new dateException('Can\'t get date by "' . $date . '" format "' . $format . '".');
        }

        $key0 = $isTime ? 1 : 0;
        $key3 = $date->format('YmdHisu');
        if (!$save || !isset(self::$instances[$key0][$timezone][$format][$key3])) {
            return new self($date, $format, $isTime, $timezone, $save);
        }
        return self::$instances[$key0][$timezone][$format][$key3];
    }
    protected static function _getGlobalConfig(): \fan\core\service\config\row
    {
        if (empty(self::$globalConfig)) {
            self::$globalConfig = self::staticContainerService('config')->get('date');
        }
        return self::$globalConfig;
    }
    /**
     * @throws \fan\core\exception\service\date
     */
    protected static function _getDate(\fan\core\service\config\row $config, ?string $date, string $format, string $timezone): array
    {
        $confFormat = $config->get(['FORMAT', $format]);
        if (is_null($confFormat)) {
            throw new dateException('Requested format "' . $format . '" isn\'t found.');
        }

        $timezone = new \DateTimeZone($timezone);
        $dateValue = (string)$date;

        $fullFormat = $confFormat->get('full_pattern');
        $date = \DateTime::createFromFormat((string)$fullFormat, $dateValue, $timezone);
        if (!is_bool($date)) {
            return [true, $date];
        }

        $shortFormat = (string)$confFormat->get('short_pattern') . ' H:i:s';
        $date = \DateTime::createFromFormat($shortFormat, $dateValue . ' 00:00:00', $timezone);
        return is_bool($date) ? [null, null] : [false, $date];
    }

    // ======== Main Interface methods ======== \\

    public function get(?string $format = null): string
    {
        return $this->date->format((string)$this->_getPattern($format));
    }

    public function getCustom(string $pattern): string
    {
        return $this->date->format($pattern);
    }

    /**
     * @throws \fan\core\exception\service\fatal
     */
    public function setFormat(string $format): static
    {
        if ($this->save) {
            throw new fatalException($this, 'You can change format only for not saved date.');
        }
        if (!isset($this->config['FORMAT'][$format])) {
            throw new fatalException($this, 'Unknown date format "' . $format . '"');
        }
        $this->format = $format;
        return $this;
    }

    public function isTime(): bool
    {
        return $this->isTime;
    }

    public function getDateAsArray(): array
    {
        $result = [];
        $pattern = $this->_getPattern();
        preg_match_all('/\w/', $pattern, $matches);
        foreach ($matches[0] as $v) {
            $result[$v] = $this->date->format($v);
        }
        return $result;
    }

    public function getTimeStamp(): int
    {
        return $this->date->getTimestamp();
    }

    public function getDifference(string $date2, bool $abs = true): int
    {
        $date2 = service('date', [$date2, $this->format, $this->timezone, $this->save]);
        $ret = $this->getTimeStamp() - $date2->getTimeStamp();
        return $abs ? abs($ret) : $ret;
    }

    public function shiftDate(int|float $shift): string
    {
        return date((string)$this->_getPattern(), (int)($this->getTimeStamp() + $shift));
    }

    public function modify(string $modify): static
    {
        $date = clone $this->date;
        $result = $date->modify($modify);
        if (is_bool($result)) {
            throw new fatalException($this, 'Can\'t modify date by "' . $modify . '"');
        }

        $key0 = $this->isTime ? 1 : 0;
        $key3 = $date->format('YmdHisu');
        if (!$this->save || !isset(self::$instances[$key0][$this->timezone][$this->format][$key3])) {
            return new self($result, $this->format, $this->isTime, $this->timezone, $this->save);
        }
        return self::$instances[$key0][$this->timezone][$this->format][$key3];
    }

    public function toArray(): array
    {
        return $this->getDateAsArray();
    }

    public function isValid(): bool
    {
        return true;
    }

    // ======== Private/Protected methods ======== \\

    protected function _saveInstance(): static
    {
        if ($this->save) {
            $key0 = $this->isTime ? 1 : 0;
            $key3 = $this->date->format('YmdHisu');
            self::$instances[$key0][$this->timezone][$this->format][$key3] = $this;
        }
        return $this;
    }

    protected function _getPattern(mixed $format = null): string
    {
        if (is_null($format)) {
            $format = $this->format;
        } elseif (!isset($this->config['FORMAT'][$format])) {
            throw new fatalException($this, 'Unknown data format "' . $format . '"');
        }
        return (string)$this->config['FORMAT'][$format][$this->isTime ? 'full_pattern' : 'short_pattern'];
    }

    // ======== The magic methods ======== \\
    /**
     * Implements PHP magic behavior for this current component.
     */
    public function __toString(): string
    {
        return $this->get(null);
    }

    // ======== Required Interface methods ======== \\

}
