<?php

declare(strict_types=1);

namespace fan\core\block\admin;
use fan\core\base\model\entity;
use fan\core\base\model\row;

/**
 * Admin table data class for loader block
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
class data_table extends data
{
    /**
     * Form error
     * @var array
     */
    protected array $insEtt = [];

    public function validateData(array &$edit, array &$insert): bool
    {
        $tmpErr = [];
        $this->validateDataOnce($edit, 'edit', $tmpErr);
        $this->validateDataOnce($insert, 'ins', $tmpErr);
        if ($tmpErr) {
            $errMsg = [];
            foreach ($tmpErr as $v) {
                $errMsg[] = implode('; ', $v);
            }
            $this->errorMsg[] = implode('\n', $errMsg);
            return false;
        }
        return true;
    }

    protected function validateDataOnce(array &$data, string $type, array &$tmpErr): void
    {
        foreach ($data as $id => &$v) {
            $err = $this->doValidate($v, $type, $id);
            foreach ($err as $fld => $err) {
                if (!isset($tmpErr[$fld]) || !in_array($err, $tmpErr[$fld])) {
                    if (!isset($tmpErr[$fld])) {
                        $tmpErr[$fld] = [];
                    }
                    $tmpErr[$fld][] = $err;
                }
            }
        }
    }

    /**
     * Transforms data between supported representations.
     */
    public function parseData(mixed $edit, mixed $insert): ?static
    {
        $edit = is_array($edit) ? $edit : [];
        $insert = is_array($insert) ? $insert : [];
        $ettAcces = $this->getMeta('check_access4edit');
        $fields   = ($this->arrayAdducer())($this->getMeta(['table_struct', 'columns']));
        foreach ($edit as $id => $v){
            $ett = $this->loadEntityById($id);
            if ($ettAcces && !$ett->$ettAcces($v)) {
                $this->isError = true;
                return null;
            }
            $this->saveRow($ett, $v, $fields);
            if (!$this->checkDBerror($ett, 'Can\'t update row ' . $id . ': ')) {
                return null;
            }
        }
        $convId = $this->getMeta(['addParam', 'convId'], []);
        if ($this->getMeta(['table_struct', 'newData'], true) || $convId) {
            $addFields = array_keys(($this->arrayAdducer())($this->getMeta(['addParam', 'default_val'], [])));
            if (!empty($convId[1])) {
                $addFields[] = $convId[1];
            }
            foreach ($insert as $ik => $v){
                if (!$convId || isset($v[$convId[1] ?? null])) {
                    $ett = $this->entityService()->get((string)$this->getMeta('entity'))->getNewRow();
                    foreach ($this->getMeta(['addParam', 'default_val'], []) as $k => $add) {
                        if (!array_key_exists($k, $v)) {
                            $v[$k] = $add;
                        }
                    }
                    $this->saveRow($ett, $v, $fields, $addFields);
                    if (!$this->checkDBerror($ett, 'Can\'t insert data: ')) {
                        return null;
                    }
                    $this->insEtt[$ik] = $ett;
                }
            }
        }
        return $this;
    }

    public function deleteData(mixed $del): void
    {
        $del = is_array($del) ? $del : [];
        if ($this->getMeta(['table_struct', 'showDel'], true)) {
            $ettAcces = $this->getMeta('check_access4delete');
            foreach ($del as $id => $v){
                $ett = $this->loadEntityById($id);;
                if ($ettAcces && !$ett->$ettAcces($v)) {
                    $this->isError = true;
                    return;
                }
                $ett->delete();
                if (!$this->checkDBerror($ett, 'Can\'t delete row ' . $id . ': ')) {
                    return;
                }
            }
        }
    }


    public function initTplVar(): void
    {
        foreach (['isHead' => true, 'showId' => true, 'showDel' => true] as $k =>$v) {
            $this->setTemplateVar($k, $this->getMeta(['table_struct', $k], $v));
        }
        $tplCols  = [];
        $metaCols = $this->getMeta(['table_struct', 'columns'], []);
        foreach ($metaCols as $k => $v) {
            $tplCols[$v['field']] = ($this->arrayAdducer())($v);
        }
        $this->setTemplateVar('columns', $tplCols);

        $opRight  = ($this->arrayAdducer())($this->getMeta('open_right'));
        if ($opRight) {
            foreach ($opRight as $f => &$o) {
                if (!empty($o['key'])) {
                    $this->addParam['open_right'][$f] = $o['key']; // ToDo: What is it?
                }
                if (empty($o['pos'])) {
                    $o['pos'] = 'before';
                }
                if (empty($o['pat'])) {
                    $o['pat'] = 'open_r1';
                }
            }
            $this->setTemplateVar('aOpRight', $opRight);
        }

        $hdOrder = ($this->arrayAdducer())($this->getMeta('order'));
        if ($hdOrder) {
            $this->setTemplateVar('hdOrder', $hdOrder);
            foreach ($this->getMeta(['table_struct', 'columns'], []) as $v) {
                if (isset($v['field']) && isset($hdOrder[$v['field']])) {
                    $this->addParam['label'][$v['field']] = isset($v['head']) ? ($this->arrayAdducer())($v['head']) : null;
                }
            }
        }
    }

    public function getExtraData(): array
    {
        $ret = parent::getExtraData();
        $hdOrder = $this->getMeta('order');
        if ($hdOrder && !isset($ret['order'])) {
            $ret['order'] = $hdOrder;
        }
        if (!$this->getMeta(['table_struct', 'newData'], true)) {
            $ret['not_new'] = 1;
        }
        return $ret;
    }

    public function getContentData(bool $cacheEnable = true): array
    {
        $ettKey = $this->getMeta('entity_key', null);
        $ettKey = is_null($ettKey) ? null : (string)$ettKey;
        $fld = [];
        foreach ($this->getMeta(['table_struct', 'columns'], []) as $v) {
            if (!empty($v['field']) && empty($v['notSQL'])) {
                $fld[] = $v['field'];
            }
        }

        $data = $this->getData();

        $orderData = isset($data['order']) ? $data['order'] : $this->getMeta('order');
        $order = '';
        if ($orderData) {
            foreach (($this->arrayAdducer())($orderData) as $k => $v) {
                if ($v) {
                    $order .= $k . ((int)$v === 1 ? ' ASC' : ' DESC') . ',';
                }
            }
            if ($order) {
                $order = ' ORDER BY ' . substr($order, 0, -1);
            }
        }

        if (empty($data['page'])) {
            $data['page'] = 1;
        }
        $ett = $this->entityService()->get((string)$this->getMeta('entity'));
        list($qtt, $offset) = $this->definePager($data['page'], $ett, $ettKey);

        $id = $ett->getDescription()->getPrimeryKey();
        if (is_array($id)) {
            $fld = array_merge($id, $fld);
        } else {
            array_unshift($fld, $id);
        }
        return $this->getArrayAssoc($ett, $ettKey, $fld, $qtt, $offset, $order, !$this->getMeta('editId', false));
    }

    protected function getArrayAssoc(entity $ett, ?string $ettKey, array $fld, int|float $qtt, int|float $offset, string $order, bool $excludeId = true): array
    {
        /* @var $rowset \fan\core\model\rowset */
        $rowset = $ettKey ?
            $ett->getRowsetByKey($ettKey, $this->getCondition(), $qtt, $offset, $order) :
            $ett->getRowsetByParam($this->getCondition(), $qtt, $offset, $order);
        return $rowset->getArrayAssoc($fld, $excludeId, '_');
    }

    public function getFieldLabel(mixed $name): mixed
    {
        foreach ($this->getMeta(['table_struct', 'columns'], []) as $v) {
            if ((string)$v['field'] === (string)$name && !empty($v['head'])) {
                return $v['head'];
            }
        }
        return $name;
    }

    /**
     * @param int|float $id Unique identifier used to locate the target item.
     */
    protected function loadEntityById(int|float|string $id): row
    {
        return $this->entityService()->get((string)$this->getMeta('entity'))->getRowById($id);
    }

    protected function definePager(int|float|string $page, entity $ett, ?string $ettKey): array
    {
        if (!$page) {
            $qtt = $offset = -1;
        } else {
            $page = is_numeric($page) ? (int)$page : 1;
            $qttElm = $ettKey ? $ett->getCountByKey((string)$ettKey, $this->getCondition()) : $ett->getCountByParam($this->getCondition());
            $qtt = (int)$this->getMeta('elmPerPage');
            $pageQtt = ceil($qttElm / $qtt);
            if ($page > $pageQtt) {
                $page = $pageQtt;
                if ($page < 1) {
                    $page = 1;
                }
            }
            $offset = ($page - 1) * $qtt;
            $this->setJson(['pager' => [$page, $qttElm < 1 ? 1 : $pageQtt, $qttElm]]);
        }
        return [$qtt, $offset];
    }

}
