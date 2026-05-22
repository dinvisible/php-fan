<?php

declare(strict_types=1);

namespace fan\core\block\form;
/**
 * Form block just for parse data, not for show form
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
abstract class parser extends \fan\core\block\base
{
    /**
     * Is form
     * @var boolean
     */
    protected bool $isForm = true;

    /**
     * Form service
     * @var \fan\core\service\form
     */
    protected ?object $form = null;

    /**
     * Role name form
     * @var string
     */
    protected string $roleName = '';

    /**
     * Init Parts of current form (usually auto - before main parsing)
     * Flag protect from double init if it was runned early
     * @var boolean
     */
    protected bool $partsInit = false;

    public function finishConstruct(?\fan\core\block\base $container = null, array $containerMeta = [], bool $allowSetEmbedded = true): void
    {
        parent::finishConstruct($container, $containerMeta, $allowSetEmbedded);
        if ($this->isForm && !$this->getRoleCondition()) {
            $this->_redefineFieldMeta();
            $this->_correctFieldMeta();
        }
    }

    public function checkFormRole(): bool
    {
        return $this->getForm()->checkFormRole();
    }

    public function getForm(): \fan\core\service\form
    {
        if (empty($this->form)) {
            $this->form = \fan\project\service\form::instance($this);
            $this->form->addListener('onSubmit', [$this, 'onSubmitEvent']);
            $this->form->addListener('onError',  [$this, 'onErrorEvent']);
        }
        return $this->form;
    }

    public function getRoleName(): string
    {
        return $this->roleName;
    }

    /**
     * @param mixed $default Fallback value returned when no explicit value is available.
     */
    public function getFormMeta(?string $key = null, mixed $default = null): mixed
    {
        $formMeta = $this->getMeta('form');
        if (empty($formMeta)) {
            return null;
        }
        return empty($key) ? $formMeta : $formMeta->get($key, $default);
    }

     public function getFieldsMeta(): mixed
    {
        return $this->getMeta(['form', 'fields'], []);
    }

    public function onSubmitEvent(mixed $block): void
    {
        if ($block === $this) {
            $this->onSubmit();
            $form = $this->getForm();
            if ($form->isError()) {
                $this->_broadcastEvent('onError',  $form->getErrorMsg());
            } else {
                $this->_broadcastEvent('onSubmit', $form->getFieldValue());
            }
        }
    }

    public function onErrorEvent(mixed $block): void
    {
        if ($block === $this) {
            $this->onError();
            $this->_broadcastEvent('onError', $this->getForm()->getErrorMsg());
        }
    }

//============ Functions are usualy redefined at the children classes ================\\

    public function checkBeforeValidation(): bool
    {
        return true;
    }

    public function checkAfterValidation(): bool
    {
        return true;
    }

    protected function onSubmit(): void
    {
    }

    protected function onError(): void
    {
    }


//============ Prived and Protected methods ================\\

    protected function _doRoleOperations(): void
    {
        if ($this->isForm) {
            if (!$this->getFormMeta('form_id')) {
                $this->setMeta(['form', 'form_id'], $this->blockName);
            }

            if (!$this->getFormMeta('not_role')) {
                $this->roleName = $this->getFormMeta('role_name') ?
                    (string)$this->getFormMeta('role_name') :
                    'form_submit_successful_' . (string)$this->getFormMeta('form_id');
            }

            $this->_setCacheRole($this->roleName);
        }
    }

    protected function _redefineFieldMeta(): void
    {
    }

    protected function _correctFieldMeta(): void
    {
        foreach ($this->getFieldsMeta() as $fieldName => $parameters) {
            if (!empty($parameters['validate_rules']) || !empty($parameters['is_required'])) {
                if (!isset($parameters['is_required'])) {
                    $parameters['is_required'] = false;
                    if (isset($parameters['validate_rules'])) {
                        foreach ($parameters['validate_rules'] as $validate) {
                            if (empty($validate['not_empty']) && (!isset($validate['rule_name']) || (string)$validate['rule_name'] !== 'is_required')) {
                                $parameters['is_required'] = true;
                                break;
                            }
                        }
                    }
                }
                if (!isset($parameters['label'])) {
                    $parameters['label'] = $fieldName;
                }
            }
            if (!isset($parameters['trim_data'])) {
                 $parameters['trim_data'] = isset($parameters['input_type']) && (string)$parameters['input_type'] !== 'password' ? true : false;
            }
            if (!isset($parameters['is_required'])) {
                    $parameters['is_required'] = false;
            }
        }
    }

    protected function _parseForm(mixed $parceEmpty = true, mixed $parsingCondition = null, mixed $allowTransfer = null): bool
    {
        if ($this->getMeta('auto_init_parts', true) && !$this->partsInit) {
            $this->_initFormParts($this);
        }
        return $this->getForm()->parseForm(
            (bool)$parceEmpty,
            is_null($parsingCondition) ? null : (bool)$parsingCondition,
            is_null($allowTransfer) ? null : (bool)$allowTransfer
        );
    }
    protected function _initFormParts(?\fan\core\block\form\parser $mainFormBlock = null): static
    {
        $this->partsInit = true;
        $parts = (array)$this->getFormMeta('form_parts', []);
        foreach ($parts as $v) {
            $block = $this->getTab()->getTabBlock($v, false);
            if (empty($block)) {
                throw new \RuntimeException('Block "' . (string)$v . '" (part of form) is not found');
            } elseif (method_exists($block, 'partInit')) {
                $block->partInit($mainFormBlock);
                if (method_exists($block, '_initFormParts') && is_callable([$block, '_initFormParts'])) {
                    $block->_initFormParts($mainFormBlock);

                }
            }
        }
        return $this;
    }

}
