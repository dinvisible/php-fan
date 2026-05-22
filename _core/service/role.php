<?php

declare(strict_types=1);

namespace fan\core\service;
use fan\core\base\expression_evaluator;
use fan\project\exception\service\fatal as fatalException;
/**
 * Description of Role
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
 * @version of file: 05.02.004 (25.12.2014)
 */
class role extends \fan\core\base\service\single
{
    /**
     * Key for mark common User space
     */
    public const COMMON_KEY = '_common_';

    /**
     * @var \fan\core\service\user Current User
     */
    private ?object $currentUser = null;

    /**
     * Static Roles of current user:
     *   ['role1' => 'expire_date_1', 'role2' => 'expire_date_2', ...];
     * @var array
     */
    private ?array $staticRoles = null;

    /**
     * Session Roles (without user's link), "_common_" OR by User space:
     *   [
     *       '_common_'     => ['role1' => 'expire_date_1', 'role2' => 'expire_date_2', ...],
     *       'user_space_1' => ['role3' => 'expire_date_3', 'role4' => 'expire_date_4', ...],
     *       'user_space_2' => ['role5' => 'expire_date_5', 'role6' => 'expire_date_6', ...],
     *       ...
     *   ];
     * @var array
     */
    private ?array $sessionRoles = null;

    /**
     * Limits of Session Roles with Fix Qtt access:
     *   [
     *       'role1' => [
     *           'qtt' => (int)'qtt_1',
     *           'urn' => (str)'regexp_1',
     *           'main_request' => [(str)'regexp_2', ...],
     *           'add_request'  => [(str)'regexp_3', ...],
     *           'both_request' => [(str)'regexp_4', ...],
     *       ],
     *       'role2' => [
     *           'qtt' => (int)'qtt_2',
     *           'urn' => (str)'regexp_5',
     *           'main_request' => [(str)'regexp_6', ...],
     *           'add_request'  => [(str)'regexp_7', ...],
     *           'both_request' => [(str)'regexp_8', ...],
     *       ],
     *       ...
     *   ];
     * @var array
     */
    private ?array $fixQttRoles = null;

    /**
     * All current (merged) Roles: simple ['role1', 'role2', ...];
     * @var array
     */
    private array $allRoles = [];

    /**
     * Current User Space
     * @var string
     */
    protected ?string $userSpace = null;

    protected function __construct()
    {
        parent::__construct();

        // Define Current User and his (static) roles
        $this->currentUser = $this->config->get('CHECK_LOGOUT', true) ?
                \fan\project\service\user::checkLogout() :
                \fan\project\service\user::getCurrent();
        $this->userSpace = $this->_getUserSpace();
        $this->_setStaticRoles();

        // Define Session roles
        $ses = $this->containerService('session', 'role', 'system');
        $this->sessionRoles =& $ses->getByLink('session',       []);
        $this->fixQttRoles  =& $ses->getByLink('fix_qtt_roles', []);
        $this->_removeSessionExpired();

        // Make subscribing
        $this->_subscribeForService('user',        'currentUser', [$this, 'onCurrentUserSet']);
        $this->_subscribeForService('user',        'changeRoles', [$this, 'onUserRolesChange']);
        $this->_subscribeForService('user',        'logoutUser',  [$this, 'onLogoutUser']);
        $this->_subscribeForService('application', 'setAppName',  [$this, 'onAppChange']);


        $this->_setCurrentRoles(true);
    }

    public function __destruct() {
        foreach ((array)$this->fixQttRoles as $k => &$v) {
            if ($v['qtt'] > 0) {
                if (true) { //ToDo: Check corresponding of all transfers to conditions
                    $v['qtt']--;
                }
            } else {
                $this->killSessionRoles($k);
            }
        }
    }

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\

    public function getRoles(): array
    {
        return $this->allRoles;
    }

    public function setSessionRoles(mixed $newRoles, int|float|string|null $expiredTime = null, bool $inUserSpace = true): static
    {
        $key = $inUserSpace ? $this->_getUserSpace() : self::COMMON_KEY;
        $val = $this->_defineExpiredDate($expiredTime);
        if (!is_null($val) && strcmp($val, date('Y-m-d H:i:s')) <= 0) {
            $this->killSessionRoles($newRoles, $inUserSpace ? 1 : 2);
            return $this;
        }

        if (!isset($this->sessionRoles[$key])) {
            $this->sessionRoles[$key] = [];
        }
        $changed = false;
        foreach ($this->_convValToArray($newRoles) as $v) {
            $v = trim((string)$v);
            $changed = $changed || !array_key_exists($v, $this->sessionRoles[$key]) || (bool)$this->sessionRoles[$key][$v] !== (bool)$val;
            $this->sessionRoles[$key][$v] = $val;
        }

        if ($changed) {
            $this->_setCurrentRoles(true);
        }
        return $this;
    }

    public function killSessionRoles(string|array|null $killRoles = null, int $destination = 3): static
    {
        $changed = false;
        if (empty($killRoles)) {
            $this->sessionRoles = [];
            $this->fixQttRoles  = [];
            $changed = true;
        } else {
            foreach ($this->_convValToArray($killRoles) as $v0) {
                $role = trim((string)$v0);
                if (!empty($role)) {
                    foreach ($this->sessionRoles as &$v1) {
                        if (array_key_exists($role, $v1)) {
                            unset($v1[$role]);
                            $changed = true;
                        }
                    }
                    if (array_key_exists($role, $this->fixQttRoles)) {
                        unset($this->fixQttRoles[$role]);
                    }
                }
            }
        }

        if ($changed) {
            $this->_setCurrentRoles(true);
        }
        return $this;
    }

    public function getSessionRoles(): ?array
    {
        return $this->sessionRoles;
    }

    public function setFixQttRoles(mixed $newRoles, int|float $qtt = 1, array $rules = [], int|float|null $expiredTime = null): static
    {
        $roles = [];
        foreach ($this->_convValToArray($newRoles) as $v) {
            $v = trim((string)$v);
            if (!empty($v)) {
                $this->fixQttRoles[$v] = [
                    'qtt'          => $qtt,
                    'urn'          => isset($rules['urn'])          ? $rules['urn']          : null,
                    'main_request' => isset($rules['main_request']) ? $rules['main_request'] : null,
                    'add_request'  => isset($rules['add_request'])  ? $rules['add_request']  : null,
                    'both_request' => isset($rules['both_request']) ? $rules['both_request'] : null,
                ];
                $roles[] = $v;
            }
        }
        return $this->setSessionRoles($roles, $expiredTime, true);
    }



    public function getCurrentUser(): ?\fan\core\service\user
    {
        return $this->currentUser;
    }

    public function setStaticRoles(mixed $newRoles, int|float|null $expiredTime = null): ?\fan\core\service\user
    {
        $user = $this->getCurrentUser();
        if (!empty($user) && !empty($newRoles)) {
            $roles = [];
            $date  = $this->_defineExpiredDate($expiredTime);
            foreach ($this->_convValToArray($newRoles, 'Incorrect value of static role') as $v) {
                $roles[$v] = $date;
            }
            $user->setRoles($roles);
        }
        return $user;
    }

    public function getStaticRoles(): ?array
    {
        return $this->staticRoles;
    }

    public function check(mixed $rolesRule): bool
    {
        if (empty($rolesRule)) {
            return true;
        }
        if (!is_string($rolesRule)) {
            $this->containerService('error')->logErrorMessage(var_export($rolesRule, false), 'Role is not string');
            return false;
        }

        try {
            return (bool)expression_evaluator::evaluate($rolesRule, fn($role) => $this->isRole($role));
        } catch (\InvalidArgumentException $e) {
            $this->containerService('error')->logErrorMessage($rolesRule, 'Incorrect role set');
            return false;
        }
    }

    public function isRole(string $role): bool
    {
        return in_array($role, $this->allRoles);
    }

    public function onCurrentUserSet(\fan\core\service\user $user): void
    {
        if ($this->getCurrentUser() !== $user) {
            $this->currentUser = $user;
            $this->_setStaticRoles();
            $this->_setCurrentRoles(true);
        }
    }

    public function onUserRolesChange(\fan\core\service\user $user): void
    {
        if ($this->getCurrentUser() === $user) {
            $this->_setStaticRoles();
            $this->_setCurrentRoles(true);
        }
    }

    public function onLogoutUser(): void
    {
        $this->currentUser = null;
        $this->_setStaticRoles();
        $this->_setCurrentRoles(true);
    }

    public function onAppChange(string $appName): void
    {
        $this->_setCurrentRoles();
    }

    // ======== Private/Protected methods ======== \\

    /**
     * @throws fatalException
     */
    protected function _convValToArray(mixed $val, ?string $exceptionMessage = null): array
    {
        if (empty($val)) {
            return [];
        }
        if (is_string($val)) {
            return [$val];
        }
        if (is_array($val)) {
            return $val;
        }
        if (is_object($val)) {
            if (method_exists($val, 'toArray')) {
                return $val->toArray();
            }
            if (method_exists($val, '__toString')) {
                return [$val->__toString()];
            }
        }
        if (!empty($exceptionMessage)) {
            throw new fatalException($this, $exceptionMessage);
        }
        return [];
    }

    protected function _setCurrentRoles(bool $force = false): static
    {
        $userSpace = $this->_getUserSpace();
        if ((string)$this->userSpace !== (string)$userSpace || $force) {
            $this->userSpace = $userSpace;

            $tmp = array_merge(
                isset($this->sessionRoles[self::COMMON_KEY])  ? $this->sessionRoles[self::COMMON_KEY]  : [],
                isset($this->sessionRoles[$userSpace]) ? $this->sessionRoles[$userSpace] : [],
                empty($this->staticRoles) ? [] : $this->staticRoles
            );
            $allRoles = [];
            foreach ($tmp as $k => $v) {
                $allRoles[] = (string)$k;
            }

            $changed  = array_diff($this->allRoles, $allRoles) || array_diff($allRoles, $this->allRoles);
            $this->allRoles = $allRoles;
            if ($changed) {
                $this->_broadcastMessage('rolesChanged', $allRoles);
            }
        }
        return $this;
    }

    protected function _setStaticRoles(): static
    {
        if (empty($this->currentUser)) {
            $this->staticRoles = [];
        } else {
            $this->staticRoles = $this->currentUser->getRoles();
            $this->_removeStaticExpired();
        }
        return $this;
    }

    protected function _removeStaticExpired(): static
    {
        if (!empty($this->staticRoles)) {
            $removed = $this->_checkRoleDate($this->staticRoles);
            if (!empty($removed)) {
                $this->getCurrentUser()->removeRole($removed);
            }
        }
        return $this;
    }

    protected function _removeSessionExpired(): static
    {
        $removed = [];
        foreach ($this->sessionRoles as &$v0) {
            $removed = array_merge($removed, $this->_checkRoleDate($v0));
        }

        foreach ($removed as $v1) {
            if (isset($this->fixQttRoles[$v1])) {
                foreach ($this->sessionRoles as $v2) {
                    if (isset($v2[$v1])) {
                        continue 2;
                    }
                }
                unset($this->fixQttRoles[$v1]);
            }
        }
        return $this;
    }

    protected function _checkRoleDate(array &$roles): array
    {
        $removed = [];
        $curDate = date('Y-m-d H:i:s');
        foreach ($roles as $role => $expire) {
            if (!is_null($expire) && strcmp($expire, $curDate) <= 0) {
                $removed[] = $role;
                unset($roles[$role]);
            }
        }
        return $removed;
    }

    protected function _getUserSpace(): string
    {
        return \fan\project\service\user::getCurrentSpace();
    }

    protected function _defineExpiredDate(int|float|string|null $expiredTime): ?string
    {
        if (is_null($expiredTime)) {
            return null;
        }
        if (is_numeric($expiredTime)) {
            return \fan\project\service\date::instance(date('Y-m-d H:i:s'), 'mysql')->shiftDate($expiredTime);
        }
        return \fan\project\service\date::instance(date($expiredTime))->get('mysql');
    }

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\

}
