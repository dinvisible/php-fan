<?php

declare(strict_types=1);

namespace fan\project\block\common;
/**
 * Pager quantifier class
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
 * @version of file: 05.02.006 (20.04.2015)
 */
class html_pager_quantifier extends \fan\core\block\common\html_pager_quantifier
{
    public function init(): void
    {
        $quantifierParams = [];
        foreach ($_GET as $k => $v) {
            if ((string)$k !== 'pager_quantifier') {
                $quantifierParams[$k] = $v;
            }
        }
        $this->_setViewVar('_quantifier_params', $quantifierParams);

        $this->parseForm();
    }


    protected function getFieldValuesFromRequest(): void
    {
        parent::getFieldValuesFromRequest();

        $form = $this->getForm();
        if (!$form->getFieldValue('pager_quantifier')) {
            $form->setFieldValue('pager_quantifier', $this->getQuantifier());
        }
    }

    public function onSubmit(): void
    {
        $key = $this->getMeta('sessionKey');

        if ($key) {
            $pagerQuantifier = $this->getQuantifier(true);

            if ($pagerQuantifier) {
                $this->getPagerSession()->set($key, $pagerQuantifier);
            }
        }
    }

    private function getPagerSession(): object
    {
        static $session = null;

        if (is_null($session)) {
            $session = $this->containerService('session', 'pager', 'custom');
        }

        return $session;
    }

    private function getQuantifier($getFromRequest = false): mixed
    {
        $quantifier = null;
        $defaultValue = $this->getFormMeta(['fields', 'pager_quantifier', 'default_value']);

        $key = $this->getMeta('sessionKey');
        if ($key) {
            if ($getFromRequest) {
                $quantifier = $this->containerService('request')->get('pager_quantifier', 'GP');
            }

            if (empty($quantifier)) {
                $quantifier = $this->getPagerSession()->get($key, $quantifier);
            }

            $values = $this->trimDataRecursive(explode(',', $this->getMeta('quantifier_values')), ['trim_data' => true]);

            if (!in_array($quantifier, $values)) {
                $quantifier = $defaultValue;
            }
        }

        return $quantifier ? $quantifier : $defaultValue;
    }

}
