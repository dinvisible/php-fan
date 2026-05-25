<?php
declare(strict_types=1);

namespace fan\core\base;
/**
 * Timer program base
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
 * @version of file: 05.02.007 (31.08.2015)
 * @abstract
 */
abstract class timer_program
{
    /**
     * @var entity_timer_program Entity of timer
     */
    private ?object $timerRow = null;

    /**
     * @var number Period of callings
     */
    private int|float|null $period = null;


    public function setTimerRow($timerRow): void
    {
        $this->timerRow = $timerRow;
    }

    public function getTimerRow(): ?object
    {
        return $this->timerRow;
    }

    public function setPeriod(int|float $period): void
    {
        if ($period >= 0) {
            $this->period = $period;
        }
    }

    public function getPeriod(): mixed
    {
        return is_null($this->period) ? $this->getTimerRow()->get_period(0, true) : $this->period;
    }

}
