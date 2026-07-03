<?php

declare(strict_types=1);

namespace fan\core\block\admin;
use fan\core\base\model\row;

/**
 * Admin form data class for loader block
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
class data_form extends data
{
    /**
     * Edited or Inserted entity
     * @var object entity
     */
    protected ?object $row = null;

    public function validateData(array &$edit, array &$insert): bool
    {
        $err = [];
        if ($edit) {
            $err = $this->doValidate($edit, 'edit');
        } elseif ($insert) {
            $err = $this->doValidate($insert, 'ins');
        }
        if ($err) {
            $this->errorMsg[] = implode("\n", $err);
            return false;
        }
        return true;
    }

    public function parseData(mixed $edit, mixed $insert): static
    {
        //$ettAcces = $this->getMeta('check_access4edit'); // ToDo: Check for use it
        $fields   = ($this->arrayAdducer())($this->getMeta(['form_struct', 'rows']));
        $entity = $this->entityService()->get((string)$this->getMeta('entity'));
        if ($edit) {
            $this->row = $entity->getRowByParam($this->getCondition());
            $this->saveRow($this->row, $edit, $fields);
            $this->checkDBerror($this->row, 'Can\'t update data: ');
        } elseif ($insert) {
            $addFields = array_keys($this->getMeta(['addParam', 'default_val'], []));
            $this->row = $entity->getNewRow();
            $this->saveRow($this->row, $insert, $fields, $addFields);
            $this->checkDBerror($this->row, 'Can\'t insert data: ');
        }
        return $this;
    }

    public function initTplVar(): void
    {
        $tplRows  = [];
        $metaRows = $this->getMeta(['form_struct', 'rows'], []);
        foreach ($metaRows as $k => $v) {
            $tplRows[$v['field']] = $v;
        }
        $this->setTemplateVar('rows', $tplRows);
    }

    protected function getMainData(array $data, array $force = []): array
    {
        $json = parent::getMainData($data, $force);
        $row = $this->getCurrentRow(true);
        $json['ei_mode'] = $row->checkIsLoad() ? 'edit' : 'ins';
        return $json;
    }


    public function getContentData(bool $cacheEnable = true): array
    {
        $row = $this->getCurrentRow($cacheEnable);

        $dataSrc = $row && $row->checkIsLoad() ? $row->getFields() : [];
        $data = [];
        foreach ($this->getMeta(['form_struct', 'rows'], []) as $v) {
            if (!empty($v['field']) && empty($v['notSQL'])) {
                $data[$v['field']] = $dataSrc[$v['field']] ?? null;
            }
        }
        return $data;
    }


    public function getFieldLabel(mixed $name): mixed
    {
        foreach ($this->getMeta(['form_struct', 'rows'], []) as $v) {
            if ((string)$v['field'] === (string)$name && !empty($v['label'])) {
                return $v['label'];
            }
        }
        return $name;
    }

    public function getCurrentRow(bool $cacheEnable): row
    {
        $ett = $this->entityService()->get((string)$this->getMeta('entity'));
        $ettKey = $this->getMeta('entity_key', null);
        return $ettKey ?
            $ett->getRowByKey((string)$ettKey, $this->getCondition()) :
            $ett->getRowByParam($this->getCondition());
    }
}
