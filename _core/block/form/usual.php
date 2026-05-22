<?php

declare(strict_types=1);

namespace fan\core\block\form;
/**
 * Usual form block abstract
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
 * @version of file: 05.02.006 (20.04.2015)
 * @abstract
 */
abstract class usual extends parser
{

    /**
     * Array for template
     * @var array
     */
    protected array $formTpl = [];

    public function finishConstruct(?\fan\core\block\base $container = null, array $containerMeta = [], bool $allowSetEmbedded = true): void
    {
        parent::finishConstruct($container, $containerMeta, $allowSetEmbedded);
        if ($this->isForm && !$this->getRoleCondition()) {
            // Create JS validation rule
            if (!$this->roleName || !role($this->roleName)) {
                $validateJS = $this->getForm()->strForJsValidation();
                if ($validateJS) {
                    $this->_getBlock('root')->setEmbedJs($validateJS);
                }
            }
        }
    }

    public function getCachePermission(): bool
    {
        $mode = $this->getMeta(['cache', 'mode']);
        if ((int)$mode === 0 || !empty($_POST) && $this->getForm()->necessaryFormParsing(null, false)) {
            $this->disableCache();
            return false;
        } elseif ((int)$mode === 1) {
            return false;
        }
        return true;
    }

    public function getViewData(): array
    {
        $result     = parent::getViewData();
        $form       = $this->getForm();
        $fieldValue = $form->getFieldValue();

        foreach ($this->getFieldsMeta() as $fieldName => $parameters) {

            if (!isset($this->formTpl[$fieldName])) {
                //name of the form element
                $this->formTpl[$fieldName]['name'] = $fieldName;
                //type of the form element
                $this->formTpl[$fieldName]['type'] = empty($parameters['input_type']) ? null : $parameters['input_type'];
                //label of the form element
                $this->formTpl[$fieldName]['label'] = empty($parameters['label']) ? null : $parameters['label'];
                //value of the form element
                //it can be an array. if it is, it's mean that may be a few elements with same name and different indexes
                $this->formTpl[$fieldName]['value'] = $form->isError() || isset($fieldValue[$fieldName]) ?
                        array_val($fieldValue, $fieldName) :
                        (empty($parameters['default_value']) ? null : $parameters['default_value']);
                //parameters of the form element
                $this->formTpl[$fieldName]['parameters'] = empty($parameters['parameters']) ? null : $parameters['parameters'];
            }
        }


        $result['aErrors']  = $form->getErrorMsg();
        $result['formTpl'] = $this->formTpl;
        $actionUrl = $this->getFormMeta('action_url');
        $actionMethodMeta = (string)$this->getFormMeta('action_method');
        if (empty($actionUrl)) {
            $actionUrl    = $this->tab->getCurrentURI(false, true, strtoupper($actionMethodMeta) !== 'GET', false);
            $defaultHttps = $this->tab->getTabMeta('page_https');
        } else {
            $defaultHttps = null;
        }
        $result['action_url'] = $this->tab->getURI((string)$actionUrl, 'link', false, $this->getFormMeta('action_https', $defaultHttps));

        $actionMethod = strtolower($actionMethodMeta);
        if ($actionMethod === 'file') {
            $actionMethod = 'post" enctype="multipart/form-data';
        } elseif ($actionMethod !== 'get') {
            $actionMethod = 'post';
        }
        $result['action_method']  = '"' . $actionMethod . '"';
        $result['form_key_field'] = $this->getFormMeta('form_key_field');
        $result['form_id']        = $this->getFormMeta('form_id');

        return $result;
    }

}
