<?php

declare(strict_types=1);

namespace fan\core\service;
use fan\core\base\service\single;
use fan\core\base\timer_program;
use fan\model\timer_program\row as timer_program_row;

/**
 * Cron-timer manager service
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
 */
class timer extends single
{
    /**
     * Limit jointly runned program (default)
     */
    public const JOINTLY_LIMIT_DEFAULT = 5;

    /**
     * Max count jointly runned program
     */
    public const JOINTLY_LIMIT_MAX = 32;

    /**
     * @var sting EntityName
     */
    protected ?string $ettName = null;
    /**
     * @var sting Base Path to timer classes
     */
    protected ?string $basePath = null;
    /**
     * @var sting Base NameSpace to timer classes
     */
    protected ?string $baseNS = null;

    private ?object $timerRuntime = null;

    /**
     * @var callable|null
     */
    private $timerDateFactory = null;

    /**
     * @var callable|null
     */
    private $timerEntityFactory = null;

    /**
     * @var callable|null
     */
    private $timerErrorFactory = null;

    /**
     * @var callable|null
     */
    private $timerLogFactory = null;

    /**
     * @var callable|null
     */
    private $timerEmailFactory = null;

    /**
     * @var callable|null
     */
    private $timerProgramFactory = null;

    public function __construct(
        ?object $runtime = null,
        ?callable $dateFactory = null,
        ?callable $entityFactory = null,
        ?callable $errorFactory = null,
        ?callable $logFactory = null,
        ?callable $emailFactory = null,
        ?callable $programFactory = null,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null
    )
    {
        $this->setTimerDependencies($runtime, $dateFactory, $entityFactory, $errorFactory, $logFactory, $emailFactory, $programFactory);
        parent::__construct(true, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory);
        $this->ettName  = (string)$this->getConfig('ENTITY', 'timer_program');
        $this->basePath = $this->timerRuntime()->parsePath((string)$this->getConfig('TIMER_DIR', '{PROJECT}/timer/'));
        $this->baseNS   = (string)$this->getConfig('BASE_NS', '\fan\project\timer');
    }

    public function setTimerDependencies(
        ?object $runtime = null,
        ?callable $dateFactory = null,
        ?callable $entityFactory = null,
        ?callable $errorFactory = null,
        ?callable $logFactory = null,
        ?callable $emailFactory = null,
        ?callable $programFactory = null
    ): static
    {
        $this->timerRuntime = $runtime;
        $this->timerDateFactory = $dateFactory;
        $this->timerEntityFactory = $entityFactory;
        $this->timerErrorFactory = $errorFactory;
        $this->timerLogFactory = $logFactory;
        $this->timerEmailFactory = $emailFactory;
        $this->timerProgramFactory = $programFactory;

        return $this;
    }

    public function chargeProgram(mixed $startTime, string $className, string $methodName, array $param, int|float $period = 0, int|float|null $overcall = null, bool $isShell = true): mixed
    {
        $startTime = is_numeric($startTime) ? $this->timerDate(date('Y-m-d H:i:s'), 'mysql')->shiftDate($startTime) : $startTime;
        if (!$startTime) {
            return null;
        }
        $row = $this->timerEntity()->get($this->ettName)->getNewRow();
        $row->setFields([
            'start_time'     => $startTime,
            'class_name'     => $className,
            'method_name'    => $methodName,
            'parameters'     => $param,
            'period'         => $period,
            'overcall_limit' => is_null($overcall) ? ($period > 0 ? 0 : -1) : $overcall,
            'overcall_qtt'   => 0,
            'is_active'      => 0,
            'last_start'     => null,
        ], true);

        if ($isShell && $this->getConfig('ENABLE_EXEC') && $this->getConfig('IS_AT_COMMAND')) {
            // Emulate work of Crontab-line by "at"-command in Windows
            $command = $this->_getCommandLine('CRON_FILE') . $row->getId();

            $date = $this->timerDate($row->get_start_time(), 'mysql')->getDateAsArray();
            $date[4] += ($date[5] > 55 ? 2 : 1);
            if ($date[4] > 59) {
                $date[4] -= 60;
                $date[3]++;
                if ($date[3] > 23) {
                    $date[3] -= 24;
                    $date[2]++;
                }
            }

            $command = 'at ' . $date[3] . ':' . $date[4] . ((string)$date[2] !== date('d') ? ' /next:' . $date[2] : '') . ' ' . $command;
            $this->execBackBin($command);
        }

        return $row->getId();
    }

    public function modifyChargedProgram(string $className, string $methodName, ?array $param = null, int|float|null $period = null, int|float|null $overcall = null): mixed
    {
        $row = $this->timerEntity()->get($this->ettName)->getRowByParam([
            'class_name'  => $className,
            'method_name' => $methodName,
        ]);

        if ($row->checkIsLoad()) {
            $this->_modifyProgram($row, $param, $period, $overcall);
            return $row->getId();
        }
        return $this->chargeProgram(0, $className, $methodName, $param ?? [], is_null($period) ? 0 : $period, $overcall);
    }

    public function modifyChargedProgramByPID(string $pid, ?array $param = null, int|float|null $period = null, int|float|null $overcall = null): ?string
    {
        $row = $this->timerEntity()->get($this->ettName)->getRowById($pid);
        if ($row->checkIsLoad()) {
            $this->_modifyProgram($row, $param, $period, $overcall);
            return $pid;
        }
        return null;

    }

    public function runCronProgram(?string $pid = null): bool
    {
        if ($pid) {
            $row = $this->timerEntity()->get($this->ettName)->getRowById($pid);
            if ($row->checkIsLoad()) {
                $this->_runProgram($row);
                return true;
            }
            return false;
        } else {
            $ett = $this->timerEntity()->get($this->ettName);
            if ($this->getConfig('ENABLE_EXEC')) {
                $rowset = $ett->getRowsetByParam('start_time <= \'' . date('Y-m-d H:i:s') . '\'', -1, -1, 'ORDER BY `last_start`');
                $jointlyLimit = $this->getConfig('JOINTLY_LIMIT', self::JOINTLY_LIMIT_DEFAULT);
                if ($jointlyLimit > self::JOINTLY_LIMIT_MAX) {
                    $jointlyLimit = self::JOINTLY_LIMIT_MAX;
                }
                $iteration = 0;
                foreach ($rowset as $e) {
                    $this->_runBackground($e->getId());
                    $iteration++;
                    if ($iteration >= $jointlyLimit) {
                        break;
                    }
                }
            } else {
                $rowset = $ett->getRowsetByParam('start_time <= \'' . date('Y-m-d H:i:s') . '\'', 1, -1, 'ORDER BY IF(`period`=0, IF(`is_active`=0, 0, 3), IF(`is_active`=0, 1, 2)), `last_start`');
                if (count($rowset) > 0) {
                    $this->_runProgram($rowset[0]);
                }
            }
        }
        return true;
    }
    public function execBackPhp(string $className, string $methodName, array $param, int|float $overcall = -1): mixed
    {
        $isExec = $this->getConfig('ENABLE_EXEC');
         // If process is started by PID shift time for many hour ahed so disable casual run by CRON
        $pid = $this->chargeProgram($isExec ? 10000 : 0, $className, $methodName, $param, 0, $overcall, false);
        if ($isExec) {
            $this->timerEntity()->get($this->ettName)->getConnection()->commit(); // ToDo: rebuild it
            $this->_runBackground((string)$pid);
        }
        return $pid;
    }

    public function execBackBin(string $cmd): bool
    {
        if ($this->getConfig('ENABLE_EXEC')) {
            if (substr(php_uname(), 0, 7) === 'Windows'){ // || !function_exists('exec')
                if (function_exists('popen') && function_exists('pclose')) {
                    pclose(popen('start /B ' . $cmd, 'r'));
                    return true;
                }
            } elseif (function_exists('exec')) {
                exec($cmd . ' > /dev/null &');
                return true;
            }
        }
        return false;
    }

    // =========================================================== \\

    protected function _runProgram(timer_program_row $timerRow): static
    {
        $error = $this->timerError();
        /* @var $error \fan\core\service\error */
        $className = $timerRow->get_class_name();

        // Check overcall before run Timer-class
        $period = $timerRow->get_period();
        if ($timerRow->get_is_active()) {
            $qttLimit = $timerRow->get_overcall_limit();
            if ($qttLimit < 0 && !$period) {
                return $this;
            }
            if ($qttLimit > -1) {
                $qtt = $timerRow->get_overcall_qtt() + 1;
                if (is_callable($this->timerLogFactory)) {
                    $this->timerLog()->logMessage('overcall', 'Quantity of owercall is ' . $qtt . ($qtt > $qttLimit ? ".\nIt is critical quantity (limit = " . $qttLimit . ').' : '.'), 'Timer program overcall', $className);
                }
                if ($qtt > $qttLimit) {
                    $error->makeErrorEmail('overcall', 'Timer program overcall', 'Quantity of owercall (' . $qtt . ") is more limit.\n\n" . $className);
                    if (!$period) {
                        return $this; // Return after one-call procedure
                    }
                } else {
                    $timerRow->set_overcall_qtt($qtt);
                    $timerRow->save();
                    $timerRow->getEntity()->getConnection()->commit();
                    return $this; // Return after allowed overcall
                }
            }
        }

        // Prepare time-parameters before run Timer-class
        $timerRow->setFields([
            'overcall_qtt' => 0,
            'is_active'    => 1,
            'last_start'   => date('Y-m-d H:i:s'),
        ]);
        $prevDate = $this->timerDate($timerRow->get_start_time(), 'mysql');
        if ($period > 0) {
            $difference = $prevDate->getDifference(date('Y-m-d H:i:s'));
            $startTime  = $prevDate->shiftDate($period * ceil($difference / $period));
            if ($startTime) {
                $timerRow->set_start_time($startTime);
            }
        }
        $timerRow->save();
        $timerRow->getEntity()->getConnection()->commit();

        $className = '\\' . trim((string)$this->baseNS, '\\') . '\\' . (string)$className;
        if (!class_exists($className)) {
            $error->logErrorMessage('Class "'. $className . '" for timer doesn\'t exists.', 'Error run timer proggamm');
            return $this;
        }
        $obj = $this->timerProgram($className);
        if (!$obj instanceof timer_program) {
            $error->logErrorMessage('Class "'. $className . ' isn\'t instance of \fan\core\base\timer_program.', 'Error run timer proggamm');
            return $this;
        }

        // Run Timer-class
        $obj->setTimerRow($timerRow);
        if (method_exists($obj, 'setTimerDependencies')) {
            $obj->setTimerDependencies($error, $this->timerEmail('timer_email'));
        }
        $methodName = (string)$timerRow->get_method_name();
        $obj->{$methodName}(...(array)$timerRow->get_parameters());

        // Fix result of Timer-class
        $period2 = $obj->getPeriod();
        if ($period2 > 0) {
            $difference = $prevDate->getDifference(date('Y-m-d H:i:s'));
            $startTime  = $prevDate->shiftDate($period2 * ceil($difference / $period2));
            if ($startTime) {
                $timerRow->set_start_time($startTime);
            }
            $timerRow->set_is_active(0);
            $timerRow->save();
        } elseif ($timerRow->checkIsLoad()) {
            $timerRow->delete();
        }
        $timerRow->getEntity()->getConnection()->commit();

        return $this;
    }

    protected function _modifyProgram(timer_program $row, mixed $param, int|float|null $period, int|float|null $overcall): void
    {
        if (!is_null($param)) {
            $row->set_parameters($param);
        }
        if (!is_null($period)) {
            $row->set_period($period);
        }
        if (!is_null($overcall)) {
            $row->set_overcall_limit($overcall);
        }
        $row->save();
    }

    protected function _runBackground(string $pid): bool
    {
        return $this->execBackBin($this->_getCommandLine('BGR_FILE') . $pid);
    }

    protected function _getCommandLine(string $key): string
    {
        $separator = defined('DIR_SEPARATOR') ? DIR_SEPARATOR : '/';
        $command   = str_replace($separator, DIRECTORY_SEPARATOR, $this->timerRuntime()->parsePath((string)$this->getConfig($key)));
        return (string)$this->getConfig('PHP_INTERPRETER') . ' ' . $command . ' ';
    }

    private function timerRuntime(): object
    {
        if ($this->timerRuntime === null) {
            throw new \RuntimeException('Bootstrap runtime service is not configured for timer service.');
        }

        return $this->timerRuntime;
    }

    private function timerDate(?string $date = null, mixed $format = null): object
    {
        if (!is_callable($this->timerDateFactory)) {
            throw new \RuntimeException('Date service factory is not configured for timer service.');
        }

        return ($this->timerDateFactory)($date, $format);
    }

    private function timerEntity(): object
    {
        if (!is_callable($this->timerEntityFactory)) {
            throw new \RuntimeException('Entity service factory is not configured for timer service.');
        }

        return ($this->timerEntityFactory)();
    }

    private function timerError(): object
    {
        if (!is_callable($this->timerErrorFactory)) {
            throw new \RuntimeException('Error service factory is not configured for timer service.');
        }

        return ($this->timerErrorFactory)();
    }

    private function timerLog(): object
    {
        if (!is_callable($this->timerLogFactory)) {
            throw new \RuntimeException('Log service factory is not configured for timer service.');
        }

        return ($this->timerLogFactory)();
    }

    private function timerEmail(string $name): object
    {
        if (!is_callable($this->timerEmailFactory)) {
            throw new \RuntimeException('Email service factory is not configured for timer service.');
        }

        return ($this->timerEmailFactory)($name);
    }

    private function timerProgram(string $className): object
    {
        if (!is_callable($this->timerProgramFactory)) {
            throw new \RuntimeException('Timer program factory is not configured for timer service.');
        }

        return ($this->timerProgramFactory)($className);
    }

}
