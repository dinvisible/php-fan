<?php

declare(strict_types=1);

namespace fan\core\block\admin;
/**
 * Admin structure class for loader block
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
class structure extends base
{
    public function init(): void
    {
        $this->containerService('role')->setSessionRoles('admin', $this->getMeta('login_timeout'));

        $data = $this->getData();
        $json = [];

        // Prepare template
        $this->initTplVar();

        $html = $this->getTemplateCode();
        if (!empty($html)) {
            $json['condition']['code'] = $html;
        }
        // Prepare param
        $addParam = $this->getAddParam();
        if ($addParam) {
            $json['condition']['param'] = $addParam;
        }
        // Prepare Extra data
        $extra = $this->getExtraData();
        if ($extra) {
            $json['condition']['extra'] = $extra;
        }
        // Prepare condition Data
        $condition = $this->getCondition();
        if ($condition) {
            $json['condition']['cond'] = $condition;
        }

        if ($json) {
            $this->setJson($json);
        }

        $this->setText('ok');
    }

    public function initTplVar(): void
    {
    }

    public function getAddParam(): array
    {
        return $this->getMeta('addParam', []);
    }

    public function getExtraData(): array
    {
        return $this->getMeta('extra', []);
    }

    public function getCondition(): array
    {
        return $this->getMeta('cond', []);
    }
}
