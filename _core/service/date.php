<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\core\base\service\multi;


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
class date extends multi
{
    protected ?object $date = null;

    protected ?string $format = null;

    protected bool $isTime = true;

    protected ?string $timezone = null;

    protected bool $save = true;

    private ?object $state = null;

    private mixed $dateFactory = null;

    private mixed $dateInstanceFactory = null;

    private ?object $dateServiceBootstrapRuntime = null;

    private ?object $dateServiceConfigurator = null;

    private mixed $dateServiceCacheFactory = null;

    private mixed $dateClassNameResolver = null;

    private mixed $dateArrayValueReader = null;

    public function __construct(
        \DateTime $date,
        mixed $format,
        bool $isTime,
        mixed $timezone,
        bool $save,
        ?object $state = null,
        ?callable $dateFactory = null,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null,
        ?callable $classNameResolver = null,
        ?callable $arrayValueReader = null,
        ?callable $dateInstanceFactory = null
    )
    {
        $this->date     = $date;
        $this->format   = $format === null ? null : (string)$format;
        $this->isTime   = (bool)$isTime;
        $this->timezone = $timezone === null ? null : (string)$timezone;
        $this->save     = (bool)$save;
        $this->state    = $state;
        $this->dateFactory = $dateFactory;
        $this->dateServiceBootstrapRuntime = $serviceBootstrapRuntime;
        $this->dateServiceConfigurator = $serviceConfigurator;
        $this->dateServiceCacheFactory = $serviceCacheFactory;
        $this->dateClassNameResolver = $classNameResolver;
        $this->dateArrayValueReader = $arrayValueReader;
        $this->dateInstanceFactory = $dateInstanceFactory;
        parent::__construct(true, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory, null, null, $classNameResolver, $arrayValueReader);

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
            throw $this->createServiceFatalException('You can change format only for not saved date.');
        }
        if (!isset($this->config['FORMAT'][$format])) {
            throw $this->createServiceFatalException('Unknown date format "' . $format . '"');
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
        $date2 = $this->createDate($date2, $this->format, $this->timezone, $this->save);
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
            throw $this->createServiceFatalException('Can\'t modify date by "' . $modify . '"');
        }

        $key0 = $this->isTime ? 1 : 0;
        $key3 = $date->format('YmdHisu');
        $saved = $this->state?->getInstance($this->isTime, (string)$this->timezone, (string)$this->format, $key3);
        if (!$this->save || $saved === null) {
            return $this->createDateInstance(
                $result,
                $this->format,
                $this->isTime,
                $this->timezone,
                $this->save,
                $this->state,
                $this->dateFactory,
                $this->dateServiceBootstrapRuntime,
                $this->dateServiceConfigurator,
                $this->dateServiceCacheFactory,
                $this->dateClassNameResolver,
                $this->dateArrayValueReader
            );
        }
        return $saved;
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
            $key3 = $this->date->format('YmdHisu');
            $this->state?->setInstance($this->isTime, (string)$this->timezone, (string)$this->format, $key3, $this);
        }
        return $this;
    }

    protected function _getPattern(mixed $format = null): string
    {
        if (is_null($format)) {
            $format = $this->format;
        } elseif (!isset($this->config['FORMAT'][$format])) {
            throw $this->createServiceFatalException('Unknown data format "' . $format . '"');
        }
        return (string)$this->config['FORMAT'][$format][$this->isTime ? 'full_pattern' : 'short_pattern'];
    }

    private function createDate(?string $date, mixed $format, mixed $timezone, bool $save): object
    {
        if (!is_callable($this->dateFactory)) {
            throw new \RuntimeException('Date service factory is not configured for date service.');
        }

        return ($this->dateFactory)($date, $format, $timezone, $save);
    }

    private function createDateInstance(
        \DateTime $date,
        mixed $format,
        bool $isTime,
        mixed $timezone,
        bool $save,
        ?object $state,
        ?callable $dateFactory,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory,
        ?callable $classNameResolver = null,
        ?callable $arrayValueReader = null
    ): static {
        if (!is_callable($this->dateInstanceFactory)) {
            throw new \RuntimeException('Date instance factory is not configured for date service.');
        }

        $instance = ($this->dateInstanceFactory)(
            $date,
            $format,
            $isTime,
            $timezone,
            $save,
            $state,
            $dateFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $classNameResolver,
            $arrayValueReader
        );
        if (!$instance instanceof self) {
            $actual = is_object($instance) ? get_class($instance) : gettype($instance);
            throw new \UnexpectedValueException('Date instance factory returned "' . $actual . '".');
        }

        return $instance;
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
