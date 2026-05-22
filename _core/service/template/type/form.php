<?php

declare(strict_types=1);

namespace fan\core\service\template\type;
/**
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
 */
abstract class form extends base
{
    /**
     * Regexp for parse combi field name
     */
    public const RE_NAME = '/^(\w+)((?:\[[^\]]+\])+)$/';

    /**
     * Form service
     * @var \fan\core\service\form
     */
    protected ?object $form = null;

    private static array $formNumber = [];

    private ?int $curNum = null;

    /**
     * Template variables
     * @var \fan\core\base\meta\row
     */
    protected ?object $formMeta = null;

    protected ?array $fieldType = null;

    protected int $separateInd = 0;

    private array $tabIndex = [];

    protected ?bool $multiLng = null;

    public function __construct(\fan\core\block\form\usual $block)
    {
        parent::__construct($block);

        $this->form     = $block->getForm();
        $this->formMeta = $block->getFormMeta();
        foreach (['input', 'checking', 'select', 'select_separated', 'select_multi', 'select_multi_separated'] as $type) {
            foreach ($this->_getFormMeta(['design', $type], []) as $k => $v) {
                $this->fieldType[$k] = $type;
            }
        }

        for ($i = $this->formMeta['formNumber']; $i < 500; $i++) {
            if (!isset(self::$formNumber[$i])) {
                self::$formNumber[$i] = $this->blockName;
                $this->curNum = $i;
                break;
            }
        }

        $this->multiLng = $this->form->isMultiLanguage();
    }

    public static function getEngineList(): array
    {
        return ['main', 'form'];
    }

    public static function getAutoParseTag(): array
    {
        return [
            'form_row'    => ['method' => 'getFormRow',  'require' => ['name']],
            'form_label'  => ['method' => 'getLabel',    'require' => ['name']],
            'form_field'  => ['method' => 'getField',    'require' => ['name']],
            'form_error'  => ['method' => 'getErrorMsg', 'require' => ['name']],
            'form_note'   => ['method' => 'getNote',     'require' => []],
            'form_button' => ['method' => 'getButton',   'require' => ['text']],
        ];
    }

    public function getKeyField(): string
    {
        $keyValue = (string)$this->formMeta['form_id'];
        $csrfLen  = (int)$this->formMeta['csrf_protection'];
        if ($csrfLen >= 4) {
            $csrfCode = substr(md5(microtime() . $keyValue), 0, min(32, $csrfLen));
            $this->containerService('session', $keyValue, 'form_key')->set('csrf', $csrfCode);
            $keyValue .= '_' . $csrfCode;
        }
        return '<input type="hidden" name="form_key_field" value="' . $keyValue . '" />' . $this->getSidField();
    }

    public function getSidField(): string
    {
        $ses = $this->containerService('session');
        return $ses->isByCookies() ? '' : '<input type="hidden" name="' . $ses->getSessionName() . '" value="' . $ses->getSessionId() .'" />';
    }

    public function getFormRow(array $data): string
    {
        $pattern = $this->_getFormMeta([
            'design',
            'formRow',
            empty($data['type']) ? $this->_getFormMeta(['default_type', 'formRow']) : $data['type'],
        ], '<div>{LABEL}{FORM_FIELD}{ERROR}{NOTE}</div>');
        $find = $replace = [];
        foreach ([
            ['{LABEL}',      'getLabel',    'label_type'],
            ['{FORM_FIELD}', 'getField',    'field_type'],
            ['{ERROR}',      'getErrorMsg', 'error_type'],
            ['{NOTE}',       'getNote',     'note_type']
        ] as $v) {
            if (strstr($pattern, $v[0])) {
                $find[]    = $v[0];
                $method    = $v[1];
                $replace[] = $this->$method(array_merge($data, ['type' => array_val($data, $v[2])]));
            }
        }
        return str_replace($find, $replace, (string)$pattern);
    }

    public function getLabel(array $data): string
    {
        $fieldMeta = $this->_getFieldMeta($data);
        if (empty($fieldMeta['label'])) {
            return '#!Label isn\'t set!#';
        }

        $required = false;
        if (!empty($fieldMeta['is_required'])) {
            $required = true;
        } elseif (isset($fieldMeta['validate_rules'])){
            foreach ($fieldMeta['validate_rules'] as $rule){
                if (!empty($rule['rule_name']) && (string)$rule['rule_name'] === 'is_required'){
                    $required = true;
                    break;
                }
            }
        }

        $type     = empty($data['type']) ? $this->_getFormMeta(['default_type', 'label']) : $data['type'];
        $patterns = $this->_getFormMeta(['design', 'label']);
        $pattern  = $required && isset($patterns[$type . '_required']) ? $patterns[$type . '_required'] : array_val($patterns, $type, '<span>{LABEL}:</span>');

        return str_replace('{LABEL}', (string)$this->_getMsgByLng($fieldMeta['label'], $data), (string)$pattern);
    }

    public function getErrorMsg(array $data): string
    {
        $error = $this->form->getErrorMsg($data['name']);
        if (empty($error)) {
            $error = $this->form->getErrorMsg($this->_parseCombiName($data));
        }
        if (empty($error)) {
            return '';
        }
        $pattern = $this->_getFormMeta([
            'design',
            'error',
            empty($data['type']) ? $this->_getFormMeta(['default_type', 'error']) : $data['type'],
        ], '<div>{TEXT}</div>');

        if (!is_array($error)) {
            $text = $error;
        } elseif (isset($data['index'])) {
            $text = array_val($error, $data['index'], '');
        } else {
            $text = '';
            foreach ($error as $v) {
                if (!empty($text) && !empty($v)) {
                    $text .= '<br />';
                }
                $text .= $v;
            }
        }
        return str_replace('{TEXT}', (string)$text, (string)$pattern);
    }

    public function getNote(array $data): string
    {
        $text = array_val($data, 'note');
        if (empty($text)) {
            $text = $this->_getFieldMeta($data, 'note');
        }
        if (empty($text)) {
            return '';
        }

        $multiLng = array_val($data, 'multiLng', $this->multiLng);
        $pattern  = $this->_getFormMeta([
            'design',
            'note',
            empty($data['type']) ? $this->_getFormMeta(['default_type', 'note']) : $data['type'],
        ], '<div>{TEXT}</div>');

        return str_replace([
            '{NOTE}',
            '{TEXT}'
        ], [
            (string)$this->_getMsgByLng($multiLng ? 'NOTE_FORM_ROW' : 'Note', $data),
            (string)$this->_getMsgByLng($text, $data)
        ], (string)$pattern);
    }

    public function getButton(array $data): string
    {
        $pattern = $this->_getFormMeta([
            'design',
            'button',
            empty($data['type']) ? $this->_getFormMeta(['default_type', 'button']) : $data['type'],
        ], '<input type="submit"{NAME} value="{VALUE}"{TABINDEX} />');
        return str_replace(' name=""', '', str_replace([
            '{TEXT}',
            '{NAME}',
            '{ID}',
            '{VALUE}',
            '{CLASS}',
        ], [
            (string)$this->_getMsgByLng($data['text'], $data),
            isset($data['name'])  ? ' name="' . $data['name'] . '"' : '',
            empty($data['id'])    ? '' : 'id="' . $data['id'] . '"',
            (string)array_val($data, 'value', 1),
            isset($data['class']) ? ' class="' . $data['class'] . '"' : '',
        ], $this->_setAttributes($pattern, $data)));
    }

    public function getField(array $data): string
    {
        if (empty($data['type'])) {
            $data['type'] = $this->_getFieldMeta($data, 'input_type');
        }

        if (!isset($this->fieldType[$data['type']])) {
            return '<!-- Undefined field type -->';
        }
        switch ($this->fieldType[$data['type']]) {
        case 'input':
            $metod = 'getInput';
            break;
        case 'checking':
            $metod = 'getChecking';
            break;
        case 'select':
        case 'select_multi':
            $metod = 'getSelect';
            break;
        case 'select_separated':
        case 'select_multi_separated':
            $metod = 'getSeparatedSelect';
            break;
        }
        return $this->$metod($data);
    }

    public function getInput(array $data): string
    {
        $pattern = $this->_getFormMeta(
                ['design', 'input', empty($data['type']) ? 'text' : $data['type']],
                '<input type="text" name="{NAME}" value="{VALUE}"{MAXLENGTH}{ATTRIBUTES}{TABINDEX} />'
        );

        $maxLength = $this->_getFieldMeta($data, 'maxlength');
        if (!empty($maxLength)) {
            $maxLength = ' maxlength="' . (int)$maxLength . '"';
            unset($data['attributes']['maxlength']);
        }
        $val = $this->_getFieldValue($data);
        return str_replace([
            '{NAME}',
            '{ID}',
            '{VALUE}',
            '{MAXLENGTH}',
        ], [
            $data['name'],
            empty($data['id']) ? '' : ' id="' . $this->_getIdByName($data['name']) . '"',
            is_scalar($val) ? $val : '',
            $maxLength,
        ], $this->_setAttributes($pattern, $data));
    }

    public function getChecking(array $data): string
    {
        $pattern = $this->_getFormMeta(
                ['design', 'checking', empty($data['type']) ? 'checkbox' : $data['type']],
                '<input type="text" name="{NAME}" value="1"{CHECKED}{ATTRIBUTES}{TABINDEX} />'
        );
        //$fldMeta = $this->_getFieldMeta($data);
        $val = $this->_getFieldValue($data);
        return str_replace([
            '{NAME}',
            '{ID}',
            '{CHECKED}',
        ], [
            $data['name'],
            empty($data['id']) ? '' : 'id="' . $this->_getIdByName($data['name']) . '"',
            empty($val)        ? '' : ' checked="checked"',
        ], $this->_setAttributes($pattern, $data));
    }

    public function getSelect(array $data): string
    {
        $fieldType = isset($this->fieldType[$data['type']]) ? $this->fieldType[$data['type']] : null;
        if (!$fieldType || $fieldType === 'input') {
            $fieldType = 'select';
        }
        $pattern = $this->_getFormMeta(
                ['design', $fieldType, empty($data['type']) ? 'select' : $data['type']],
                '<select name="{NAME}"{ATTRIBUTES}{TABINDEX}>[<option value="{VALUE}"{SELECTED}>{TEXT}</option>]</select>'
        );
        $matches = [];
        if (preg_match('/^(?:[^\[]+|\[\])*\[(.+?)(?<!\[)\].*$/', $pattern, $matches)) {
            $subPattern = $matches[1];
            $pattern = str_replace('[' . $subPattern . ']', '{SUB_PATTERN}', $pattern);
        } else {
            $subPattern = '';
        }

        $val = $this->_getFieldValue($data);
        $fdt = $this->_getFieldData($data);
        return  str_replace([
            '{NAME}',
            '{ID}',
            '{SUB_PATTERN}',
        ], [
            $data['name'],
            empty($data['id']) ? '' : 'id="' . $this->_getIdByName($data['name']) . '"',
            empty($subPattern) ? '' : $this->_parseSubPattern(
                    $subPattern,
                    (is_scalar($val) || $fieldType === 'select_multi') ? $val : '',
                    $fdt,
                    $data,
                    $fieldType
            ),
        ], $this->_setAttributes($pattern, $data));
    }

    public function getSeparatedSelect(array $data): string
    {
        $fieldType = isset($this->fieldType[$data['type']]) ? $this->fieldType[$data['type']] : null;
        $pattern   = $this->_getFormMeta(
                ['design', $fieldType, empty($data['type']) ? 'checkbox_alone' : $data['type']],
                '<input type="checkbox" name="{NAME}[]" id="{ID}" value="{VALUE}"{CHECKED}{ATTRIBUTES}{TABINDEX} />'
        );

        $fdt = $this->_getFieldData($data);
        if (is_scalar($fdt)) {
            return 'Incorrect data';
        }

        if (empty($fdt[$this->separateInd++])) {
            return '';
        }
        $ind = $this->separateInd - 1;
        $cdt = $fdt[$ind];
        $cdt['value'] = array_val($cdt, 'value');
        $cdt['text']  = array_val($cdt, 'text');

        $val = $this->_getFieldValue($data, []);
        if ($fieldType === 'select_multi_separated' && !is_array($val)) {
            $val = [];
        }
        $selectedValue = is_scalar($val) || $val === null ? (string)$val : '';
        $selectedValues = is_array($val) ? array_map(
                static fn($item) => is_scalar($item) || $item === null ? (string)$item : $item,
                $val
        ) : [];

        return  str_replace([
            '{NAME}',
            '{ID}',
            '{VALUE}',
            '{TEXT}',
            '{CHECKED}',
        ], [
            $data['name'],
            $this->_getIdByName($data['name']) . '_' . ($ind),
            array_val($cdt, 'value'),
            array_val($cdt, 'text'),
            isset($cdt['value']) && (
                    $fieldType === 'select_separated' ?
                    (string)$cdt['value'] === $selectedValue :
                    in_array((string)$cdt['value'], $selectedValues, true)
            ) ?
                ' checked="checked"' :
                '',
        ], $this->_setAttributes($pattern, $data));
    }


    // ---------------------------------------------------- \\

    protected function _getMsgByLng(string $text, array $data): mixed
    {
        return array_val($data, 'multiLng', $this->multiLng) ? msg((string)$text) : (string)$text;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    protected function _parseSubPattern(string $subPattern, mixed $value, mixed $fdt, array $data, string $fieldType): string
    {
        $ret = '';
        if (is_array($fdt)) {
            foreach ($fdt as $k =>$d) {
                $d['value'] = array_val($d, 'value');
                $d['text']  = array_val($d, 'text');
                $normalizedValues = array_map(
                        static fn($item) => is_scalar($item) || $item === null ? (string)$item : $item,
                        adduceToArray($value)
                );
                $normalizedValue = is_scalar($value) || $value === null ? (string)$value : '';
                $selected = !is_null($value) && (
                        $fieldType === 'select_multi' ?
                        in_array((string)$d['value'], $normalizedValues, true) :
                        strcmp((string)$d['value'], $normalizedValue) === 0
                );
                $ret .= str_replace([
                    '{NAME}',
                    '{ID}',
                    '{VALUE}',
                    '{TEXT}',
                    '{SELECTED}',
                    '{CHECKED}',
                ], [
                    $data['name'],
                    $this->_getIdByName($data['name']) . '_' . $k,
                    $d['value'],
                    $d['text'],
                    ($selected ? ' selected="selected"' : ''),
                    ($selected ? ' checked="checked"' : ''),
                ], $this->_setAttributes($subPattern, $data));
            }
        }
        return $ret;
    }

    protected function _setAttributes(string $pattern, array $data, bool $isTabInd = true): string
    {
        if ($isTabInd) {
            $pattern = $this->_setTabIndex($pattern, $data);
        }
        $attrRepl = '';
        if (!empty($data['name'])) {
            $attr = $this->_getFieldMeta($data, 'attributes', array_val($data, 'attributes'));
            if ($attr) {
                foreach ($attr as $k => $v) {
                    $attrRepl .= ' ' . $k . '="' . $v . '"';
                }
            }
        }
        return str_replace('{ATTRIBUTES}', $attrRepl, $pattern);
    }

    protected function _setTabIndex(string $pattern, array $data): string
    {
        if (!strstr($pattern, '{TABINDEX}') || (isset($data['tabindex']) && empty($data['tabindex']))) {
            return $pattern;
        }
        $tabIndex = array_val($data, 'tabindex', empty($this->tabIndex) ? 1 : max($this->tabIndex) + 1);
        if (in_array($tabIndex, $this->tabIndex)) {
            throw new \UnexpectedValueException('Duplicate TabIndex ' . $tabIndex . ' at the form "' . $this->blockName . '".');
        } else {
            $this->tabIndex[] = $tabIndex;
        }
        return str_replace('{TABINDEX}', ' tabindex="' . ($this->curNum * 100 + $tabIndex) . '"', $pattern);
    }

    protected function _getIdByName(string $name): string
    {
        return str_replace(']', '', str_replace('[', '_', str_replace('][', '_', (string)$name)));
    }

    /**
     * @param mixed $default Fallback value returned when no explicit value is available.
     */
    protected function _getFormMeta(mixed $key, mixed $default = null): mixed
    {
        if (is_array($key)) {
            $dest = array_get_element($this->formMeta, $key, false);
            return is_null($dest) ? $default : $dest;
        }
        return array_val($this->formMeta, $key, $default);
    }

    /**
     * @param mixed $default Fallback value returned when no explicit value is available.
     */
    protected function _getFieldMeta(array $data, mixed $key = null, mixed $default = null): mixed
    {
        $name = $data['name'];
        $matches = [];
        for ($i = 0; $i < 2; $i++) {
            $key = is_null($key) ? ['fields', $name] : ['fields', $name, $key];
            $val = $this->_getFormMeta($key);
            if (!is_null($val)) {
                return $val;
            }
            if (!empty($i) || !preg_match(self::RE_NAME, $name, $matches)) {
                break;
            }
            $name = $matches[1];
        }
        return $default;
    }

    /**
     * @param mixed $default Fallback value returned when no explicit value is available.
     */
    protected function _getFieldValue(array $data, mixed $default = null): mixed
    {
        $val = $this->form->getFieldValue($this->_parseCombiName($data), false);
        return is_null($val) ? $default : $val;
    }

    /**
     * @param mixed $default Fallback value returned when no explicit value is available.
     */
    protected function _getFieldData(array $data, mixed $default = null): mixed
    {
        $name = $data['name'];
        $matches = [];
        for ($i = 0; $i < 2; $i++) {
            $val = $this->form->getFieldData($name);
            if (!is_null($val)) {
                return $val;
            }
            if (!empty($i) || !preg_match(self::RE_NAME, $name, $matches)) {
                break;
            }
            $name = $matches[1];
        }
        return $default;
    }

    protected function _parseCombiName(array $data): string|array
    {
        $name = $data['name'];
        $matches = [];
        if (preg_match(self::RE_NAME, $name, $matches)) {
            $name = explode('][', substr($matches[2], 1, -1));
            array_unshift($name, $matches[1]);
        }
        return $name;
    }

}
