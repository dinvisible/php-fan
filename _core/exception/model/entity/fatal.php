<?php

declare(strict_types=1);

namespace fan\core\exception\model\entity;
/**
 * Exception a fatal error
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
 * @version of file: 05.02.011 (03.10.2015)
 */
class fatal extends \fan\core\exception\base
{
    /**
     * Entity's object
     * @var \fan\core\base\model\entity
     */
    protected ?object $entity = null;

    public function __construct(\fan\core\base\model\entity $entity, string $logErrMsg, int $code = E_USER_ERROR, ?\Throwable $previous = null)
    {
        $this->entity = $entity;

        parent::__construct($logErrMsg, $code, $previous);

        $note = method_exists($entity, '__toString') ? $entity->__toString() : '';
        $this->_logByService($logErrMsg, 'Entity fatal error (' . get_class($entity) . ').', $note);
    }

    public function getEntity(): \fan\core\base\model\entity
    {
        return $this->entity;
    }

    protected function _defineDbOper(?string $dbOper = 'rollback'): ?string
    {
        return parent::_defineDbOper($dbOper);
    }
}
