<?php

declare(strict_types=1);

namespace fan\app\__log_viewer\main;
/**
 * Request password block
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
 * @version of file: 05.02.005 (12.02.2015)
 */
class request_password extends \fan\project\block\form\injector
{
    /**
     * Current User
     * @var \fan\core\service\user
     */
    protected ?object $user = null;

    public function init(): void
    {
        $this->_parseForm();
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function checkPassword(mixed $value, array $data): bool
    {
        $this->user = getUser($this->getForm()->getFieldValue($data['login']));
        return empty($this->user) ? false : $this->user->checkPassword($value);
    }

    protected function onSubmit(): void
    {
        if (!empty($this->user)) {
            $this->user->setCurrent();
        }
    }

}
