<?php

declare(strict_types=1);

namespace fan\project\block\form;
/**
 * Form for send data to server block abstract
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
 * @version of file: 05.02.007 (31.08.2015)
 * @abstract
 */
abstract class injector extends \fan\core\block\form\usual
{
    protected function _parseForm($parceEmpty = true, $parsingCondition = null, $allowTransfer = null): bool
    {
        $idForm = $this->getMeta(['form', 'form_id']);
        $root   = $this->_getBlock('root');
        $isUseJs = false;

        $fields = $this->getFormMeta('fields');
        if (!empty($fields)) {
            foreach ($fields as $key => $field) {
                if (!empty($field['fill_empty'])) {
                    // prepare embedded JS init
                    $root->setEmbedJs(
                        sprintf(
                            'new triggerEmpty("%s", "%s", "%s");',
                            $idForm,
                            $key,
                            str_replace(['"', "\n"], ['&quot;', '\n'], $field['fill_empty'])
                        ),
                        'head',
                        -1
                    );
                    $isUseJs = true;
                }
            }
        }
        if ($isUseJs) {
            $root->setExternalJs('/js/js-wrapper.js');
            $root->setExternalJs('/js/extra/trigger_empty.js');
        }

        return parent::_parseForm($parceEmpty, $parsingCondition, $allowTransfer);
    }
}
