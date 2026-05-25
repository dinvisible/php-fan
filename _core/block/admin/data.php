<?php

declare(strict_types=1);

namespace fan\core\block\admin;
use fan\core\base\model\row;
use fan\core\exception\base as exception_base;

/**
 * Admin data class for loader block
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
 * @version of file: 05.02.009 (23.09.2015)
 */
abstract class data extends base
{
    /**
     * Form error
     * @var array
     */
    protected array $errorMsg = [];

    /**
     * Form error
     * @var boolean
     */
    protected bool $isError = false;

    /**
     * JSON-parameters of admin data
     * @var array
     */
    protected array $addParam = [];

    /**
     * JSON-extr data for admin
     * @var array
     */
    protected array $extraData = [];

    public function init(): void
    {
        $this->roleService()->setSessionRoles('admin', $this->getMeta('login_timeout'));

        $data = $this->getData();


        // ====== Parse gotten data ======= \\
        $this->errorService()->setParseDBerror(false);
        do {
            if (isset($data['edit']) || isset($data['ins'])) {
                if (empty($data['edit'])) {
                    $data['edit'] = [];
                } elseif (empty($data['ins'])) {
                    $data['ins'] = [];
                }

                if (!$this->validateData($data['edit'], $data['ins'])) {
                    $this->isError = true;
                    break;
                }

                $this->parseData($data['edit'], $data['ins']);
                if ($this->isError) {
                    break;
                }
            }

            if (isset($data['del'])) { // Cancel edit if delete impossible?
                $this->deleteData($data['del']);
                if ($this->isError) {
                    break;
                }
            }
        } while (false);
        $this->errorService()->setParseDBerror(true);
        if ($this->isError) {
            $this->setText(implode("\n", $this->errorMsg));
            return;
        }

        // ====== Prepare output data ======= \\
        $json = $this->getMainData($data);

        $json['data'] = $this->getContentData();
        $this->setJson($json);

        $this->setText('ok');
    }

    public function validateData(array &$edit, array &$insert): bool
    {
        return true;
    }

    /**
     * Transforms data between supported representations.
     */
    public function parseData(mixed $edit, mixed $insert): ?static
    {
        return $this;
    }

    public function saveRow(row $row, array $data, array $fields, array $addFields = []): static
    {
        $edtType = $this->getMeta('editableTypes', []);
        $data2 = [];
        foreach ($fields as $v) {
            $k = $v['field'];
            if (!empty($edtType[$v['type']]) && array_key_exists($k, $data)) {
                $data2[$k] = $data[$k];
            }
        }
        if (!$row->checkIsLoad()) {
            foreach ($addFields as $k) {
                $data2[$k] = $data[$k];
            }
        }
        $row->setFields($data2, true);
        return $this;
    }

    public function deleteData(mixed $del): void
    {
    }

    protected function checkDBerror(row $row, string $errPref = ''): bool
    {
        $con = $row->getEntity()->getConnection();
        if ($con->isError()) {
            $errMsg = (string)$con->getErrorMessage();
            if (preg_match('/^(?:.+\:)?([^(]+)/', $errMsg, $matches)) {
                $errMsg = trim($matches[1]);
            }
            $this->errorMsg[] = $errPref . $errMsg;
            $this->isError = true;
            return false;
        }
        return true;
    }

    protected function getMainData(array $data, array $force = []): array
    {
        $json      = [];
        $forceMeta = $this->getMeta('force', []);
        $isFirst   = !empty($data['first']);

        // Prepare template
        $forceTpl = isset($force['template']) ? !empty($force['template']) : !empty($forceMeta['template']);
        if ($isFirst || $forceTpl) {
            $this->initTplVar();
            if (!$this->getTemplate()) {
                $this->setTemplate((string)$this->getMeta('default_tpl'));
            }
            $html = $this->getTemplateCode([]);
            if ($html) {
                $json['code'] = $html;
            }
        }

        // Prepare param
        $forceAddParam = isset($force['add_param']) ? !empty($force['add_param']) : !empty($forceMeta['add_param']);
        if ($isFirst || $forceAddParam) {
            $addParam = $this->getAddParam();
            if ($addParam) {
                $json['param'] = $addParam;
            }
        }

        // Prepare Extra data
        $forceExtraData = isset($force['extra_data']) ? !empty($force['extra_data']) : !empty($forceMeta['extra_data']);
        if ($isFirst || $forceExtraData) {
            $extra = $this->getExtraData();
            if ($extra) {
                $json['extra'] = $extra;
            }
        }

        // Flag for use Main Page
        if ($isFirst || $this->getMeta('useMainPage')) {
            $json['useMainPage'] = 1;
        }

        return $json;
    }

    public function initTplVar(): void
    {
    }

    public function getAddParam(): array
    {
        $ret    = ($this->arrayAdducer())($this->getMeta('addParam', []));
        $entity = $this->getMeta('entity', []);
        if ($entity) {
            $ret['id_name'] = $this->entityService()->get((string)$entity)->getDescription()->getPrimeryKey();
        }
        return ($this->recursiveMerger())($ret, $this->addParam);
    }

    public function getExtraData(): array
    {
        $ret = [];
        $tagId = $this->getMeta('tagId');
        if ($tagId) {
            $ret['tagId'] = 'cont_' . $tagId;
        }
        return ($this->recursiveMerger())($ret, $this->extraData);
    }

    public function getCondition(): array
    {
        $cond = $this->getMeta('condition', [], true);
        $data = $this->getData();
        if (!empty($data['cond'])) {
            $cond = ($this->recursiveMerger())($cond, $data['cond']);
        }
        return $cond;
    }

    public function getContentData(): array
    {
        return [];
    }


    public function getFieldLabel(mixed $name): mixed
    {
        return $name;
    }

    // ================================ Validate data ================================ \\
    /**
     * @param int|float|string|null $id Unique identifier used to locate the target item.
     */
    public function doValidate(array &$data, string $type, int|float|string|null $id = null): array
    {
        $req = $this->getMeta('validateRequiredMsg');
        $err = [];
        foreach ($this->getMeta('validation', []) as $fld => $vld) {
            if (isset($data[$fld]) && (!isset($vld['trim_data']) || $vld['trim_data'])) {
                $data[$fld] = trim((string)$data[$fld]);
            }
            if (!empty($vld['is_required']) && ($type === 'ins' ? empty($data[$fld]) : isset($data[$fld]) && !$data[$fld])) {
                $err[$fld] = str_replace('{FIELD_LABEL}', $this->getFieldLabel($fld), $req);
                continue;
            }
            if (!empty($vld['validate_rules'])) {
                foreach ($vld['validate_rules'] as $rule) {
                    $method = 'rule_' . $rule['rule_name'];
                    if ((isset($data[$fld]) || $type === 'ins') && (!empty($data[$fld]) || empty($rule['not_empty']))) {
                        if (!isset($data[$fld])) {
                            $data[$fld] = null;
                        }
                        if (!$this->$method($data[$fld], ($this->arrayAdducer())($rule['rule_data'] ?? []), $type, $id)) {
                            $err[$fld] = str_replace('{FIELD_LABEL}', $this->getFieldLabel($fld), !empty($rule['error_msg']) ? $rule['error_msg'] : 'Error');
                            continue 2;
                        }
                    }
                }
            }
        }
        return $err;
    }



    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    protected function rule_is_required(mixed $value): bool
    {
        return is_array($value) ? $value !== [] : (string)$value !== '';
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    protected function rule_is_int(mixed $value, array $data): bool
    {
        if (!preg_match('/^\-?\d+$/', (string)$value)) {
            return false;
        }
        if (isset($data['min_value']) && $value < $data['min_value']) {
            return false;
        }
        if (isset($data['max_value']) && $value > $data['max_value']) {
            return false;
        }
        return true;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    protected function rule_is_float(mixed $value, array $data): bool
    {
        $value = str_replace(',', '.', (string)$value);
        if (!is_numeric($value)) {
            return false;
        }
        if (isset($data['min_value']) && $value < $data['min_value'] - 0.000001) {
            return false;
        }
        if (isset($data['max_value']) && $value > $data['max_value'] + 0.000001) {
            return false;
        }
        return true;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    protected function rule_is_date(mixed $value, array $data): bool
    {
        $value = str_replace(',', '.', (string)$value);
        try {
            $dateService = $this->dateService($value);
            /* @var $dateService \fan\core\service\date */
        } catch (exception_base $e) {
            return false;
        }
        $date = $dateService->get('mysql');
        return (!isset($data['min_value']) || $date >= $data['min_value']) && (!isset($data['max_value']) || $date <= $data['max_value']);
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    protected function rule_is_email(mixed $value, array $data): bool
    {
        if (!preg_match('/^[a-z_0-9!#*=.-]+@([a-z0-9-]+\.)+[a-z]{2,4}$/i', (string)$value)) {
            return false;
        }
        return true;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    protected function rule_is_alphalogin(mixed $value, array $data): bool
    {
        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_@\.-]*$/', (string)$value)) {
            return false;
        }
        return true;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    protected function rule_is_alphanumeric(mixed $value, array $data): bool
    {
        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]*$/', (string)$value)) {
             return false;
        }
        return true;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    protected function rule_match_regexp(mixed $value, array $data): bool
    {
        if (!preg_match((string)$data['regexp'], (string)$value)) {
            return false;
        }
        return true;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    protected function rule_equal_to(mixed $value, array $data): bool
    {
        $value2 = null;
        if (isset($data['compare_field'])) {
            $value2 = $this->fieldValue[$data['compare_field']] ?? null;
        }
           if ((string)$value !== (string)$value2) {
            return false;
        }
        return true;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    protected function rule_not_equal_to(mixed $value, array $data): bool
    {
        $value2 = null;
        if (isset($data['compare_field'])) {
            $value2 = $this->fieldValue[$data['compare_field']] ?? null;
        }
        if ((string)$value === (string)$value2) {
            return false;
        }
        return true;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    protected function rule_greater_than(mixed $value, array $data): bool
    {
        $value2 = null;
        if (isset($data['compare_field'])) {
            $value2 = $this->fieldValue[$data['compare_field']] ?? null;
        }
        $dataType = (string)($data['data_type'] ?? '');
        if ($dataType === 'DATE' || $dataType === 'DATETIME') {
            $value = $this->dateService((string)$value, 'euro')->get('mysql');
            $value2 = $this->dateService((string)$value2, 'euro')->get('mysql');
        }
        if ($value <= $value2) {
            return false;
        }
        return true;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    protected function rule_lesser_than(mixed $value, array $data): bool
    {
        $value2 = null;
        if (isset($data['compare_field'])) {
            $value2 = $this->fieldValue[$data['compare_field']] ?? null;
        }
        $dataType = (string)($data['data_type'] ?? '');
        if ($dataType === 'DATE' || $dataType === 'DATETIME') {
            $value = $this->dateService((string)$value, 'euro')->get('mysql');
            $value2 = $this->dateService((string)$value2, 'euro')->get('mysql');
        }
        if ($value >= $value2) {
            return false;
        }
        return true;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    protected function rule_greater_or_equal_to(mixed $value, array $data): bool
    {
        $value2 = null;
        if (isset($data['compare_field'])) {
            $value2 = $this->fieldValue[$data['compare_field']] ?? null;
        }
        if ($value < $value2) {
            return false;
        }
        return true;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    protected function rule_lesser_or_equal_to(mixed $value, array $data): bool
    {
        $value2 = null;
        if (isset($data['compare_field'])) {
            $value2 = $this->fieldValue[$data['compare_field']] ?? null;
        }
        if ($value > $value2) {
            return false;
        }
        return true;
    }

}
